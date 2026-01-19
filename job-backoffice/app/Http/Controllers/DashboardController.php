<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\JobVacancy;
use App\Models\JobApplication;


class DashboardController extends Controller
{
    function index() 
    {

        if(auth()->user()->role == 'admin'){
            $analytics = $this->adminDashbord();
        }else{
          $analytics = $this->companyOwnerDashbord();
        }

        return view('dashboard.index',compact(['analytics']));
    }
    private function adminDashbord(){
      // Last 30 days active users (job-seekers role)
        $activeUsers = User::where('last_login_at', '>=', now()->subDays(30))
        ->where('role', 'job-seeker')->count();

        // Total job vacancies (not deleted)
        $totalJobs = JobVacancy::whereNull('deleted_at')->count();
#
        // Total job applications (not deleted)
        $totaleApplications = JobApplication::whereNull('deleted_at')->count();

     

        // Most applied jobs
        $mostAppliedJobs = JobVacancy::withCount('jobApplications as totalCount')
        ->whereNull('deleted_at')
        ->orderbydesc('totalCount')
        ->limit(5)
        ->get();

        //conversion Rates 
        $conversionRates = JobVacancy::withCount('jobApplications as totalCount')
        ->having('totalCount', '>', 0)
        ->orderbydesc('totalCount')
        ->limit(5)
        ->get()
         ->map(function ($job) {
            if ($job->viewCount == 0) {
                $job->conversionRate = 0;
                return $job;
            }
            $job->conversionRate = round(($job->totalCount / $job->viewCount) * 100,2);
            return $job;
        });
           $analytics = [
            'activeUsers' => $activeUsers,
            'totalJobs' => $totalJobs,
            'totalApplications' => $totaleApplications,
            'mostAppliedJobs'=>$mostAppliedJobs,
            'conversionRates'=>$conversionRates
            
        ];

        return $analytics;
        
    }
    private function companyOwnerDashbord(){
        $company = auth()->user()->company;

        // filter active users by applying to jobs of the company 

        $activeUsers = User::where('last_login_at', '>=', now()->subDays(30))
        ->where('role', 'job-seeker')
        ->whereHas('jobApplications', function ($query) use ($company) {
            $query->whereIn('jobVacancyId', $company->jobVacancies->pluck('id'));
        })
        ->count();
         
        //total jobs of the company
        $totalJobs = $company->jobVacancies->count();

        //total applications of the company
        $totalApplications = JobApplication::whereIn('jobVacancyId', $company->jobVacancies->pluck('id'))->count();

        //most applied jobs of the company
        $mostAppliedJobs = JobVacancy::withCount('jobApplications as totalCount')
        ->whereIn('id', $company->jobVacancies->pluck('id'))
        ->limit(5)
        ->orderbydesc('totalCount')
        ->get();
        
        // Conversion Rates
        $conversionRates = JobVacancy::withCount('jobApplications as totalCount')
        ->whereIn('id', $company->jobVacancies->pluck('id'))
        ->having('totalCount', '>', 0)
        ->limit(5)
        ->orderbydesc('totalCount')
        ->get()
         ->map(function ($job) {
            if ($job->viewCount == 0) {
                $job->conversionRate = 0;
                return $job;
            }
            $job->conversionRate = round(($job->totalCount / $job->viewCount) * 100,2);
            return $job;
        });
       $analytics = [
            'activeUsers' => $activeUsers,
            'totalJobs' => $totalJobs,
            'totalApplications' => $totalApplications,
            'mostAppliedJobs'=>$mostAppliedJobs,
            'conversionRates'=>$conversionRates
            
        ];
        return $analytics;


    }
}
