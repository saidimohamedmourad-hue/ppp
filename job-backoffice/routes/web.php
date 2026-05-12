<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobCategoryController;
use App\Http\Controllers\JobVacancyController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\TrainingCategoryController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\TrainingApplicationController;

// Dashboard — admin, company-owner, school-owner
Route::middleware(['auth', 'role:admin,company-owner,school-owner'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});

// Jobs + schools admin (not school-owner)
Route::middleware(['auth', 'role:admin,company-owner'])->group(function () {
    Route::resource('job-application', JobApplicationController::class);
    route::put('job-application/{id}/restore', [JobApplicationController::class, 'restore'])->name('job-application.restore');

    Route::resource('job-vacancy', JobVacancyController::class);
    route::put('job-vacancy/{id}/restore', [JobVacancyController::class, 'restore'])->name('job-vacancy.restore');

    Route::resource('school', SchoolController::class);
    Route::put('school/{id}/restore', [SchoolController::class, 'restore'])->name('school.restore');
});

// Training lists — school-owner sees only own school (controller)
Route::middleware(['auth', 'role:admin,company-owner,school-owner'])->group(function () {
    Route::resource('training-application', TrainingApplicationController::class);
    Route::put('training-application/{id}/restore', [TrainingApplicationController::class, 'restore'])->name('training-application.restore');

    Route::resource('training-session', TrainingSessionController::class);
    Route::put('training-session/{id}/restore', [TrainingSessionController::class, 'restore'])->name('training-session.restore');
});

// company routes
Route::middleware(['auth', 'role:company-owner'])->group(function () {
    Route::get('/my-company', [CompanyController::class, 'show'])->name('my-company.show');
    Route::get('/my-company/edit', [CompanyController::class, 'edit'])->name('my-company.edit');
    Route::put('/my-company', [CompanyController::class, 'update'])->name('my-company.update');

});

// school routes
Route::middleware(['auth', 'role:school-owner'])->group(function () {
    Route::get('/my-school', [SchoolController::class, 'show'])->name('my-school.show');
    Route::get('/my-school/edit', [SchoolController::class, 'edit'])->name('my-school.edit');
    Route::put('/my-school', [SchoolController::class, 'update'])->name('my-school.update');
});

// admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    // users
    Route::resource('user', UserController::class);
    Route::put('user/{id}/restore', [UserController::class, 'restore'])->name('user.restore');
    // companies
    Route::resource('company', CompanyController::class);
    Route::put('company/{id}/restore', [CompanyController::class, 'restore'])->name('company.restore');
    // job categories
    Route::resource('job-category', JobCategoryController::class);
    Route::put('job-category/{id}/restore', [JobCategoryController::class, 'restore'])->name('job-category.restore');

    // training categories
    Route::resource('training-category', TrainingCategoryController::class);
    Route::put('training-category/{id}/restore', [TrainingCategoryController::class, 'restore'])->name('training-category.restore');

});

require __DIR__.'/auth.php';
