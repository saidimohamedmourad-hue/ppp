<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobCategoryController;
use App\Http\Controllers\JobVacancyController;
use App\Http\Controllers\CompanyController; 


//shared routes
Route::middleware(['auth','role:admin,company-owner'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
   
    //job applications
    Route::resource('job-application', JobApplicationController::class);
        route::put('job-application/{id}/restore', [JobApplicationController::class, 'restore'])->name('job-application.restore');

    //job vacancies
    Route::resource('job-vacancy', JobVacancyController::class);
       route::put('job-vacancy/{id}/restore', [JobVacancyController::class, 'restore'])->name('job-vacancy.restore');

    
   
});
// company routes
Route::middleware(['auth','role:company-owner'])->group(function () {
    Route::get('/my-company', [CompanyController::class, 'show'])->name('my-company.show');
    Route::get('/my-company/edit', [CompanyController::class, 'edit'])->name('my-company.edit');
    Route::put('/my-company', [CompanyController::class, 'update'])->name('my-company.update');

});

//admin routes
Route::middleware(['auth','role:admin'])->group(function () {
     //users
    Route::resource('user', UserController::class );
    Route::put('user/{id}/restore', [UserController::class, 'restore'])->name('user.restore');
        //companies
    Route::resource('company', CompanyController::class);  
    Route::put('company/{id}/restore', [CompanyController::class, 'restore'])->name('company.restore');
        //job categories
    Route::resource('job-category', JobCategoryController::class);
    Route::put('job-category/{id}/restore', [JobCategoryController::class, 'restore'])->name('job-category.restore');
     
}) ;




require __DIR__.'/auth.php';
