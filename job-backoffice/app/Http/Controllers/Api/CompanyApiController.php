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

        $mostApplied = JobVacancy::withCount('jobApplications as totalCount')
            ->whereIn('id', $jobIds)
            ->orderByDesc('totalCount')
            ->limit(5)
            ->get();

        return response()->json([
            'company'             => $company,
            'totalJobs'           => $totalJobs,
            'totalApplications'   => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'mostAppliedJobs'     => $mostApplied,
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
