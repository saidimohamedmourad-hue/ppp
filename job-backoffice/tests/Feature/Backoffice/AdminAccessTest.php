<?php

use App\Models\User;

/*
 * Smoke tests for the role-based access control on the Laravel Blade
 * backoffice. We're not auditing every method — we just verify that:
 *
 *   - Unauthenticated users are bounced to /login.
 *   - Authenticated users with the wrong role get a 403.
 *   - Authenticated users with the right role render the page (200).
 *
 * The point is to catch regressions when someone touches the role middleware
 * or removes a Route::resource by accident.
 */

it('redirects guests away from /company', function () {
    $this->get('/company')
        ->assertRedirect('/login');
});

it('lets admins reach /company', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/company')->assertOk();
});

it('blocks job-seekers from /company', function () {
    $jobSeeker = User::factory()->create(['role' => 'job-seeker']);

    $this->actingAs($jobSeeker)->get('/company')->assertForbidden();
});

it('lets admins reach /school', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/school')->assertOk();
});

it('blocks job-seekers from /school', function () {
    $jobSeeker = User::factory()->create(['role' => 'job-seeker']);

    $this->actingAs($jobSeeker)->get('/school')->assertForbidden();
});

it('lets admins reach /user', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/user')->assertOk();
});

it('blocks school-owners from /user (admin-only)', function () {
    $school = User::factory()->create(['role' => 'school-owner']);

    $this->actingAs($school)->get('/user')->assertForbidden();
});

it('lets admins reach /job-vacancy and /training-session', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/job-vacancy')->assertOk();
    $this->actingAs($admin)->get('/training-session')->assertOk();
});

it('lets admins reach /job-application', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/job-application')->assertOk();
});

it('redirects to /login when hitting /training-application as guest', function () {
    $this->get('/training-application')->assertRedirect('/login');
});

it('lets admins reach training-application list', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/training-application')->assertOk();
});

it('lets admins reach the job categories CRUD', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/job-category')->assertOk();
    $this->actingAs($admin)->get('/job-category/create')->assertOk();
});

it('lets admins reach the training categories CRUD', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/training-category')->assertOk();
    $this->actingAs($admin)->get('/training-category/create')->assertOk();
});
