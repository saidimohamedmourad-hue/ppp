<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuthLinkingService;
use App\Services\LoginAuditService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * Endpoints called by the SPA / mobile app after the user finished the
 * provider-side OAuth flow. The client sends us a token, we verify it with
 * the provider, then we find-or-create the matching User and issue a
 * Sanctum token.
 */
class SocialAuthController extends Controller
{
    public function __construct(
        private AuthLinkingService $linker,
        private LoginAuditService $audit,
    ) {}

    /**
     * POST /api/auth/google
     *
     * Accepts either:
     *   - `{ id_token: <JWT> }`       — preferred (Android / iOS native, one-tap web)
     *   - `{ access_token: <opaque> }` — Web implicit flow
     *
     * Both go to Google's official endpoints for verification, so we never
     * trust client-supplied claims directly.
     */
    public function google(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id_token'     => 'required_without:access_token|string',
            'access_token' => 'required_without:id_token|string',
        ]);

        if (! empty($data['id_token'])) {
            $payload = $this->verifyGoogleIdToken($data['id_token']);
        } else {
            $payload = $this->fetchGoogleUserinfo($data['access_token']);
        }

        // We require an email; without it we can't link/identify the account.
        if (empty($payload['email'])) {
            throw ValidationException::withMessages([
                'id_token' => ['Le compte Google n\'a pas partagé son adresse email.'],
            ]);
        }

        // sub = Google's stable, opaque user ID. NEVER use email as the
        // identifier — users can change emails on Gmail in some cases.
        $user = $this->linker->findOrCreateFromSocial(
            provider: 'google',
            providerUserId: $payload['sub'],
            email: $payload['email'],
            name: $payload['name'] ?? null,
            avatar: $payload['picture'] ?? null,
            meta: [
                'email_verified' => $payload['email_verified'] ?? null,
                'locale'         => $payload['locale'] ?? null,
            ],
        );

        $user->update(['last_login_at' => now()]);
        $token = $user->createToken('mobile')->plainTextToken;
        $this->audit->recordSuccess($user, provider: 'google', request: $request);

        return response()->json([
            'user'  => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    /**
     * Verifies a Google-issued ID token via the tokeninfo endpoint. Google
     * handles signature + expiry checks for us, then returns the JWT payload.
     *
     * @return array<string,mixed>
     */
    private function verifyGoogleIdToken(string $idToken): array
    {
        try {
            $resp = Http::timeout(8)
                ->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $idToken]);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'id_token' => ['Impossible de joindre Google pour vérifier votre identité. Réessayez.'],
            ]);
        }

        if (! $resp->successful()) {
            throw ValidationException::withMessages([
                'id_token' => ['Le jeton Google est invalide ou expiré.'],
            ]);
        }

        $payload = $resp->json();

        // Enforce that the token was issued for one of OUR client IDs.
        $allowedAudiences = array_filter([
            config('services.google.web_client_id'),
            config('services.google.android_client_id'),
            config('services.google.ios_client_id'),
        ]);
        if (! empty($allowedAudiences) && ! in_array($payload['aud'] ?? null, $allowedAudiences, true)) {
            throw ValidationException::withMessages([
                'id_token' => ['Ce jeton n\'a pas été émis pour notre application.'],
            ]);
        }

        return $payload;
    }

    /**
     * Fetches the user profile via the OAuth2 userinfo endpoint using an
     * opaque access_token (typically from the web implicit flow).
     *
     * @return array<string,mixed>
     */
    private function fetchGoogleUserinfo(string $accessToken): array
    {
        try {
            $resp = Http::timeout(8)
                ->withToken($accessToken)
                ->get('https://openidconnect.googleapis.com/v1/userinfo');
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'access_token' => ['Impossible de joindre Google pour vérifier votre identité. Réessayez.'],
            ]);
        }

        if (! $resp->successful()) {
            throw ValidationException::withMessages([
                'access_token' => ['Le jeton Google est invalide ou révoqué.'],
            ]);
        }

        return $resp->json();
    }

    /**
     * POST /api/auth/facebook
     *
     * Expects `{ access_token: string }` from Facebook Login (web JS SDK or
     * native mobile). We:
     *   1. Verify the token belongs to OUR app via /debug_token.
     *   2. Fetch the user profile via /me.
     *   3. Find-or-create-or-link via AuthLinkingService.
     *
     * Note: Instagram users with linked Facebook accounts can sign in here —
     * Meta dropped standalone Instagram OAuth in 2024.
     */
    public function facebook(Request $request): JsonResponse
    {
        $data = $request->validate([
            'access_token' => 'required|string',
        ]);

        $payload = $this->verifyFacebookToken($data['access_token']);

        if (empty($payload['email'])) {
            throw ValidationException::withMessages([
                'access_token' => ['Le compte Facebook n\'a pas partagé son adresse email. Vérifiez les permissions accordées.'],
            ]);
        }

        $user = $this->linker->findOrCreateFromSocial(
            provider: 'facebook',
            providerUserId: $payload['id'],
            email: $payload['email'],
            name: $payload['name'] ?? null,
            avatar: $payload['picture']['data']['url'] ?? null,
            meta: [
                'verified'  => $payload['verified'] ?? null,
                'locale'    => $payload['locale']   ?? null,
            ],
        );

        $user->update(['last_login_at' => now()]);
        $token = $user->createToken('mobile')->plainTextToken;
        $this->audit->recordSuccess($user, provider: 'facebook', request: $request);

        return response()->json([
            'user'  => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    /**
     * Verifies the Facebook access_token in two steps:
     *  1) /debug_token confirms the token was issued for OUR app_id and is
     *     still valid (handles expiry + revocation).
     *  2) /me fetches the user profile fields we need.
     *
     * @return array<string,mixed>
     */
    private function verifyFacebookToken(string $userAccessToken): array
    {
        $appId     = config('services.facebook.client_id');
        $appSecret = config('services.facebook.client_secret');
        if (! $appId || ! $appSecret) {
            throw ValidationException::withMessages([
                'access_token' => ['Facebook Login n\'est pas configuré côté serveur.'],
            ]);
        }

        // Step 1: validate the token belongs to our app.
        try {
            $debug = Http::timeout(8)->get('https://graph.facebook.com/debug_token', [
                'input_token'  => $userAccessToken,
                'access_token' => $appId.'|'.$appSecret,
            ]);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'access_token' => ['Impossible de joindre Facebook pour vérifier votre identité. Réessayez.'],
            ]);
        }

        if (! $debug->successful()) {
            throw ValidationException::withMessages([
                'access_token' => ['Le jeton Facebook est invalide.'],
            ]);
        }

        $debugData = $debug->json('data') ?? [];

        if (empty($debugData['is_valid']) || ! $debugData['is_valid']) {
            throw ValidationException::withMessages([
                'access_token' => ['Le jeton Facebook a expiré ou a été révoqué.'],
            ]);
        }

        if (($debugData['app_id'] ?? null) !== $appId) {
            throw ValidationException::withMessages([
                'access_token' => ['Ce jeton n\'a pas été émis pour notre application.'],
            ]);
        }

        // Step 2: fetch the profile. `picture.type(large)` gives a 200x200 avatar.
        try {
            $me = Http::timeout(8)->get('https://graph.facebook.com/me', [
                'fields'       => 'id,name,email,picture.type(large),verified',
                'access_token' => $userAccessToken,
            ]);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'access_token' => ['Impossible de récupérer votre profil Facebook. Réessayez.'],
            ]);
        }

        if (! $me->successful()) {
            throw ValidationException::withMessages([
                'access_token' => ['Le profil Facebook n\'est pas accessible.'],
            ]);
        }

        return $me->json();
    }

    /**
     * Mirrors AuthApiController::formatUser. Kept here so the two endpoints
     * can evolve independently if we ever add provider-specific fields.
     */
    private function formatUser(User $user): array
    {
        $user->loadMissing(['company', 'school']);

        return [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'role'            => $user->role,
            'profile_picture' => $user->profile_picture,
            'avatar_url'      => $user->avatar_url,
            'cv_url'          => $user->cv_url,
            'last_login_at'   => $user->last_login_at,
            'created_at'      => $user->created_at,
            'has_company'     => $user->company !== null,
            'has_school'      => $user->school !== null,
        ];
    }
}
