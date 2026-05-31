<?php

use App\Models\User;
use App\Notifications\ResetPasswordFr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

/*
 * Coverage matrix for the JSON API password-reset flow (Phase 1).
 *
 *   POST /api/forgot-password
 *     - sends a notification when email exists
 *     - returns 200 even for unknown emails (anti-enumeration)
 *     - rejects malformed payloads (422)
 *
 *   POST /api/reset-password
 *     - successful reset returns a fresh Sanctum token + user
 *     - rejects invalid / expired tokens
 *     - rejects unconfirmed / too-short passwords
 *     - revokes other existing tokens after a successful reset
 */

beforeEach(function () {
    // Use the array driver so we never actually try smtp.gmail.com in CI.
    Notification::fake();
});

it('sends a French reset notification for an existing email', function () {
    $user = User::factory()->create(['role' => 'job-seeker']);

    $response = $this->postJson('/api/forgot-password', ['email' => $user->email]);

    $response->assertOk()
        ->assertJsonStructure(['message']);

    Notification::assertSentTo($user, ResetPasswordFr::class);
});

it('returns 200 for unknown emails without leaking existence', function () {
    $response = $this->postJson('/api/forgot-password', ['email' => 'who-am-i@example.com']);

    $response->assertOk()
        ->assertJsonStructure(['message']);

    Notification::assertNothingSent();
});

it('rejects forgot-password without an email', function () {
    $this->postJson('/api/forgot-password', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('rejects forgot-password with malformed email', function () {
    $this->postJson('/api/forgot-password', ['email' => 'not-an-email'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('resets the password with a valid token and returns a Sanctum token', function () {
    $user = User::factory()->create([
        'role'     => 'job-seeker',
        'password' => Hash::make('OLD_PASSWORD'),
    ]);

    // Issue a real token through the Password broker so we hit the same code
    // path the controller uses.
    $token = Password::broker()->createToken($user);

    $response = $this->postJson('/api/reset-password', [
        'email'                 => $user->email,
        'token'                 => $token,
        'password'              => 'BRAND_NEW_pw',
        'password_confirmation' => 'BRAND_NEW_pw',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['message', 'user' => ['id', 'email'], 'token']);

    // The password was actually rotated.
    $user->refresh();
    expect(Hash::check('BRAND_NEW_pw', $user->password))->toBeTrue();
    expect(Hash::check('OLD_PASSWORD', $user->password))->toBeFalse();
});

it('rejects a reset attempt with an invalid token', function () {
    $user = User::factory()->create(['role' => 'job-seeker']);

    $response = $this->postJson('/api/reset-password', [
        'email'                 => $user->email,
        'token'                 => 'totally-not-a-real-token',
        'password'              => 'BRAND_NEW_pw',
        'password_confirmation' => 'BRAND_NEW_pw',
    ]);

    $response->assertStatus(422)->assertJsonValidationErrors(['email']);
});

it('rejects mismatched password and confirmation', function () {
    $user = User::factory()->create(['role' => 'job-seeker']);
    $token = Password::broker()->createToken($user);

    $this->postJson('/api/reset-password', [
        'email'                 => $user->email,
        'token'                 => $token,
        'password'              => 'BRAND_NEW_pw',
        'password_confirmation' => 'NOT_THE_SAME',
    ])->assertStatus(422)->assertJsonValidationErrors(['password']);
});

it('rejects passwords shorter than 8 chars', function () {
    $user = User::factory()->create(['role' => 'job-seeker']);
    $token = Password::broker()->createToken($user);

    $this->postJson('/api/reset-password', [
        'email'                 => $user->email,
        'token'                 => $token,
        'password'              => 'short',
        'password_confirmation' => 'short',
    ])->assertStatus(422)->assertJsonValidationErrors(['password']);
});

it('revokes existing Sanctum tokens after a successful reset', function () {
    $user = User::factory()->create([
        'role'     => 'job-seeker',
        'password' => Hash::make('OLD_PASSWORD'),
    ]);

    // Simulate active sessions: two existing access tokens.
    $user->createToken('phone-1');
    $user->createToken('phone-2');
    expect($user->tokens()->count())->toBe(2);

    $token = Password::broker()->createToken($user);

    $this->postJson('/api/reset-password', [
        'email'                 => $user->email,
        'token'                 => $token,
        'password'              => 'BRAND_NEW_pw',
        'password_confirmation' => 'BRAND_NEW_pw',
    ])->assertOk();

    // The 2 old tokens are gone; only the brand-new one issued at reset-time
    // remains (so the user is auto-logged-in on whichever device they reset
    // from, but every other session is invalidated).
    $remaining = $user->tokens()->pluck('name');
    expect($remaining)->toHaveCount(1)
        ->and($remaining->first())->toBe('mobile');
});
