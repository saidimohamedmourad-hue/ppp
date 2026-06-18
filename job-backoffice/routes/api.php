<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\CompanyApiController;
use App\Http\Controllers\Api\JobApiController;
use App\Http\Controllers\Api\LinkedAccountsController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\SchoolApiController;
use App\Http\Controllers\Api\TrainingApiController;
use App\Models\JobCategory;
use App\Models\TrainingCategory;
use Illuminate\Support\Facades\Route;

// ─── Public config (what the SPA needs to know before login) ─────────────────
// Returns only non-secret values so it's safe for unauthenticated callers.
Route::get('/config', function () {
    return [
        'turnstile' => [
            'enabled'  => ! empty(config('services.turnstile.secret')),
            'site_key' => config('services.turnstile.site_key'),
        ],
        // Liste fermée des niveaux d'études (Algérie) — partagée web + Flutter.
        'education_levels' => config('education.levels'),
    ];
});

// ─── Auth (public) ────────────────────────────────────────────────────────────
Route::post('/register', [AuthApiController::class, 'register']);
// Phase 6 hardening: brute-force protection by IP AND by email.
// See `login` limiter in AppServiceProvider for the actual budget.
Route::middleware('throttle:login')->post('/login', [AuthApiController::class, 'login']);

// Password reset by email (Phase 1 — see PLAN_AUTH_SOCIAL.md).
// Per-IP + per-email rate limit (see `forgot-password` limiter).
Route::middleware('throttle:forgot-password')->group(function () {
    Route::post('/forgot-password', [AuthApiController::class, 'forgotPassword']);
});
// Reset has its own simpler per-IP limit — token validation already gates abuse.
Route::middleware('throttle:10,60')->post('/reset-password', [AuthApiController::class, 'resetPassword']);

// Social sign-in (Phases 2–3). Each client sends a provider-issued token; we
// verify it server-side and return a Sanctum token.
Route::prefix('auth')->middleware('throttle:30,60')->group(function () {
    Route::post('/google',   [SocialAuthController::class, 'google']);
    Route::post('/facebook', [SocialAuthController::class, 'facebook']);
});

// ─── Public ───────────────────────────────────────────────────────────────────
Route::get('/job-categories', fn () => response()->json(['data' => JobCategory::orderBy('name')->get()]));
Route::get('/training-categories', fn () => response()->json(['data' => TrainingCategory::orderBy('name')->get()]));
Route::get('/jobs', [JobApiController::class, 'index']);
Route::get('/jobs/{id}', [JobApiController::class, 'show']);
Route::get('/training-sessions', [TrainingApiController::class, 'index']);
Route::get('/training-sessions/{id}', [TrainingApiController::class, 'show']);
Route::get('/companies', [CompanyApiController::class, 'index']);
Route::get('/companies/{id}', [CompanyApiController::class, 'show']);
Route::get('/schools', [SchoolApiController::class, 'index']);
Route::get('/schools/{id}', [SchoolApiController::class, 'show']);

