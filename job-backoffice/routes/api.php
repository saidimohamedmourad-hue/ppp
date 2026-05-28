<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CompanyApiController;
use App\Http\Controllers\Api\JobApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Api\SchoolApiController;
use App\Http\Controllers\Api\TrainingApiController;
use App\Models\JobCategory;
use App\Models\TrainingCategory;
use Illuminate\Support\Facades\Route;

// ─── Auth (public) ────────────────────────────────────────────────────────────
Route::post('/register', [AuthApiController::class, 'register']);
Route::post('/login', [AuthApiController::class, 'login']);

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
