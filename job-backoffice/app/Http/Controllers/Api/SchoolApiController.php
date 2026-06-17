<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\TrainingApplication;
use App\Models\TrainingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolApiController extends Controller
{
    public function index(): JsonResponse
    {
        $schools = School::whereNull('deleted_at')->latest()->paginate(10);

        return response()->json($schools);
    }

    public function show(string $id): JsonResponse
    {
        $school = School::with('owner')->whereNull('deleted_at')->findOrFail($id);

        return response()->json($school);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $school = $request->user()->school;
        if (! $school) {
            return response()->json(['message' => 'Aucune école associée.'], 404);
        }

        $sessionIds = $school->trainingSessions()->whereNull('deleted_at')->pluck('id');

        $totalSessions     = $sessionIds->count();
        $totalApplications = TrainingApplication::whereIn('trainingSessionId', $sessionIds)
            ->whereNull('deleted_at')->count();

        $pendingApplications = TrainingApplication::whereIn('trainingSessionId', $sessionIds)
            ->where('status', 'pending')
            ->whereNull('deleted_at')
            ->count();

        $acceptedApplications = TrainingApplication::whereIn('trainingSessionId', $sessionIds)
            ->where('status', 'accepted')
            ->whereNull('deleted_at')
            ->count();

        // Candidats actifs (job-seekers connectés sur 30 jours ayant postulé
        // à au moins une session de cette école).
        $activeUsers = \App\Models\User::where('role', 'job-seeker')
            ->where('last_login_at', '>=', now()->subDays(30))
            ->whereHas('trainingApplications', fn ($q) => $q->whereIn('trainingSessionId', $sessionIds)->whereNull('deleted_at'))
            ->count();

        $totalViews = (int) TrainingSession::whereIn('id', $sessionIds)->sum('viewCount');

        // Détail par formation : vues (viewCount), inscriptions (totalCount) et
        // acceptées (acceptedCount) -> tableau complet + taux de conversion.
        $mostApplied = TrainingSession::withCount([
                'trainingApplications as totalCount',
                'trainingApplications as acceptedCount' => fn ($q) => $q->where('status', 'accepted'),
            ])
            ->whereIn('id', $sessionIds)
            ->orderByDesc('totalCount')
            ->limit(50)
            ->get();

        $recentApplicants = TrainingApplication::whereIn('trainingSessionId', $sessionIds)
            ->whereNull('deleted_at')
            ->with(['user:id,name,email', 'trainingSession:id,title'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($a) => [
                'id'         => $a->id,
                'status'     => $a->status,
                'created_at' => $a->created_at,
                'user'       => ['id' => $a->user?->id, 'name' => $a->user?->name, 'email' => $a->user?->email],
                'session'    => ['id' => $a->trainingSession?->id, 'title' => $a->trainingSession?->title],
            ]);

        return response()->json([
            'school'               => $school,
            'totalSessions'        => $totalSessions,
            'totalApplications'    => $totalApplications,
            'pendingApplications'  => $pendingApplications,
            'acceptedApplications' => $acceptedApplications,
            'activeUsers'          => $activeUsers,
            'totalViews'           => $totalViews,
            'mostAppliedSessions'  => $mostApplied,
            'recentApplicants'     => $recentApplicants,
        ]);
    }

    public function mySchool(Request $request): JsonResponse
    {
        $school = $request->user()->school;
        if (! $school) {
            return response()->json(['message' => 'Aucune école associée.'], 404);
        }

        return response()->json($school->load('owner'));
    }

    public function updateMySchool(Request $request): JsonResponse
    {
        $school = $request->user()->school;
        if (! $school) {
            return response()->json(['message' => 'Aucune école associée.'], 404);
        }

        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'address'     => 'sometimes|string|max:500',
            'industry'    => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:2000',
            'website'     => 'nullable|url|max:255',
        ]);

        $school->update($data);

        return response()->json($school->fresh());
    }
}
