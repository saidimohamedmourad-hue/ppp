<?php

use App\Models\AuthProvider;
use App\Models\User;
use App\Services\AuthLinkingService;

/*
 * Unit-style coverage of the 3 matching branches. We treat this as a Feature
 * test so we get RefreshDatabase + can hit real Eloquent relations.
 */

it('case 1: returns existing user when the (provider, sub) pair already exists', function () {
    $user = User::factory()->create(['role' => 'job-seeker']);
    AuthProvider::create([
        'user_id'          => $user->id,
        'provider'         => 'google',
        'provider_user_id' => 'sub-AAA',
    ]);

    $found = app(AuthLinkingService::class)->findOrCreateFromSocial(
        provider:       'google',
        providerUserId: 'sub-AAA',
        email:          'whatever@example.com',
        name:           'Whatever',
        avatar:         null,
    );

    expect($found->id)->toBe($user->id);

    // No second AuthProvider row created.
    expect(AuthProvider::count())->toBe(1);
});

it('case 2: auto-links the social provider when email matches a local account', function () {
    $local = User::factory()->create([
        'role'              => 'job-seeker',
        'email'             => 'shared@example.com',
        'email_verified_at' => null,
    ]);

    $linked = app(AuthLinkingService::class)->findOrCreateFromSocial(
        provider:       'google',
        providerUserId: 'sub-BBB',
        email:          'shared@example.com',
        name:           'From Google',
        avatar:         'https://example.test/x.png',
    );

    expect($linked->id)->toBe($local->id);

    expect(AuthProvider::where('user_id', $local->id)
        ->where('provider', 'google')->exists())->toBeTrue();

    // Email is now marked verified because the social IdP vouched for it.
    expect($linked->refresh()->email_verified_at)->not->toBeNull();

    // No duplicate user was created.
    expect(User::where('email', 'shared@example.com')->count())->toBe(1);
});

it('case 3: creates a brand-new job-seeker when nothing matches', function () {
    $created = app(AuthLinkingService::class)->findOrCreateFromSocial(
        provider:       'facebook',
        providerUserId: 'fb-CCC',
        email:          'newcomer@example.com',
        name:           'New Comer',
        avatar:         'https://example.test/avatar.png',
    );

    expect($created)->toBeInstanceOf(User::class);
    expect($created->email)->toBe('newcomer@example.com');
    expect($created->role)->toBe('job-seeker');
    expect($created->password)->toBeNull();   // social-only, no local password
    expect($created->email_verified_at)->not->toBeNull();

    expect(AuthProvider::where('user_id', $created->id)
        ->where('provider', 'facebook')
        ->where('provider_user_id', 'fb-CCC')->exists())->toBeTrue();
});

it('falls back to "Utilisateur" when the provider sent no name', function () {
    $created = app(AuthLinkingService::class)->findOrCreateFromSocial(
        provider:       'google',
        providerUserId: 'sub-DDD',
        email:          'anon@example.com',
        name:           null,
        avatar:         null,
    );

    expect($created->name)->toBe('Utilisateur');
});
