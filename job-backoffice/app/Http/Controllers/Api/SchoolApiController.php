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

        $mostApplied = TrainingSession::withCount('trainingApplications as totalCount')
            ->whereIn('id', $sessionIds)
            ->orderByDesc('totalCount')
            ->limit(5)
            ->get();

        return response()->json([
            'school'              => $school,
            'totalSessions'       => $totalSessions,
            'totalApplications'   => $totalApplications,
            'pendingApplications' => $pendingApplications,
            'mostAppliedSessions' => $mostApplied,
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
