<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyApiController extends Controller
{
    public function index(): JsonResponse
    {
        $companies = Company::whereNull('deleted_at')->latest()->paginate(10);

        return response()->json($companies);
    }

    public function show(string $id): JsonResponse
    {
        $company = Company::with(['owner'])->whereNull('deleted_at')->findOrFail($id);

        return response()->json($company);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        if (! $company) {
            return response()->json(['message' => 'Aucune entreprise associée.'], 404);
        }

        $jobIds = $company->jobVacancies()->whereNull('deleted_at')->pluck('id');

        $totalJobs         = $jobIds->count();
        $totalApplications = JobApplication::whereIn('jobVacancyId', $jobIds)->whereNull('deleted_at')->count();

        $pendingApplications = JobApplication::whereIn('jobVacancyId', $jobIds)
            ->where('status', 'pending')
            ->whereNull('deleted_at')
            ->count();

        $acceptedApplications = JobApplication::whereIn('jobVacancyId', $jobIds)
            ->where('status', 'accepted')
            ->whereNull('deleted_at')
            ->count();

        // Candidats actifs (job-seekers connectés sur les 30 derniers jours
        // ayant postulé à au moins une offre de cette entreprise).
        $activeUsers = \App\Models\User::where('role', 'job-seeker')
            ->where('last_login_at', '>=', now()->subDays(30))
            ->whereHas('jobApplications', fn ($q) => $q->whereIn('jobVacancyId', $jobIds)->whereNull('deleted_at'))
            ->count();

        // Total des vues cumulées des offres de l'entreprise.
        $totalViews = (int) JobVacancy::whereIn('id', $jobIds)->sum('viewCount');

        // Détail par offre : vues (colonne viewCount), candidatures (totalCount)
        // et acceptées (acceptedCount) -> le front affiche le tableau complet
        // et calcule le taux de conversion (candidatures / vues).
        $mostApplied = JobVacancy::withCount([
                'jobApplications as totalCount',
                'jobApplications as acceptedCount' => fn ($q) => $q->where('status', 'accepted'),
            ])
            ->whereIn('id', $jobIds)
            ->orderByDesc('totalCount')
            ->limit(50)
            ->get();

        // Candidatures récentes (pour le tableau "Candidatures récentes").
        $recentApplicants = JobApplication::whereIn('jobVacancyId', $jobIds)
            ->whereNull('deleted_at')
            ->with(['user:id,name,email', 'jobVacancy:id,title'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($a) => [
                'id'         => $a->id,
                'status'     => $a->status,
                'created_at' => $a->created_at,
                'user'       => ['id' => $a->user?->id, 'name' => $a->user?->name, 'email' => $a->user?->email],
                'job'        => ['id' => $a->jobVacancy?->id, 'title' => $a->jobVacancy?->title],
            ]);

        return response()->json([
            'company'              => $company,
            'totalJobs'            => $totalJobs,
            'totalApplications'    => $totalApplications,
            'pendingApplications'  => $pendingApplications,
            'acceptedApplications' => $acceptedApplications,
            'activeUsers'          => $activeUsers,
            'totalViews'           => $totalViews,
            'mostAppliedJobs'      => $mostApplied,
            'recentApplicants'     => $recentApplicants,
        ]);
    }

    public function myCompany(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        if (! $company) {
            return response()->json(['message' => 'Aucune entreprise associée.'], 404);
        }

        return response()->json($company->load('owner'));
    }

    public function updateMyCompany(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        if (! $company) {
            return response()->json(['message' => 'Aucune entreprise associée.'], 404);
        }

        $data = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'address'  => 'sometimes|string|max:500',
            'industry' => 'sometimes|string|max:255',
            'website'  => 'nullable|url|max:255',
        ]);

        $company->update($data);

        return response()->json($company->fresh());
    }
}
