<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Records every auth-related event in the `login_audits` table.
 *
 * The controllers stay thin — they just call recordSuccess() /
 * recordFailure() with whatever context they have, and this service handles
 * the IP / UA extraction and the actual DB insert.
 *
 * We swallow exceptions on purpose: if logging fails (e.g. DB temporarily
 * down) we don't want to break a successful login.
 */
class LoginAuditService
{
    public function recordSuccess(
        User $user,
        string $provider,
        string $event = 'login',
        ?Request $request = null,
    ): void {
        $this->insert([
            'user_id'         => $user->id,
            'attempted_email' => $user->email,
            'provider'        => $provider,
            'event'           => $event,
            'success'         => true,
            'failure_reason'  => null,
        ], $request);
    }

    public function recordFailure(
        string $provider,
        ?string $attemptedEmail = null,
        ?string $reason = null,
        ?Request $request = null,
    ): void {
        $this->insert([
            'user_id'         => null,
            'attempted_email' => $attemptedEmail,
            'provider'        => $provider,
            'event'           => 'refused',
            'success'         => false,
            'failure_reason'  => $reason ? Str::limit($reason, 120, '') : null,
        ], $request);
    }

    /**
     * @param  array<string,mixed>  $row
     */
    private function insert(array $row, ?Request $request): void
    {
        $request ??= request();

        try {
            DB::table('login_audits')->insert(array_merge($row, [
                'id'         => (string) Str::uuid(),
                'ip'         => $request?->ip(),
                'user_agent' => $request ? Str::limit((string) $request->userAgent(), 500, '') : null,
                'created_at' => now(),
            ]));
        } catch (\Throwable) {
            // Never let audit logging break the actual auth flow.
        }
    }
}
