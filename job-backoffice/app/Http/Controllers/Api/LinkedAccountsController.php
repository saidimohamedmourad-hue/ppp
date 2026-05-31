<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuthProvider;
use App\Services\AuthLinkingService;
use App\Services\LoginAuditService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * Phase 5 — manage the social providers attached to the currently
 * authenticated user.
 *
 * All routes are mounted behind auth:sanctum; the user always operates on
 * their own account.
 *
 * Endpoints:
 *   GET    /api/profile/auth-providers
 *   POST   /api/profile/auth-providers/google
 *   POST   /api/profile/auth-providers/facebook
 *   DELETE /api/profile/auth-providers/{id}
 *   POST   /api/profile/password         (set initial password on a
 *                                         social-only account)
 */
class LinkedAccountsController extends Controller
{
    public function __construct(private LoginAuditService $audit) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $providers = $user->authProviders()
            ->orderBy('created_at')
            ->get(['id', 'provider', 'provider_user_id', 'created_at'])
            ->map(fn ($row) => [
                'id'          => $row->id,
                'provider'    => $row->provider,
                'display_id'  => $this->maskId($row->provider_user_id),
                'linked_at'   => $row->created_at,
            ]);

        return response()->json([
            'has_password' => ! empty($user->password),
            'providers'    => $providers,
        ]);
    }

    public function linkGoogle(Request $request): JsonResponse
    {
        $payload = $this->fetchGoogleProfile($request);
        $this->guardAlreadyLinked($request->user(), 'google', $payload['sub']);
        $this->guardOwnedByAnotherUser($request->user(), 'google', $payload['sub']);

        $request->user()->authProviders()->create([
            'provider'         => 'google',
            'provider_user_id' => $payload['sub'],
            'meta'             => ['email' => $payload['email'] ?? null],
        ]);
        $this->audit->recordSuccess($request->user(), 'google', event: 'link', request: $request);

        return $this->index($request);
    }

    public function linkFacebook(Request $request): JsonResponse
    {
        $payload = $this->fetchFacebookProfile($request);
        $this->guardAlreadyLinked($request->user(), 'facebook', $payload['id']);
        $this->guardOwnedByAnotherUser($request->user(), 'facebook', $payload['id']);

        $request->user()->authProviders()->create([
            'provider'         => 'facebook',
            'provider_user_id' => $payload['id'],
            'meta'             => ['email' => $payload['email'] ?? null],
        ]);
        $this->audit->recordSuccess($request->user(), 'facebook', event: 'link', request: $request);

        return $this->index($request);
    }

    public function unlink(Request $request, string $id): JsonResponse
    {
        $user = $request->user();

        $provider = $user->authProviders()->where('id', $id)->first();
        if (! $provider) {
            return response()->json(['message' => 'Cette méthode de connexion n\'existe pas.'], 404);
        }

        // Safety: a user must always retain at least one way to sign in.
        // If they have no password, they need at least 2 providers to allow
        // one to be removed.
        $hasPassword = ! empty($user->password);
        $providerCount = $user->authProviders()->count();
        if (! $hasPassword && $providerCount <= 1) {
            throw ValidationException::withMessages([
                'provider' => ['Vous ne pouvez pas supprimer votre seule méthode de connexion. Définissez un mot de passe d\'abord.'],
            ]);
        }

        $providerName = $provider->provider;
        $provider->delete();
        $this->audit->recordSuccess($user, $providerName, event: 'unlink', request: $request);

        return $this->index($request);
    }

    /**
     * Lets a social-only user set a local password so they can sign in by
     * email/password too. If the user already has a password, we require
     * the current one — this becomes a "change password" flow.
     */
    public function setPassword(Request $request): JsonResponse
    {
        $user = $request->user();
        $needsCurrent = ! empty($user->password);

        $data = $request->validate([
            'current_password' => $needsCurrent ? 'required|string' : 'nullable',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if ($needsCurrent && ! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        $user->forceFill(['password' => Hash::make($data['password'])])->save();
        // Revoke other tokens so a stolen session doesn't survive this change.
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'message'      => 'Mot de passe défini avec succès.',
            'has_password' => true,
        ]);
    }

    // ─── helpers ─────────────────────────────────────────────────────────

    /**
     * @return array<string,mixed>
     */
    private function fetchGoogleProfile(Request $request): array
    {
        $data = $request->validate([
            'id_token'     => 'required_without:access_token|string',
            'access_token' => 'required_without:id_token|string',
        ]);

        try {
            if (! empty($data['id_token'])) {
                $resp = Http::timeout(8)->get(
                    'https://oauth2.googleapis.com/tokeninfo',
                    ['id_token' => $data['id_token']],
                );
            } else {
                $resp = Http::timeout(8)
                    ->withToken($data['access_token'])
                    ->get('https://openidconnect.googleapis.com/v1/userinfo');
            }
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'access_token' => ['Impossible de joindre Google. Réessayez.'],
            ]);
        }

        if (! $resp->successful() || empty($resp->json('sub'))) {
            throw ValidationException::withMessages([
                'access_token' => ['Le jeton Google est invalide ou expiré.'],
            ]);
        }

        return $resp->json();
    }

    /**
     * @return array<string,mixed>
     */
    private function fetchFacebookProfile(Request $request): array
    {
        $data = $request->validate(['access_token' => 'required|string']);

        $appId     = config('services.facebook.client_id');
        $appSecret = config('services.facebook.client_secret');
        if (! $appId || ! $appSecret) {
            throw ValidationException::withMessages([
                'access_token' => ['Facebook Login n\'est pas configuré.'],
            ]);
        }

        try {
            $debug = Http::timeout(8)->get('https://graph.facebook.com/debug_token', [
                'input_token'  => $data['access_token'],
                'access_token' => $appId.'|'.$appSecret,
            ]);
            $me = Http::timeout(8)->get('https://graph.facebook.com/me', [
                'fields'       => 'id,name,email',
                'access_token' => $data['access_token'],
            ]);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'access_token' => ['Impossible de joindre Facebook. Réessayez.'],
            ]);
        }

        $debugData = $debug->json('data') ?? [];
        if (! ($debugData['is_valid'] ?? false) || ($debugData['app_id'] ?? null) !== $appId) {
            throw ValidationException::withMessages([
                'access_token' => ['Le jeton Facebook est invalide.'],
            ]);
        }
        if (! $me->successful() || empty($me->json('id'))) {
            throw ValidationException::withMessages([
                'access_token' => ['Profil Facebook inaccessible.'],
            ]);
        }

        return $me->json();
    }

    private function guardAlreadyLinked($user, string $provider, string $providerUserId): void
    {
        $own = $user->authProviders()
            ->where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->exists();
        if ($own) {
            throw ValidationException::withMessages([
                'provider' => ['Ce compte '.ucfirst($provider).' est déjà lié.'],
            ]);
        }
    }

    private function guardOwnedByAnotherUser($user, string $provider, string $providerUserId): void
    {
        $otherUserHasIt = AuthProvider::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->where('user_id', '!=', $user->id)
            ->exists();
        if ($otherUserHasIt) {
            throw ValidationException::withMessages([
                'provider' => ['Ce compte '.ucfirst($provider).' est déjà lié à un autre utilisateur IQRA.'],
            ]);
        }
    }

    /**
     * Display-only: keep the first/last few chars to give the user a way to
     * recognize the linked account without exposing the full opaque id.
     */
    private function maskId(string $id): string
    {
        if (strlen($id) <= 8) return $id;
        return substr($id, 0, 4).'…'.substr($id, -4);
    }
}
