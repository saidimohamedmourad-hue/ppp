<?php

use App\Models\User;
use App\Notifications\JobApplicationReceived;
use Illuminate\Notifications\DatabaseNotification;
use Laravel\Sanctum\Sanctum;

/*
 * Smoke coverage for the notification bell endpoints.
 *
 * Notifications are stored by Laravel's `database` channel into the
 * `notifications` table — we exercise the read paths (list, unread count)
 * and the mutation paths (mark read, mark all, delete).
 *
 * We don't test the fire-on-apply path here — that's covered by the apply
 * Pest tests when they're added; the controller hook is mechanical.
 */

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'job-seeker']);
    Sanctum::actingAs($this->user);
});

it('lists notifications for the current user (and only them)', function () {
    $other = User::factory()->create(['role' => 'job-seeker']);

    DatabaseNotification::create([
        'id'              => (string) \Illuminate\Support\Str::uuid(),
        'type'            => JobApplicationReceived::class,
        'notifiable_type' => $this->user->getMorphClass(),
        'notifiable_id'   => $this->user->id,
        'data'            => ['icon' => '📨', 'title' => 'Mine'],
    ]);
    DatabaseNotification::create([
        'id'              => (string) \Illuminate\Support\Str::uuid(),
        'type'            => JobApplicationReceived::class,
        'notifiable_type' => $other->getMorphClass(),
        'notifiable_id'   => $other->id,
        'data'            => ['icon' => '📨', 'title' => 'Theirs'],
    ]);

    $this->getJson('/api/notifications')
        ->assertOk()
        ->assertJsonPath('data.0.data.title', 'Mine')
        ->assertJsonPath('meta.total', 1);
});

it('returns the unread count and ignores read rows', function () {
    DatabaseNotification::create([
        'id'              => (string) \Illuminate\Support\Str::uuid(),
        'type'            => JobApplicationReceived::class,
        'notifiable_type' => $this->user->getMorphClass(),
        'notifiable_id'   => $this->user->id,
        'data'            => ['title' => 'Unread A'],
    ]);
    DatabaseNotification::create([
        'id'              => (string) \Illuminate\Support\Str::uuid(),
        'type'            => JobApplicationReceived::class,
        'notifiable_type' => $this->user->getMorphClass(),
        'notifiable_id'   => $this->user->id,
        'data'            => ['title' => 'Unread B'],
    ]);
    DatabaseNotification::create([
        'id'              => (string) \Illuminate\Support\Str::uuid(),
        'type'            => JobApplicationReceived::class,
        'notifiable_type' => $this->user->getMorphClass(),
        'notifiable_id'   => $this->user->id,
        'data'            => ['title' => 'Read'],
        'read_at'         => now(),
    ]);

    $this->getJson('/api/notifications/unread-count')
        ->assertOk()
        ->assertJson(['count' => 2]);
});

it('filter=unread skips read rows', function () {
    DatabaseNotification::create([
        'id'              => (string) \Illuminate\Support\Str::uuid(),
        'type'            => JobApplicationReceived::class,
        'notifiable_type' => $this->user->getMorphClass(),
        'notifiable_id'   => $this->user->id,
        'data'            => ['title' => 'Unread'],
    ]);
    DatabaseNotification::create([
        'id'              => (string) \Illuminate\Support\Str::uuid(),
        'type'            => JobApplicationReceived::class,
        'notifiable_type' => $this->user->getMorphClass(),
        'notifiable_id'   => $this->user->id,
        'data'            => ['title' => 'Read'],
        'read_at'         => now(),
    ]);

    $this->getJson('/api/notifications?filter=unread')
        ->assertOk()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.0.data.title', 'Unread');
});

it('marks a single notification as read', function () {
    $id = (string) \Illuminate\Support\Str::uuid();
    DatabaseNotification::create([
        'id'              => $id,
        'type'            => JobApplicationReceived::class,
        'notifiable_type' => $this->user->getMorphClass(),
        'notifiable_id'   => $this->user->id,
        'data'            => ['title' => 'A'],
    ]);

    $this->putJson("/api/notifications/{$id}/read")->assertOk();

    expect(DatabaseNotification::find($id)->read_at)->not->toBeNull();
});

it('returns 404 when marking a notification that belongs to another user', function () {
    $other = User::factory()->create();
    $id = (string) \Illuminate\Support\Str::uuid();
    DatabaseNotification::create([
        'id'              => $id,
        'type'            => JobApplicationReceived::class,
        'notifiable_type' => $other->getMorphClass(),
        'notifiable_id'   => $other->id,
        'data'            => ['title' => 'Theirs'],
    ]);

    $this->putJson("/api/notifications/{$id}/read")->assertNotFound();
});

it('marks all notifications as read', function () {
    for ($i = 0; $i < 3; $i++) {
        DatabaseNotification::create([
            'id'              => (string) \Illuminate\Support\Str::uuid(),
            'type'            => JobApplicationReceived::class,
            'notifiable_type' => $this->user->getMorphClass(),
            'notifiable_id'   => $this->user->id,
            'data'            => ['title' => 'A'.$i],
        ]);
    }

    $this->putJson('/api/notifications/read-all')->assertOk();
    expect($this->user->fresh()->unreadNotifications()->count())->toBe(0);
});

it('deletes a notification', function () {
    $id = (string) \Illuminate\Support\Str::uuid();
    DatabaseNotification::create([
        'id'              => $id,
        'type'            => JobApplicationReceived::class,
        'notifiable_type' => $this->user->getMorphClass(),
        'notifiable_id'   => $this->user->id,
        'data'            => ['title' => 'A'],
    ]);

    $this->deleteJson("/api/notifications/{$id}")->assertOk();
    expect(DatabaseNotification::find($id))->toBeNull();
});

it('requires authentication', function () {
    Sanctum::actingAs(User::factory()->create()); // resets
    auth()->forgetGuards();

    // Hit without a token.
    $this->getJson('/api/notifications')->assertUnauthorized();
});
