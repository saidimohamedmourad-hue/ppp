<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeJobApplicationJob;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\Resume;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobApiController extends Controller
{
    // ─── Public ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $query = JobVacancy::with(['company', 'jobCategory'])->whereNull('deleted_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('company', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('jobCategoryId', $request->category);
        }

        if ($request->filled('min_salary')) {
            $query->where('salary', '>=', $request->min_salary);
        }

        if ($request->filled('max_salary')) {
            $query->where('salary', '<=', $request->max_salary);
        }

        $jobs = $query->latest()->paginate(10);

        return response()->json($jobs);
    }

    public function show(string $id): JsonResponse
    {
        $job = JobVacancy::with(['company', 'jobCategory'])->whereNull('deleted_at')->findOrFail($id);
        $job->increment('viewCount');

        return response()->json($job);
    }

    // ─── Job-Seeker ───────────────────────────────────────────────────────────

    public function apply(Request $request, string $id): JsonResponse
    {
        $job = JobVacancy::whereNull('deleted_at')->findOrFail($id);
        $user = $request->user();

        if (JobApplication::where('userId', $user->id)->where('jobVacancyId', $id)->exists()) {
            return response()->json(['message' => 'Vous avez déjà postulé à cette offre.'], 409);
        }

        $data = $request->validate([
            'resume_id'    => 'required_without:resume_file|nullable|uuid|exists:resumes,id',
            'resume_file'  => 'required_without:resume_id|nullable|file|mimes:pdf|max:2048',
            'cover_letter' => 'nullable|string|max:5000',
        ]);

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

        // Validate resume belongs to user
        if ($resumeId && ! Resume::where('id', $resumeId)->where('userId', $user->id)->exists()) {
            return response()->json(['message' => 'CV invalide.'], 403);
        }

        $application = JobApplication::create([
            'jobVacancyId'       => $id,
            'userId'             => $user->id,
            'resumeId'           => $resumeId,
            'cover_letter'       => $data['cover_letter'] ?? null,
            'status'             => 'pending',
            'aiGeneratedScore'   => 0,
            'aiGeneratedFeedback' => '',
        ]);

        // Analyse IA en arrière-plan (queue)
        AnalyzeJobApplicationJob::dispatch($application->id);

        return response()->json($application->load(['jobVacancy.company', 'resume']), 201);
    }

    public function myApplications(Request $request): JsonResponse
    {
        $applications = JobApplication::with(['jobVacancy.company', 'jobVacancy.jobCategory', 'resume'])
            ->where('userId', $request->user()->id)
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(10);

        return response()->json($applications);
    }

    public function withdrawApplication(Request $request, string $id): JsonResponse
    {
        $application = JobApplication::where('userId', $request->user()->id)
            ->whereNull('deleted_at')
            ->findOrFail($id);

        $application->delete();

        return response()->json(['message' => 'Candidature retirée.']);
    }

    // ─── Company-Owner ────────────────────────────────────────────────────────

    public function companyJobs(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        if (! $company) {
            return response()->json(['message' => 'Aucune entreprise associée.'], 404);
        }

        $jobs = JobVacancy::with(['jobCategory'])
            ->where('companyId', $company->id)
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(10);

        return response()->json($jobs);
    }

    public function store(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        if (! $company) {
            return response()->json(['message' => 'Aucune entreprise associée.'], 404);
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'location' => 'required|string|max:255',
            'type' => 'required|in:Full-time,Contract,Remote,Hybrid',
            'salary' => 'required|numeric|min:0',
            'jobCategoryId' => 'required|uuid|exists:job_categories,id',
        ]);

        $job = JobVacancy::create(array_merge($data, ['companyId' => $company->id]));

        return response()->json($job->load(['company', 'jobCategory']), 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $company = $request->user()->company;
        $job = JobVacancy::where('companyId', $company?->id)->whereNull('deleted_at')->findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:5000',
            'location' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:Full-time,Contract,Remote,Hybrid',
            'salary' => 'sometimes|numeric|min:0',
            'jobCategoryId' => 'sometimes|uuid|exists:job_categories,id',
        ]);

        $job->update($data);

        return response()->json($job->load(['company', 'jobCategory']));
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $company = $request->user()->company;
        $job = JobVacancy::where('companyId', $company?->id)->whereNull('deleted_at')->findOrFail($id);
        $job->delete();

        return response()->json(['message' => 'Offre supprimée.']);
    }

    public function jobApplicants(Request $request, string $id): JsonResponse
    {
        $company = $request->user()->company;
        $job = JobVacancy::where('companyId', $company?->id)->whereNull('deleted_at')->findOrFail($id);

        $applications = JobApplication::with(['user', 'resume'])
            ->where('jobVacancyId', $job->id)
            ->whereNull('deleted_at')
            ->latest()
            ->paginate(10);

        return response()->json($applications);
    }

    public function updateApplicationStatus(Request $request, string $applicationId): JsonResponse
    {
        $company = $request->user()->company;

        $application = JobApplication::whereHas('jobVacancy', fn ($q) => $q->where('companyId', $company?->id))
            ->findOrFail($applicationId);

        $data = $request->validate([
            'status' => 'required|in:pending,reviewed,shortlisted,rejected',
        ]);

        $application->update($data);

        return response()->json($application->load(['user', 'resume', 'jobVacancy']));
    }
}
