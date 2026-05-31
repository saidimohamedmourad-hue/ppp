<?php

use App\Models\AuthProvider;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;

/*
 * Phase 5 — manage linked auth providers from the user profile.
 *
 *   GET    /api/profile/auth-providers   (list + has_password flag)
 *   POST   /api/profile/auth-providers/google   (link a Google account)
 *   POST   /api/profile/auth-providers/facebook (link a FB account)
 *   DELETE /api/profile/auth-providers/{id}     (with last-method guard)
 *   POST   /api/profile/password-init            (set a local password)
 */

beforeEach(function () {
    config([
        'services.google.web_client_id'   => 'fake',
        'services.facebook.client_id'     => 'fake-fb-app',
        'services.facebook.client_secret' => 'fake-fb-secret',
    ]);
});

it('lists current providers and password status for the authenticated user', function () {
    $u = User::factory()->create(['role' => 'job-seeker']);
    AuthProvider::create([
        'user_id' => $u->id, 'provider' => 'google',
        'provider_user_id' => '111122223333',
    ]);
    Sanctum::actingAs($u);

    $this->getJson('/api/profile/auth-providers')
        ->assertOk()
        ->assertJsonPath('has_password', true)
        ->assertJsonPath('providers.0.provider', 'google')
        ->assertJsonPath('providers.0.display_id', '1111…3333');
});

it('links a new Google account when the user is authenticated', function () {
    Http::fake([
        'https://openidconnect.googleapis.com/v1/userinfo*' => Http::response([
            'sub'   => 'new-google-sub',
            'email' => 'ada@example.com',
        ], 200),
    ]);
    $u = User::factory()->create(['role' => 'job-seeker']);
    Sanctum::actingAs($u);

    $this->postJson('/api/profile/auth-providers/google', ['access_token' => 'ya29.fake'])
        ->assertOk()
        ->assertJsonPath('providers.0.provider', 'google');

    $this->assertDatabaseHas('auth_providers', [
        'user_id'  => $u->id,
        'provider' => 'google',
        'provider_user_id' => 'new-google-sub',
    ]);
});

it('refuses to double-link the same provider account', function () {
    $u = User::factory()->create(['role' => 'job-seeker']);
    AuthProvider::create([
        'user_id' => $u->id, 'provider' => 'google',
        'provider_user_id' => 'duplicate-sub',
    ]);
    Http::fake([
        'https://openidconnect.googleapis.com/v1/userinfo*' => Http::response([
            'sub' => 'duplicate-sub', 'email' => 'ada@example.com',
        ], 200),
    ]);
    Sanctum::actingAs($u);

    $this->postJson('/api/profile/auth-providers/google', ['access_token' => 'ya29.fake'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['provider']);
});

it('refuses to link a provider that belongs to someone else', function () {
    $other = User::factory()->create(['role' => 'job-seeker']);
    AuthProvider::create([
        'user_id' => $other->id, 'provider' => 'google',
        'provider_user_id' => 'shared-sub',
    ]);

    $me = User::factory()->create(['role' => 'job-seeker']);
    Sanctum::actingAs($me);

    Http::fake([
        'https://openidconnect.googleapis.com/v1/userinfo*' => Http::response([
            'sub' => 'shared-sub', 'email' => 'collision@example.com',
        ], 200),
    ]);

    $this->postJson('/api/profile/auth-providers/google', ['access_token' => 'ya29.fake'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['provider']);
});

it('links a Facebook account through debug_token + /me', function () {
    Http::fake([
        'https://graph.facebook.com/debug_token*' => Http::response([
            'data' => ['is_valid' => true, 'app_id' => 'fake-fb-app'],
        ], 200),
        'https://graph.facebook.com/me*' => Http::response([
            'id'    => 'fb-uid-77',
            'email' => 'ada@example.com',
            'name'  => 'Ada',
        ], 200),
    ]);
    $u = User::factory()->create(['role' => 'job-seeker']);
    Sanctum::actingAs($u);

    $this->postJson('/api/profile/auth-providers/facebook', ['access_token' => 'EAAfake'])
        ->assertOk()
        ->assertJsonPath('providers.0.provider', 'facebook');
});

it('unlinks a provider when the user still has a password', function () {
    $u = User::factory()->create(['role' => 'job-seeker']);
    $provider = AuthProvider::create([
        'user_id' => $u->id, 'provider' => 'google',
        'provider_user_id' => 'sub-1',
    ]);
    Sanctum::actingAs($u);

    $this->deleteJson("/api/profile/auth-providers/{$provider->id}")
        ->assertOk()
        ->assertJsonPath('providers', []);
});

it('refuses to unlink the only remaining sign-in method for a social-only account', function () {
    $u = User::factory()->create([
        'role'     => 'job-seeker',
        'password' => null,                // social-only account
    ]);
    $provider = AuthProvider::create([
        'user_id' => $u->id, 'provider' => 'google',
        'provider_user_id' => 'sub-1',
    ]);
    Sanctum::actingAs($u);

    $this->deleteJson("/api/profile/auth-providers/{$provider->id}")
        ->assertStatus(422)
        ->assertJsonValidationErrors(['provider']);

    // The provider is still there.
    $this->assertDatabaseHas('auth_providers', ['id' => $provider->id]);
});

it('allows a social-only user to set an initial password', function () {
    $u = User::factory()->create([
        'role'     => 'job-seeker',
        'password' => null,
    ]);
    Sanctum::actingAs($u);

    $this->postJson('/api/profile/password-init', [
        'password'              => 'BRAND_NEW_pw',
        'password_confirmation' => 'BRAND_NEW_pw',
    ])->assertOk()
      ->assertJsonPath('has_password', true);

    expect(Hash::check('BRAND_NEW_pw', $u->refresh()->password))->toBeTrue();
});

it('requires the current password when changing an existing one', function () {
    $u = User::factory()->create([
        'role'     => 'job-seeker',
        'password' => Hash::make('OLD_pass_1'),
    ]);
    Sanctum::actingAs($u);

    // Wrong current → rejected.
    $this->postJson('/api/profile/password-init', [
        'current_password'      => 'WRONG_pass',
        'password'              => 'NEW_pass_8c',
        'password_confirmation' => 'NEW_pass_8c',
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['current_password']);

    // Right current → accepted.
    $this->postJson('/api/profile/password-init', [
        'current_password'      => 'OLD_pass_1',
        'password'              => 'NEW_pass_8c',
        'password_confirmation' => 'NEW_pass_8c',
    ])->assertOk();

    expect(Hash::check('NEW_pass_8c', $u->refresh()->password))->toBeTrue();
});
