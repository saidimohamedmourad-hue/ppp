<?php

namespace Database\Seeders;

use App\Models\User;
use Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JobCategory;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\Resume;
use App\Models\jobapplication;
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // seed the root admnin 

        User::firstOrCreate([
             "email"=> "admin@admin.com"],
             [   
            "name"=> "admin",
           
            "password"=> Hash::make('12345678'),
            'role'  => 'admin',
            'email_verified_at' => now(),
        ]);

        // seed data to test with
        $jobData = json_decode(file_get_contents(database_path('data/job_data.json')), true);
        $jobApplicants = json_decode(file_get_contents(database_path('data/job_applications.json')), true);
   // create job categories 
        foreach ($jobData['jobCategories'] as $category) {
             JobCategory::firstOrCreate([
                'name' => $category,
            ]);
        }

            // Create companies 
            foreach ($jobData['companies'] as $company) {
                // create owners for companies
                $companyOwner = User::firstOrCreate([
                    'email' => fake()->unique()->safeEmail(),
                ], [
                    'name' => fake()->name(),
                    'password' => Hash::make('12345678'),
                    'role' => 'company-owner',
                    'email_verified_at' => now(),
                ]);
                 Company::firstOrCreate([
                    'name' => $company['name'],
                ],[
                    'address' => $company['address'],
                    'industry' => $company['industry'],
                    'website' => $company['website'],
                    'ownerId' => $companyOwner->id
                ]);
            }

        
            //create job_vacancies
            foreach ($jobData['jobVacancies'] as $job) {
                    // get the create company 
                $company = Company::where('name', $job['company'])->firstorfail();
                     // get the created job category

                $category = JobCategory::where('name', $job['category'])->firstOrFail();

           
                

                
                    JobVacancy::firstOrCreate([
                        'title' => $job['title'],
                         'companyId' => $company->id,
                    ], [
                        'description' => $job['description'],
                        'location' => $job['location'],
                        'salary' => $job['salary'],
                        'type' => $job['type'],
                        'jobCategoryId' => $category->id,
                       
                    ]);
                
            }
        
    
    // create job applicants
    foreach ($jobApplicants['jobApplications'] as $application) {
        // get random job vacancy
        $jobVacancy = JobVacancy::inRandomOrder()->first();
        // create user for applicant (job seeker)
        $applicant = User::firstOrCreate([
            'email' => fake()->unique()->safeEmail(),
        ],[
            'name'=> fake()->name(),
            'role'=> 'job-seeker',
            'password'=> Hash::make('12345678'),
            'email_verified_at' => now(),
        ]);
         
        // create resume 
       $resume= Resume::Create([
            'userId' => $applicant->id,
            
            'filename' => $application['resume']['filename'],
            'fileUri' => $application['resume']['fileUri'],
            'contactDetails' => $application['resume']['contactDetails'],
            'education' => $application['resume']['education'],
            'summary' => $application['resume']['summary'],
            'skills' => $application['resume']['skills'],
            'experience' => $application['resume']['experience'],
        ]);
        // create job application
        jobapplication::Create([
            'userId' => $applicant->id,
            'jobVacancyId' => $jobVacancy->id,
            'resumeId' => $resume->id,
             'status' => $application['status'],
             'aiGeneratedScore' => $application['aiGeneratedScore'],
             'aiGeneratedFeedback' => $application['aiGeneratedFeedback'],
        ]);

        
       
          }    
   }
}
