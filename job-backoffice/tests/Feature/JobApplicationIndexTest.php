<?php

use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobCategory;
use App\Models\JobVacancy;
use App\Models\Resume;
use App\Models\User;

test('job application index renders when linked vacancy is archived', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $owner = User::factory()->create(['role' => 'company-owner']);

    $company = Company::create([
        'name' => 'Test Company',
        'address' => 'Alger',
        'industry' => 'Tech',
        'ownerId' => $owner->id,
    ]);

    $category = JobCategory::create(['name' => 'Engineering']);

    $vacancy = JobVacancy::create([
        'title' => 'Archived Developer',
        'description' => 'Test role',
        'location' => 'Alger',
        'salary' => 50000,
        'type' => 'CDI',
        'jobCategoryId' => $category->id,
        'companyId' => $company->id,
    ]);

    $applicant = User::factory()->create(['role' => 'job-seeker']);

    $resume = Resume::create([
        'filename' => 'cv.pdf',
        'fileUri' => 'resumes/cv.pdf',
        'contactDetails' => ['name' => $applicant->name, 'email' => $applicant->email],
        'education' => 'Master',
        'summary' => 'Developer',
        'skills' => 'PHP',
        'experience' => '2 years',
        'userId' => $applicant->id,
    ]);

    JobApplication::create([
        'status' => 'pending',
        'jobVacancyId' => $vacancy->id,
        'userId' => $applicant->id,
        'resumeId' => $resume->id,
    ]);

    $vacancy->delete();

    $response = $this->actingAs($admin)->get(route('job-application.index'));

    $response->assertOk();
    $response->assertSee('Archived Developer');
    $response->assertSee('archivée', false);
});