// ─── Authenticated ────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/me', [AuthApiController::class, 'me']);

    // Notifications (in-app bell + dropdown)
    Route::get('/notifications',                    [NotificationApiController::class, 'index']);
    Route::get('/notifications/unread-count',       [NotificationApiController::class, 'unreadCount']);
    Route::put('/notifications/read-all',           [NotificationApiController::class, 'markAllAsRead']);
    Route::put('/notifications/{id}/read',          [NotificationApiController::class, 'markAsRead']);
    Route::delete('/notifications/{id}',            [NotificationApiController::class, 'destroy']);

    // Phase 5 — linked social providers + set local password
    Route::prefix('profile/auth-providers')->group(function () {
        Route::get('/', [LinkedAccountsController::class, 'index']);
        Route::post('/google',   [LinkedAccountsController::class, 'linkGoogle']);
        Route::post('/facebook', [LinkedAccountsController::class, 'linkFacebook']);
        Route::delete('/{id}',   [LinkedAccountsController::class, 'unlink']);
    });
    Route::post('/profile/password-init', [LinkedAccountsController::class, 'setPassword']);

    // Profile
    Route::get('/profile', [ProfileApiController::class, 'show']);
    Route::put('/profile', [ProfileApiController::class, 'update']);
    Route::put('/profile/password', [ProfileApiController::class, 'updatePassword']);
    Route::post('/profile/photo', [ProfileApiController::class, 'uploadProfilePicture']);
    Route::get('/resumes', [ProfileApiController::class, 'myResumes']);
    Route::post('/resumes', [ProfileApiController::class, 'uploadResume']);
    Route::delete('/resumes/{id}', [ProfileApiController::class, 'deleteResume']);

    // ─── Job-Seeker ───────────────────────────────────────────────────────────
    Route::middleware('role:job-seeker')->group(function () {
        Route::post('/jobs/{id}/apply', [JobApiController::class, 'apply']);
        Route::get('/my/job-applications', [JobApiController::class, 'myApplications']);
        Route::delete('/my/job-applications/{id}', [JobApiController::class, 'withdrawApplication']);
        Route::post('/training-sessions/{id}/apply', [TrainingApiController::class, 'apply']);
        Route::get('/my/training-applications', [TrainingApiController::class, 'myApplications']);
        Route::delete('/my/training-applications/{id}', [TrainingApiController::class, 'withdrawApplication']);

        // Recommandations selon le CV (spécialité, catégorie, niveau).
        Route::get('/me/recommended-trainings', [TrainingApiController::class, 'recommended']);
        Route::get('/me/recommended-jobs', [JobApiController::class, 'recommended']);
    });

    // ─── Company-Owner ────────────────────────────────────────────────────────
    Route::middleware('role:company-owner')->group(function () {
        Route::get('/company/dashboard', [CompanyApiController::class, 'dashboard']);
        Route::get('/company/me', [CompanyApiController::class, 'myCompany']);
        Route::put('/company/me', [CompanyApiController::class, 'updateMyCompany']);
        Route::get('/company/jobs', [JobApiController::class, 'companyJobs']);
        Route::post('/company/jobs', [JobApiController::class, 'store']);
        Route::put('/company/jobs/{id}', [JobApiController::class, 'update']);
        Route::delete('/company/jobs/{id}', [JobApiController::class, 'destroy']);
        Route::get('/company/jobs/{id}/applicants', [JobApiController::class, 'jobApplicants']);
        Route::put('/company/applications/{id}/status', [JobApiController::class, 'updateApplicationStatus']);
    });

    // ─── School-Owner ─────────────────────────────────────────────────────────
    Route::middleware('role:school-owner')->group(function () {
        Route::get('/school/dashboard', [SchoolApiController::class, 'dashboard']);
        Route::get('/school/me', [SchoolApiController::class, 'mySchool']);
        Route::put('/school/me', [SchoolApiController::class, 'updateMySchool']);
        Route::get('/school/training-sessions', [TrainingApiController::class, 'schoolSessions']);
        Route::post('/school/training-sessions', [TrainingApiController::class, 'storeSession']);
        Route::put('/school/training-sessions/{id}', [TrainingApiController::class, 'updateSession']);
        Route::delete('/school/training-sessions/{id}', [TrainingApiController::class, 'destroySession']);
        Route::get('/school/training-sessions/{id}/applicants', [TrainingApiController::class, 'sessionApplicants']);
        Route::put('/school/training-applications/{id}/status', [TrainingApiController::class, 'updateApplicationStatus']);
    });

    // ─── Admin ────────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/analytics', [AdminApiController::class, 'analytics']);
        Route::get('/admin/users', [AdminApiController::class, 'users']);
        Route::delete('/admin/users/{id}', [AdminApiController::class, 'deleteUser']);
        Route::get('/admin/companies', [AdminApiController::class, 'companies']);
        Route::get('/admin/schools', [AdminApiController::class, 'schools']);
        Route::delete('/admin/jobs/{id}', [AdminApiController::class, 'deleteJob']);
        Route::delete('/admin/training/{id}', [AdminApiController::class, 'deleteTraining']);
    });
});
