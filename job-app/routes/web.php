<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobVacancyController;
Route::get('/', function () {
    return view('welcome');
});



Route::middleware(['auth','role:job-seeker'])->group(function () {
    route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
    route::get('/job-applications',[JobApplicationController::class,'index'])->name('job-applications.index');
    route::get('/job-vacancy/{id}',[JobVacancyController::class,'show'])->name('job-vacancy.show');
    route::get('/job-vacancy/{id}/apply', [JobVacancyController::class, 'apply'])->name('job-vacancy.apply');
    route::post('/job-vacancy/{id}/apply', [JobVacancyController::class, 'processApllication'])->name('job-vacancy.processApllication');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Test OpenAI
    // Route::get('/test-openai', [JobVacancyController::class, 'testOpenAI'])->name('test-openai');
});

require __DIR__.'/auth.php';
