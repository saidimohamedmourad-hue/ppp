<?php

namespace App\Services;

use App\Models\AuthProvider;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Centralizes the "find-or-link-or-create" logic when a user signs in through
 * a social provider (Google, Facebook, Phone…).
 *
 * Matching rules, in order:
 *   1. If this (provider, provider_user_id) pair already exists → return the
 *      attached user. Most common case for repeat sign-ins.
 *   2. Else, if the email returned by the provider matches a local user, we
 *      auto-link: the social provider is added to that existing account, and
 *      the email is marked verified (the provider has already verified it).
 *   3. Else, create a fresh job-seeker account with the provider's data.
 */
class AuthLinkingService
{
    /**
     * @param array<string,mixed> $meta Provider-side payload to persist
     *                                  (verified status, photo URL, …).
     */
    public function findOrCreateFromSocial(
        string $provider,
        string $providerUserId,
        ?string $email,
        ?string $name,
        ?string $avatar,
        array $meta = []
    ): User {
        // Case 1 — already linked.
        $existing = AuthProvider::where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();
        if ($existing) {
            return $existing->user;
        }

        // Case 2 — email matches a local account → auto-link.
        if ($email && $user = User::where('email', $email)->first()) {
            $user->authProviders()->create([
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
                'meta' => $meta,
            ]);

            // The social provider has already verified the email; mirror that.
            if ($user->email_verified_at === null) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            return $user;
        }

        // Case 3 — brand-new account.
        return DB::transaction(function () use ($provider, $providerUserId, $email, $name, $avatar, $meta) {
            $user = User::create([
                'name'              => $name ?? 'Utilisateur',
                'email'             => $email,
                // password stays null — social-only account
                'role'              => 'job-seeker',
                'avatar_url'        => $avatar,
                'email_verified_at' => now(),
            ]);

            $user->authProviders()->create([
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
                'meta' => $meta,
            ]);

            return $user;
        });
    }
}
