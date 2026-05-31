<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordFr;
use App\Services\LoginAuditService;
use App\Services\TurnstileService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    public function __construct(
        private LoginAuditService $audit,
        private TurnstileService $turnstile,
    ) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:job-seeker,company-owner,school-owner',
            // Phone is required for company-owner and school-owner because we
            // expose it to candidates as the recruiter's contact channel.
            // Job-seekers can fill it later (it becomes required on their
            // first job/training application — see JobApiController::apply).
            'phone'    => 'required_unless:role,job-seeker|nullable|string|min:6|max:32|regex:/^[0-9+\-\s()]+$/',
        ], [
            'phone.required_unless' => 'Le numéro de téléphone est obligatoire pour les entreprises et écoles.',
            'phone.regex'           => 'Le numéro de téléphone contient des caractères invalides.',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'user'  => $this->formatUser($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
            $this->audit->recordFailure(
                provider: 'password',
                attemptedEmail: $data['email'],
                reason: 'bad-credentials',
                request: $request,
            );
            throw ValidationException::withMessages([
                'email' => ['Les identifiants sont incorrects.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('mobile')->plainTextToken;
        $this->audit->recordSuccess($user, provider: 'password', request: $request);

        return response()->json([
            'user'  => $this->formatUser($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->formatUser($request->user())]);
    }

    /**
     * POST /api/forgot-password
     * Send a password reset link to the given email. Always returns 200 to
     * avoid leaking which emails are registered (email enumeration defence).
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'              => 'required|email',
            'turnstile_token'    => $this->turnstile->isEnabled() ? 'required|string' : 'nullable|string',
        ]);

        if (! $this->turnstile->verify($data['turnstile_token'] ?? null, $request->ip())) {
            throw ValidationException::withMessages([
                'turnstile_token' => ['La vérification anti-bot a échoué. Réessayez.'],
            ]);
        }

        $user = User::where('email', $data['email'])->first();
        if ($user) {
            // Sends the reset link via the configured mailer using our FR
            // notification (overrides the default English one).
            $status = Password::sendResetLink(['email' => $data['email']]);
            // We do not branch on $status — see comment above (no enumeration).
            // Status values: PASSWORD_RESET_LINK_SENT, INVALID_USER, THROTTLED.
        }

        return response()->json([
            'message' => 'Si un compte avec cette adresse existe, un email contenant un lien de réinitialisation vient d\'être envoyé.',
        ]);
    }

    /**
     * POST /api/reset-password
     * Consume a reset token and set a new password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'                 => 'required|email',
            'token'                 => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Revoke any existing API tokens so a stolen token doesn't
                // survive the password change.
                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [match ($status) {
                    Password::INVALID_TOKEN => 'Le lien de réinitialisation est invalide ou expiré.',
                    Password::INVALID_USER  => 'Aucun compte ne correspond à cette adresse.',
                    default                  => 'La réinitialisation a échoué. Veuillez recommencer.',
                }],
            ]);
        }

        // Auto-login the user after a successful reset: convenient on web/mobile.
        $user  = User::where('email', $data['email'])->firstOrFail();
        $token = $user->createToken('mobile')->plainTextToken;
        $this->audit->recordSuccess($user, provider: 'password-reset', event: 'reset', request: $request);

        return response()->json([
            'message' => 'Mot de passe réinitialisé avec succès.',
            'user'    => $this->formatUser($user),
            'token'   => $token,
        ]);
    }

    private function formatUser(User $user): array
    {
        $user->loadMissing(['company', 'school']);

        return [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'phone'           => $user->phone,
            'role'            => $user->role,
            'profile_picture' => $user->profile_picture,
            'cv_url'          => $user->cv_url,
            'last_login_at'   => $user->last_login_at,
            'created_at'      => $user->created_at,
            'has_company'     => $user->company !== null,
            'has_school'      => $user->school !== null,
        ];
    }
}
