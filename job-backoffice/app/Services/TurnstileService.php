<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Cloudflare Turnstile verifier.
 *
 * Why Turnstile rather than reCAPTCHA:
 *   - Free, no tracking, GDPR-friendly, no Google footprint.
 *   - Renders an invisible challenge most of the time → no friction for users.
 *
 * If TURNSTILE_SECRET is unset (typical in dev), `verify()` returns true so
 * forgot-password keeps working locally without needing to wire Cloudflare.
 */
class TurnstileService
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function isEnabled(): bool
    {
        return ! empty(config('services.turnstile.secret'));
    }

    /**
     * Returns true when:
     *   - Turnstile is disabled (no secret configured), OR
     *   - the token was issued for our site and is unused, fresh, and from
     *     the expected client IP (if provided).
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }
        if (empty($token)) {
            return false;
        }

        try {
            $resp = Http::asForm()->timeout(5)->post(self::VERIFY_URL, array_filter([
                'secret'   => config('services.turnstile.secret'),
                'response' => $token,
                'remoteip' => $ip,
            ]));
        } catch (ConnectionException) {
            // Fail-closed: if Cloudflare is unreachable, treat as invalid
            // rather than risk being a free-for-all.
            return false;
        }

        return $resp->successful() && (bool) $resp->json('success');
    }
}
