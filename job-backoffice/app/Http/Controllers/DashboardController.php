<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\School;
use App\Models\TrainingSession;
use App\Models\TrainingApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function index()
    {
        $role = auth()->user()->role;

        if ($role === 'school-owner') {
            $school = auth()->user()->school;
            $jobAnalytics = null;
            $showJobTab = false;
            $trainingAnalytics = $school
                ? $this->schoolOwnerTrainingDashboard($school)
                : $this->emptyTrainingAnalytics();
            $hasSchool = (bool) $school;

            return view('dashboard.index', compact('jobAnalytics', 'trainingAnalytics', 'showJobTab', 'hasSchool'));
        }

        $jobAnalytics = $role === 'admin'
            ? $this->adminDashbord()
            : $this->companyOwnerDashbord();

        $trainingAnalytics = $role === 'admin'
            ? $this->adminTrainingDashboard()
            : $this->companyOwnerTrainingDashboard();

        $showJobTab = true;
        $hasSchool = true;

        return view('dashboard.index', compact('jobAnalytics', 'trainingAnalytics', 'showJobTab', 'hasSchool'));
    }

    private function emptyTrainingAnalytics(): array
    {
        return [
            'activeApplicants' => 0,
            'totalSessions' => 0,
            'totalApplications' => 0,
            'totalSchools' => null,
            'mostAppliedSessions' => collect(),
            'conversionRates' => collect(),
            'includeSchoolColumn' => false,
        ];
    }

    private function adminTrainingDashboard(): array
    {
        return $this->buildTrainingAnalytics(
            sessionQuery: TrainingSession::query(),
            includeSchoolColumn: true,
            includeTotalSchools: true,
        );
    }

    private function companyOwnerTrainingDashboard(): array
    {
        return $this->buildTrainingAnalytics(
            sessionQuery: TrainingSession::query(),
            includeSchoolColumn: true,
            includeTotalSchools: false,
        );
    }

    private function schoolOwnerTrainingDashboard(School $school): array
    {
        return $this->buildTrainingAnalytics(
            sessionQuery: TrainingSession::query()->where('schoolId', $school->id),
            includeSchoolColumn: false,
            includeTotalSchools: false,
        );
    }

    /**
     * @param  Builder<TrainingSession>  $sessionQuery
     */
    private function buildTrainingAnalytics(Builder $sessionQuery, bool $includeSchoolColumn, bool $includeTotalSchools): array
    {
        $activeApplicants = User::query()
            ->where('last_login_at', '>=', now()->subDays(30))
            ->where('role', 'job-seeker')
            ->whereHas('trainingApplications', function (Builder $q) use ($sessionQuery) {
                $q->whereNull('deleted_at')
                    ->whereHas('trainingSession', fn (Builder $sq) => $this->applySessionScope($sq, $sessionQuery));
            })
            ->count();

        $totalSessions = (clone $sessionQuery)->whereNull('deleted_at')->count();

        $totalApplications = TrainingApplication::query()
            ->whereNull('deleted_at')
            ->whereHas('trainingSession', fn (Builder $sq) => $this->applySessionScope($sq, $sessionQuery))
            ->count();

        $totalSchools = $includeTotalSchools ? School::whereNull('deleted_at')->count() : null;

        $mostAppliedSessions = (clone $sessionQuery)
            ->whereNull('deleted_at')
            ->with(['school'])
            ->withCount('trainingApplications as totalCount')
            ->orderByDesc('totalCount')
            ->limit(5)
            ->get();

        $conversionRates = (clone $sessionQuery)
            ->whereNull('deleted_at')
            ->withCount('trainingApplications as totalCount')
            ->having('totalCount', '>', 0)
            ->orderByDesc('totalCount')
            ->limit(5)
            ->get()
            ->map(function (TrainingSession $session) {
                if ($session->viewCount == 0) {
                    $session->conversionRate = 0;
                } else {
                    $session->conversionRate = round(($session->totalCount / $session->viewCount) * 100, 2);
                }

                return $session;
            });

        return [
            'activeApplicants' => $activeApplicants,
            'totalSessions' => $totalSessions,
            'totalApplications' => $totalApplications,
            'totalSchools' => $totalSchools,
            'mostAppliedSessions' => $mostAppliedSessions,
            'conversionRates' => $conversionRates,
            'includeSchoolColumn' => $includeSchoolColumn,
        ];
    }

    /**
     * Restrict training sessions to the same scope as $sessionQuery (e.g. one school).
     *
     * @param  Builder<TrainingSession>  $sessionQuery  defines allowed session IDs / constraints
     */
    private function applySessionScope(Builder $trainingSessionRelation, Builder $sessionQuery): void
    {
        $trainingSessionRelation->whereNull('deleted_at')
            ->whereIn('id', (clone $sessionQuery)->select('id'));
    }

    private function adminDashbord()
    {
        // Last 30 days active users (job-seekers role)
        $activeUsers = User::where('last_login_at', '>=', now()->subDays(30))
            ->where('role', 'job-seeker')->count();

        // Total job vacancies (not deleted)
        $totalJobs = JobVacancy::whereNull('deleted_at')->count();
        //
        // Total job applications (not deleted)
        $totaleApplications = JobApplication::whereNull('deleted_at')->count();

        // Most applied jobs
        $mostAppliedJobs = JobVacancy::withCount('jobApplications as totalCount')
            ->whereNull('deleted_at')
            ->orderbydesc('totalCount')
            ->limit(5)
            ->get();

        // conversion Rates
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
                $job->conversionRate = round(($job->totalCount / $job->viewCount) * 100, 2);

                return $job;
            });
        $analytics = [
            'activeUsers' => $activeUsers,
            'totalJobs' => $totalJobs,
            'totalApplications' => $totaleApplications,
            'mostAppliedJobs' => $mostAppliedJobs,
            'conversionRates' => $conversionRates,

        ];

        return $analytics;

    }

    private function companyOwnerDashbord()
    {
        $company = auth()->user()->company;

        // filter active users by applying to jobs of the company

        $activeUsers = User::where('last_login_at', '>=', now()->subDays(30))
            ->where('role', 'job-seeker')
            ->whereHas('jobApplications', function ($query) use ($company) {
                $query->whereIn('jobVacancyId', $company->jobVacancies->pluck('id'));
            })
            ->count();

        // total jobs of the company
        $totalJobs = $company->jobVacancies->count();

        // total applications of the company
        $totalApplications = JobApplication::whereIn('jobVacancyId', $company->jobVacancies->pluck('id'))->count();

        // most applied jobs of the company
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
                $job->conversionRate = round(($job->totalCount / $job->viewCount) * 100, 2);

                return $job;
            });
        $analytics = [
            'activeUsers' => $activeUsers,
            'totalJobs' => $totalJobs,
            'totalApplications' => $totalApplications,
            'mostAppliedJobs' => $mostAppliedJobs,
            'conversionRates' => $conversionRates,

        ];

        return $analytics;

    }
}
