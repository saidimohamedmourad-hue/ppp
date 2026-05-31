<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

/*
 * Phase 6 — the LoginAuditService is invisible to API consumers, so we test
 * the side-effect: after each auth flow, the right row appears in the
 * `login_audits` table.
 */

beforeEach(fn () => Notification::fake());

it('records a successful password login', function () {
    $user = User::factory()->create([
        'role'     => 'job-seeker',
        'password' => Hash::make('hunter2hunter2'),
    ]);

    $this->postJson('/api/login', [
        'email'    => $user->email,
        'password' => 'hunter2hunter2',
    ])->assertOk();

    $this->assertDatabaseHas('login_audits', [
        'user_id'  => $user->id,
        'provider' => 'password',
        'event'    => 'login',
        'success'  => true,
    ]);
});

it('records a refused password login without leaking which email exists', function () {
    $this->postJson('/api/login', [
        'email'    => 'ghost@example.com',
        'password' => 'whatever',
    ])->assertStatus(422);

    $this->assertDatabaseHas('login_audits', [
        'user_id'         => null,
        'provider'        => 'password',
        'event'           => 'refused',
        'success'         => false,
        'attempted_email' => 'ghost@example.com',
        'failure_reason'  => 'bad-credentials',
    ]);
});

it('records a successful password reset', function () {
    $user = User::factory()->create([
        'role'     => 'job-seeker',
        'password' => Hash::make('OLD_PASSWORD'),
    ]);
    $token = Password::broker()->createToken($user);

    $this->postJson('/api/reset-password', [
        'email'                 => $user->email,
        'token'                 => $token,
        'password'              => 'BRAND_NEW_pw',
        'password_confirmation' => 'BRAND_NEW_pw',
    ])->assertOk();

    $this->assertDatabaseHas('login_audits', [
        'user_id'  => $user->id,
        'provider' => 'password-reset',
        'event'    => 'reset',
        'success'  => true,
    ]);
});

it('records a successful Google sign-in', function () {
    config(['services.google.web_client_id' => 'fake']);
    Http::fake([
        'https://openidconnect.googleapis.com/v1/userinfo*' => Http::response([
            'sub'   => 'g-uid-200',
            'email' => 'auditme@example.com',
            'name'  => 'Audit Me',
        ], 200),
    ]);

    $this->postJson('/api/auth/google', ['access_token' => 'ya29.fake'])->assertOk();

    $audit = DB::table('login_audits')
        ->where('provider', 'google')
        ->where('success', true)
        ->first();

    expect($audit)->not->toBeNull();
    expect($audit->event)->toBe('login');
});

it('captures the client IP and user agent', function () {
    $user = User::factory()->create([
        'role'     => 'job-seeker',
        'password' => Hash::make('hunter2hunter2'),
    ]);

    $this->withServerVariables([
        'REMOTE_ADDR'     => '203.0.113.42',
        'HTTP_USER_AGENT' => 'CustomTestUA/1.0',
    ])->postJson('/api/login', [
        'email'    => $user->email,
        'password' => 'hunter2hunter2',
    ])->assertOk();

    $row = DB::table('login_audits')->where('user_id', $user->id)->first();

    expect($row->ip)->toBe('203.0.113.42');
    expect($row->user_agent)->toBe('CustomTestUA/1.0');
});
