<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

/*
 * Coverage matrix for the social sign-in endpoints (Phases 2 + 3).
 *
 *   POST /api/auth/google
 *     - access_token path: fetches userinfo, returns Sanctum token
 *     - id_token path: hits tokeninfo, returns Sanctum token
 *     - links to existing user when email matches
 *     - rejects 401 from Google
 *     - rejects payloads without a token
 *     - rejects userinfo without an email
 *
 *   POST /api/auth/facebook
 *     - happy path: debug_token + /me, returns Sanctum token
 *     - links to existing user when email matches
 *     - rejects when /debug_token says is_valid=false
 *     - rejects when token is from a different app
 *     - rejects when /me returns no email
 *     - returns a configuration error when no app secret is set
 */

beforeEach(function () {
    config([
        'services.google.web_client_id'   => 'fake-google-client-id',
        'services.facebook.client_id'     => 'fake-app-id',
        'services.facebook.client_secret' => 'fake-app-secret',
    ]);
});

// ─── Google ──────────────────────────────────────────────────────────────

it('signs in via Google access_token and creates a new user', function () {
    Http::fake([
        'https://openidconnect.googleapis.com/v1/userinfo*' => Http::response([
            'sub'            => 'google-uid-001',
            'email'          => 'ada@example.com',
            'email_verified' => true,
            'name'           => 'Ada Lovelace',
            'picture'        => 'https://example.test/ada.png',
        ], 200),
    ]);

    $response = $this->postJson('/api/auth/google', ['access_token' => 'ya29.fake']);

    $response->assertOk()
        ->assertJsonStructure(['user' => ['id', 'email'], 'token'])
        ->assertJsonPath('user.email', 'ada@example.com');

    $this->assertDatabaseHas('users',          ['email' => 'ada@example.com']);
    $this->assertDatabaseHas('auth_providers', [
        'provider'         => 'google',
        'provider_user_id' => 'google-uid-001',
    ]);
});

it('signs in via Google id_token by hitting tokeninfo', function () {
    Http::fake([
        'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
            'sub'   => 'google-uid-002',
            'email' => 'grace@example.com',
            'aud'   => 'fake-google-client-id',
            'name'  => 'Grace Hopper',
        ], 200),
    ]);

    $this->postJson('/api/auth/google', ['id_token' => 'fake.jwt.payload'])
        ->assertOk()
        ->assertJsonPath('user.email', 'grace@example.com');

    $this->assertDatabaseHas('auth_providers', ['provider_user_id' => 'google-uid-002']);
});

it('links an existing local user when Google email matches', function () {
    $existing = User::factory()->create([
        'role'  => 'job-seeker',
        'email' => 'reuse@example.com',
    ]);

    Http::fake([
        'https://openidconnect.googleapis.com/v1/userinfo*' => Http::response([
            'sub'   => 'google-uid-003',
            'email' => 'reuse@example.com',
            'name'  => 'Reuse User',
        ], 200),
    ]);

    $this->postJson('/api/auth/google', ['access_token' => 'ya29.fake'])
        ->assertOk()
        ->assertJsonPath('user.id', $existing->id);

    // No duplicate user was created.
    expect(User::where('email', 'reuse@example.com')->count())->toBe(1);

    // The Google provider row is now bound to the *existing* user.
    $this->assertDatabaseHas('auth_providers', [
        'user_id'  => $existing->id,
        'provider' => 'google',
    ]);
});

it('rejects a Google access_token Google says is invalid', function () {
    Http::fake([
        'https://openidconnect.googleapis.com/v1/userinfo*' => Http::response([], 401),
    ]);

    $this->postJson('/api/auth/google', ['access_token' => 'expired'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['access_token']);
});

it('rejects a Google sign-in without any token', function () {
    $this->postJson('/api/auth/google', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['id_token', 'access_token']);
});

it('rejects a Google profile without an email', function () {
    Http::fake([
        'https://openidconnect.googleapis.com/v1/userinfo*' => Http::response([
            'sub'  => 'google-uid-004',
            'name' => 'No Email',
        ], 200),
    ]);

    $this->postJson('/api/auth/google', ['access_token' => 'ya29.fake'])
        ->assertStatus(422);
});

// ─── Facebook ────────────────────────────────────────────────────────────

it('signs in via Facebook and creates a new user', function () {
    Http::fake([
        'https://graph.facebook.com/debug_token*' => Http::response([
            'data' => ['is_valid' => true, 'app_id' => 'fake-app-id'],
        ], 200),
        'https://graph.facebook.com/me*' => Http::response([
            'id'      => 'fb-uid-001',
            'name'    => 'Mark Z',
            'email'   => 'mark@example.com',
            'picture' => ['data' => ['url' => 'https://example.test/m.png']],
        ], 200),
    ]);

    $this->postJson('/api/auth/facebook', ['access_token' => 'EAAfake'])
        ->assertOk()
        ->assertJsonPath('user.email', 'mark@example.com');

    $this->assertDatabaseHas('auth_providers', [
        'provider'         => 'facebook',
        'provider_user_id' => 'fb-uid-001',
    ]);
});

it('rejects when Facebook says the token is not valid', function () {
    Http::fake([
        'https://graph.facebook.com/debug_token*' => Http::response([
            'data' => ['is_valid' => false, 'app_id' => 'fake-app-id'],
        ], 200),
    ]);

    $this->postJson('/api/auth/facebook', ['access_token' => 'EAAexpired'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['access_token']);
});

it('rejects when the token was issued for a different Facebook app', function () {
    Http::fake([
        'https://graph.facebook.com/debug_token*' => Http::response([
            'data' => ['is_valid' => true, 'app_id' => 'someone-elses-app'],
        ], 200),
    ]);

    $this->postJson('/api/auth/facebook', ['access_token' => 'EAAfake'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['access_token']);
});

it('rejects a Facebook profile without an email', function () {
    Http::fake([
        'https://graph.facebook.com/debug_token*' => Http::response([
            'data' => ['is_valid' => true, 'app_id' => 'fake-app-id'],
        ], 200),
        'https://graph.facebook.com/me*' => Http::response([
            'id'   => 'fb-uid-002',
            'name' => 'No Email',
        ], 200),
    ]);

    $this->postJson('/api/auth/facebook', ['access_token' => 'EAAfake'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['access_token']);
});

it('returns a clear error when Facebook is not configured', function () {
    config([
        'services.facebook.client_id'     => null,
        'services.facebook.client_secret' => null,
    ]);

    $this->postJson('/api/auth/facebook', ['access_token' => 'EAAfake'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['access_token']);
});
