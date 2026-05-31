<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

/*
 * Phase 6 — verify the named rate limiters actually fire under sustained
 * attack patterns. We clear the in-memory limiter between tests so each
 * scenario starts from zero.
 */

beforeEach(function () {
    Notification::fake();
    RateLimiter::clear('login-ip:127.0.0.1');
    RateLimiter::clear('forgot-ip:127.0.0.1');
});

it('throttles /login after 5 bad attempts from the same IP', function () {
    User::factory()->create([
        'role'     => 'job-seeker',
        'email'    => 'target@example.com',
        'password' => Hash::make('correctcorrect'),
    ]);

    // 5 failed attempts — each rejected with 422 by bad-credentials.
    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/login', [
            'email'    => 'target@example.com',
            'password' => 'wrong-'.$i,
        ])->assertStatus(422);
    }

    // 6th attempt — even with the *correct* password — must be throttled.
    $this->postJson('/api/login', [
        'email'    => 'target@example.com',
        'password' => 'correctcorrect',
    ])->assertStatus(429);
});

it('throttles /login per email even if attacker rotates source IPs', function () {
    User::factory()->create([
        'role'     => 'job-seeker',
        'email'    => 'rotation@example.com',
        'password' => Hash::make('correctcorrect'),
    ]);

    // 5 failed attempts coming from "different" IPs (we just bust the limiter
    // bucket of each IP between calls to simulate fresh IPs).
    for ($i = 0; $i < 5; $i++) {
        RateLimiter::clear('login-ip:127.0.0.1');

        $this->postJson('/api/login', [
            'email'    => 'rotation@example.com',
            'password' => 'wrong-'.$i,
        ])->assertStatus(422);
    }
    RateLimiter::clear('login-ip:127.0.0.1');

    // The per-email bucket should be exhausted — 6th attempt blocked.
    $this->postJson('/api/login', [
        'email'    => 'rotation@example.com',
        'password' => 'correctcorrect',
    ])->assertStatus(429);
});

it('throttles /forgot-password after 3 requests for the same email', function () {
    $u = User::factory()->create(['role' => 'job-seeker']);

    for ($i = 0; $i < 3; $i++) {
        $this->postJson('/api/forgot-password', ['email' => $u->email])->assertOk();
    }

    // 4th request for the same email — blocked.
    $this->postJson('/api/forgot-password', ['email' => $u->email])->assertStatus(429);
});
