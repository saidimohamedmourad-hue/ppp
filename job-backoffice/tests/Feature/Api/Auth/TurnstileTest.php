<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

/*
 * Phase 6 — Cloudflare Turnstile integration.
 *
 * We rely on a single env-driven switch (`services.turnstile.secret`) so dev
 * environments where it's unset keep behaving like before.
 */

beforeEach(function () {
    Notification::fake();
    RateLimiter::clear('forgot-ip:127.0.0.1');
    // Each test sets the cap-name/email keys it needs.
});

it('skips Turnstile verification when no secret is configured', function () {
    config(['services.turnstile.secret' => null]);

    $u = User::factory()->create(['role' => 'job-seeker']);

    $this->postJson('/api/forgot-password', ['email' => $u->email])
        ->assertOk();
});

it('requires a Turnstile token when Turnstile is enabled', function () {
    config(['services.turnstile.secret' => 'fake-secret']);
    $u = User::factory()->create(['role' => 'job-seeker']);

    $this->postJson('/api/forgot-password', ['email' => $u->email])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['turnstile_token']);
});

it('rejects forgot-password when Cloudflare says the token is invalid', function () {
    config(['services.turnstile.secret' => 'fake-secret']);
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => false], 200),
    ]);
    $u = User::factory()->create(['role' => 'job-seeker']);

    $this->postJson('/api/forgot-password', [
        'email'           => $u->email,
        'turnstile_token' => 'fake-widget-token',
    ])->assertStatus(422)
      ->assertJsonValidationErrors(['turnstile_token']);
});

it('accepts forgot-password when Cloudflare validates the token', function () {
    config(['services.turnstile.secret' => 'fake-secret']);
    Http::fake([
        'challenges.cloudflare.com/*' => Http::response(['success' => true], 200),
    ]);
    $u = User::factory()->create(['role' => 'job-seeker']);

    $this->postJson('/api/forgot-password', [
        'email'           => $u->email,
        'turnstile_token' => 'valid-widget-token',
    ])->assertOk();
});

it('exposes turnstile state and site_key via /api/config', function () {
    config([
        'services.turnstile.secret'   => 'fake-secret',
        'services.turnstile.site_key' => '0xPUBLIC123',
    ]);

    $this->getJson('/api/config')
        ->assertOk()
        ->assertJson([
            'turnstile' => ['enabled' => true, 'site_key' => '0xPUBLIC123'],
        ]);
});
