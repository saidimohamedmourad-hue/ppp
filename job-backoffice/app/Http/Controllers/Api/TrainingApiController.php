<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Resume;
use App\Models\TrainingApplication;
use App\Models\TrainingSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingApiController extends Controller
{
    // ─── Public ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $query = TrainingSession::with(['school', 'trainingCategory'])
            ->whereNull('deleted_at')
            ->where('status', 'open');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('school', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('trainingCategory', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('category')) {
            $query->where('trainingCategoryId', $request->category);
        }

        if ($request->filled('format')) {
            $query->where('format', $request->format);
        }

        $sessions = $query->latest()->paginate(10);

        return response()->json($sessions);
    }

    public function show(string $id): JsonResponse
    {
        $session = TrainingSession::with(['school', 'trainingCategory'])
            ->whereNull('deleted_at')
            ->where('status', 'open')
            ->findOrFail($id);

        $session->increment('viewCount');

        return response()->json($session);
    }

    // ─── Job-Seeker ───────────────────────────────────────────────────────────

    public function apply(Request $request, string $id): JsonResponse
    {
        $session = TrainingSession::whereNull('deleted_at')->where('status', 'open')->findOrFail($id);
        $user = $request->user();

        if (TrainingApplication::where('userId', $user->id)->where('trainingSessionId', $id)->exists()) {
            return response()->json(['message' => 'Vous êtes déjà inscrit à cette formation.'], 409);
        }

        if ($session->maxParticipants && $session->currentParticipants >= $session->maxParticipants) {
            return response()->json(['message' => 'La formation est complète.'], 409);
        }

        $data = $request->validate([
            'resume_id'   => 'required_without:resume_file|nullable|uuid|exists:resumes,id',
            'resume_file' => 'required_without:resume_id|nullable|file|mimes:pdf|max:2048',
        ]);

        $resumeId = $data['resume_id'] ?? null;

        if ($request->hasFile('resume_file')) {
            $file = $request->file('resume_file');
            $path = $file->store('resumes', 'public');

            $resume = Resume::create([
                'filename'       => $file->getClientOriginalName(),
                'fileUri'        => $path,
                'userId'         => $user->id,
                'contactDetails' => ['name' => $user->name, 'email' => $user->email],
                'summary'        => '',
                'skills'         => '',
                'experience'     => '',
                'education'      => '',
            ]);
            $resumeId = $resume->id;
        }

        if ($resumeId && ! Resume::where('id', $resumeId)->where('userId', $user->id)->exists()) {
            return response()->json(['message' => 'CV invalide.'], 403);
        }

        $application = TrainingApplication::create([
            'trainingSessionId'  => $id,
            'userId'             => $user->id,
            'resumeId'           => $resumeId,
            'status'             => 'pending',
            'aiGeneratedScore'   => 0,
            'aiGeneratedFeedback' => '',
        ]);

        $session->increment('currentParticipants');

        return response()->json($application->load(['trainingSession.school', 'resume']), 201);
    }

    public function myApplications(Request $request): JsonResponse
    {
        $applications = TrainingApplication::with(['trainingSession.school', 'trainingSession.trainingCategory', 'resume'])
            ->where('userId', $request->user()->id)
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(10);

        return response()->json($applications);
    }

    // ─── School-Owner ─────────────────────────────────────────────────────────

    public function schoolSessions(Request $request): JsonResponse
    {
        $school = $request->user()->school;
        if (! $school) {
            return response()->json(['message' => 'Aucune école associée.'], 404);
        }

        $sessions = TrainingSession::with(['trainingCategory'])
            ->where('schoolId', $school->id)
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(10);

        return response()->json($sessions);
    }

    public function storeSession(Request $request): JsonResponse
    {
        $school = $request->user()->school;
        if (! $school) {
            return response()->json(['message' => 'Aucune école associée.'], 404);
        }

        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'required|string|max:5000',
            'location'            => 'required|string|max:255',
            'trainingDate'        => 'required|date',
            'endDate'             => 'nullable|date|after:trainingDate',
            'startTime'           => 'nullable|string',
            'endTime'             => 'nullable|string',
            'maxParticipants'     => 'required|integer|min:1',
            'status'              => 'required|in:draft,open,closed,cancelled',
            'salary'              => 'nullable|numeric|min:0',
            'trainingCategoryId'  => 'required|uuid|exists:training_categories,id',
        ]);

        $session = TrainingSession::create(array_merge($data, [
            'schoolId'           => $school->id,
            'currentParticipants' => 0,
            'viewCount'          => 0,
        ]));

        return response()->json($session->load(['school', 'trainingCategory']), 201);
    }

    public function updateSession(Request $request, string $id): JsonResponse
    {
        $school = $request->user()->school;
        $session = TrainingSession::where('schoolId', $school?->id)->whereNull('deleted_at')->findOrFail($id);

        $data = $request->validate([
            'title'              => 'sometimes|string|max:255',
            'description'        => 'sometimes|string|max:5000',
            'location'           => 'sometimes|string|max:255',
            'trainingDate'       => 'sometimes|date',
            'endDate'            => 'nullable|date',
            'startTime'          => 'nullable|string',
            'endTime'            => 'nullable|string',
            'maxParticipants'    => 'sometimes|integer|min:1',
            'status'             => 'sometimes|in:draft,open,closed,cancelled',
            'salary'             => 'nullable|numeric|min:0',
            'trainingCategoryId' => 'sometimes|uuid|exists:training_categories,id',
        ]);

        $session->update($data);

        return response()->json($session->load(['school', 'trainingCategory']));
    }

    public function destroySession(Request $request, string $id): JsonResponse
    {
        $school = $request->user()->school;
        $session = TrainingSession::where('schoolId', $school?->id)->whereNull('deleted_at')->findOrFail($id);
        $session->delete();

        return response()->json(['message' => 'Formation supprimée.']);
    }

    public function sessionApplicants(Request $request, string $id): JsonResponse
    {
        $school = $request->user()->school;
        $session = TrainingSession::where('schoolId', $school?->id)->whereNull('deleted_at')->findOrFail($id);

        $applications = TrainingApplication::with(['user', 'resume'])
            ->where('trainingSessionId', $session->id)
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(10);

        return response()->json($applications);
    }

    public function updateApplicationStatus(Request $request, string $applicationId): JsonResponse
    {
        $school = $request->user()->school;

        $application = TrainingApplication::whereHas(
            'trainingSession',
            fn ($q) => $q->where('schoolId', $school?->id)
        )->findOrFail($applicationId);

        $data = $request->validate([
            'status' => 'required|in:pending,reviewed,accepted,rejected',
        ]);

        $application->update($data);

        return response()->json($application->load(['user', 'resume', 'trainingSession']));
    }
}
