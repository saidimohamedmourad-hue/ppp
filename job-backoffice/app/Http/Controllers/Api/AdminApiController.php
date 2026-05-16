<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\School;
use App\Models\TrainingApplication;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminApiController extends Controller
{
    public function analytics(): JsonResponse
    {
        return response()->json([
            'totalUsers'        => User::where('role', 'job-seeker')->whereNull('deleted_at')->count(),
            'totalCompanies'    => Company::whereNull('deleted_at')->count(),
            'totalSchools'      => School::whereNull('deleted_at')->count(),
            'totalJobs'         => JobVacancy::whereNull('deleted_at')->count(),
            'totalTrainings'    => TrainingSession::whereNull('deleted_at')->count(),
            'totalJobApps'      => JobApplication::whereNull('deleted_at')->count(),
            'totalTrainingApps' => TrainingApplication::whereNull('deleted_at')->count(),
            'activeUsers'       => User::where('role', 'job-seeker')
                ->where('last_login_at', '>=', now()->subDays(30))
                ->whereNull('deleted_at')
                ->count(),
        ]);
    }

    public function users(Request $request): JsonResponse
    {
        $query = User::whereNull('deleted_at');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function companies(Request $request): JsonResponse
    {
        $query = Company::with('owner')->whereNull('deleted_at');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->latest()->paginate(10));
    }

    public function schools(Request $request): JsonResponse
    {
        $query = School::with('owner')->whereNull('deleted_at');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->latest()->paginate(10));
    }

    public function deleteJob(string $id): JsonResponse
    {
        $job = JobVacancy::whereNull('deleted_at')->findOrFail($id);
        $job->delete();

        return response()->json(['message' => 'Offre supprimée par l\'administrateur.']);
    }

    public function deleteTraining(string $id): JsonResponse
    {
        $session = TrainingSession::whereNull('deleted_at')->findOrFail($id);
        $session->delete();

        return response()->json(['message' => 'Formation supprimée par l\'administrateur.']);
    }

    public function deleteUser(string $id): JsonResponse
    {
        $user = User::whereNull('deleted_at')->findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé.']);
    }
}
