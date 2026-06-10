<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeTrainingApplicationJob;
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
        // Show open + cancelled sessions; hide closed
        $query = TrainingSession::with(['school', 'trainingCategory'])
            ->whereNull('deleted_at')
            ->whereIn('status', ['open', 'cancelled']);

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

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        $sessions = $query->latest()->paginate(10);

        return response()->json($sessions);
    }

    public function show(string $id): JsonResponse
    {
        // Allow viewing open + cancelled (cancelled shows the reason banner)
        $session = TrainingSession::with(['school', 'trainingCategory'])
            ->whereNull('deleted_at')
            ->whereIn('status', ['open', 'cancelled'])
            ->findOrFail($id);

        $session->increment('viewCount');

        return response()->json($session);
    }

    // ─── Job-Seeker ───────────────────────────────────────────────────────────

    public function apply(Request $request, string $id): JsonResponse
    {
        // Only open sessions accept applications (cancelled/closed do not)
        $session = TrainingSession::whereNull('deleted_at')->where('status', 'open')->findOrFail($id);
        $user = $request->user();

        if (TrainingApplication::where('userId', $user->id)->where('trainingSessionId', $id)->exists()) {
            return response()->json(['message' => 'Vous êtes déjà inscrit à cette formation.'], 409);
        }

        // If full, application goes on the waiting list
        $isWaitlist = $session->maxParticipants > 0
            && $session->currentParticipants >= $session->maxParticipants;

        $data = $request->validate([
            // CV OPTIONNEL pour une inscription à une formation (contrairement
            // à une candidature emploi où il reste obligatoire).
            'resume_id'    => 'nullable|uuid|exists:resumes,id',
            'resume_file'  => 'nullable|file|mimes:pdf|max:2048',
            'cover_letter' => 'nullable|string|max:5000',
            // Niveau d'études (Algérie) : obligatoire, parmi la liste fermée.
            'education_level' => ['required', 'string', \Illuminate\Validation\Rule::in(config('education.levels'))],
            'phone'        => $user->phone ? 'nullable|string|min:6|max:32|regex:/^[0-9+\-\s()]+$/'
                                           : 'required|string|min:6|max:32|regex:/^[0-9+\-\s()]+$/',
        ], [
            'phone.required' => 'Indiquez un numéro de téléphone — l\'école s\'en servira pour vous contacter.',
            'phone.regex'    => 'Le numéro de téléphone contient des caractères invalides.',
            'education_level.required' => 'Sélectionnez votre niveau d\'études.',
            'education_level.in'       => 'Niveau d\'études invalide.',
        ]);

        if (! empty($data['phone']) && $data['phone'] !== $user->phone) {
            $user->update(['phone' => $data['phone']]);
        }

        $resumeId = $data['resume_id'] ?? null;

        if ($request->hasFile('resume_file')) {
            $file = $request->file('resume_file');
            $path = $file->store('resumes', 'public');

            $resume = Resume::create([
                'filename' => $file->getClientOriginalName(),
                'fileUri' => $path,
                'userId' => $user->id,
                'contactDetails' => ['name' => $user->name, 'email' => $user->email],
                'summary' => '',
                'skills' => '',
                'experience' => '',
                'education' => '',
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
            'cover_letter'       => $data['cover_letter'] ?? null,
            'education_level'    => $data['education_level'],
            'status'             => 'pending',
            'is_waitlist'        => $isWaitlist,
            'aiGeneratedScore'   => 0,
            'aiGeneratedFeedback' => '',
        ]);

        // Only count toward capacity if not on the waitlist
        if (! $isWaitlist) {
            $session->increment('currentParticipants');
        }

        // Analyse IA en arrière-plan (queue)
        AnalyzeTrainingApplicationJob::dispatch($application->id);

        // Notify the school-owner (bell + email) — best-effort, never blocks
        // the enrollment if mail transport fails.
        $schoolOwner = $session->school?->owner;
        if ($schoolOwner) {
            try {
                $schoolOwner->notify(new \App\Notifications\TrainingApplicationReceived($application));
            } catch (\Throwable $e) {
                report($e);
            }
        }

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

    public function withdrawApplication(Request $request, string $id): JsonResponse
    {
        $application = TrainingApplication::where('userId', $request->user()->id)
            ->whereNull('deleted_at')
            ->findOrFail($id);

        // Only decrement capacity if this app was actually counted (not on waitlist)
        if (! $application->is_waitlist) {
            $application->trainingSession()->decrement('currentParticipants');
        }
        $application->delete();

        return response()->json(['message' => 'Inscription annulée.']);
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
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'location' => 'required|string|max:255',
            'trainingDate' => 'required|date',
            'endDate' => 'nullable|date|after:trainingDate',
            'startTime' => 'nullable|string',
            'endTime' => 'nullable|string',
            'maxParticipants' => 'required|integer|min:1',
            'status' => 'required|in:open,closed,cancelled',
            'type' => 'required|in:en_ligne,accelerer,presentiel,longue_duree',
            'cancellation_reason' => 'nullable|required_if:status,cancelled|string|max:1000',
            'salary' => 'nullable|numeric|min:0',
            'trainingCategoryId' => 'required|uuid|exists:training_categories,id',
            // Niveau d'études minimum requis pour la formation (obligatoire).
            'min_education_level' => ['required', 'string', \Illuminate\Validation\Rule::in(config('education.levels'))],
        ], [
            'min_education_level.required' => 'Sélectionnez le niveau d\'études minimum requis.',
            'min_education_level.in'       => 'Niveau d\'études minimum invalide.',
        ]);

        $session = TrainingSession::create(array_merge($data, [
            'schoolId' => $school->id,
            'currentParticipants' => 0,
            'viewCount' => 0,
        ]));

        return response()->json($session->load(['school', 'trainingCategory']), 201);
    }

    public function updateSession(Request $request, string $id): JsonResponse
    {
        $school = $request->user()->school;
        $session = TrainingSession::where('schoolId', $school?->id)->whereNull('deleted_at')->findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:5000',
            'location' => 'sometimes|string|max:255',
            'trainingDate' => 'sometimes|date',
            'endDate' => 'nullable|date',
            'startTime' => 'nullable|string',
            'endTime' => 'nullable|string',
            'maxParticipants' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:open,closed,cancelled',
            'type' => 'sometimes|in:en_ligne,accelerer,presentiel,longue_duree',
            'cancellation_reason' => 'nullable|required_if:status,cancelled|string|max:1000',
            'salary' => 'nullable|numeric|min:0',
            'trainingCategoryId' => 'sometimes|uuid|exists:training_categories,id',
            'min_education_level' => ['sometimes', 'required', 'string', \Illuminate\Validation\Rule::in(config('education.levels'))],
        ], [
            'min_education_level.required' => 'Sélectionnez le niveau d\'études minimum requis.',
            'min_education_level.in'       => 'Niveau d\'études minimum invalide.',
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

        $newStatus = $data['status'];
        $previousStatus = $application->status;
        $wasWaitlist = $application->is_waitlist;

        // Accepting a waitlist app: count it now (may overfill maxParticipants)
        if ($newStatus === 'accepted' && $wasWaitlist) {
            $application->trainingSession()->increment('currentParticipants');
            $application->is_waitlist = false;
        }

        $application->status = $newStatus;
        $application->save();

        // Notify candidate when status really changed (best-effort).
        if ($previousStatus !== $newStatus && $application->user) {
            try {
                $application->user->notify(new \App\Notifications\TrainingApplicationStatusChanged($application->fresh()));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Lean payload — see JobApiController::updateApplicationStatus for the
        // rationale (strict JSON encoder + possibly dirty text columns).
        return response()->json([
            'id'          => $application->id,
            'status'      => $application->status,
            'is_waitlist' => (bool) $application->is_waitlist,
        ]);
    }
}
