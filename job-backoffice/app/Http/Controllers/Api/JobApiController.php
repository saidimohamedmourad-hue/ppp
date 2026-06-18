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
            // CV obligatoire pour une candidature à un emploi : il faut fournir
            // soit un CV existant, soit un fichier PDF.
            'resume_id'    => 'required_without:resume_file|nullable|uuid|exists:resumes,id',
            'resume_file'  => 'required_without:resume_id|nullable|file|mimes:pdf|max:2048',
            'cover_letter' => 'nullable|string|max:5000',
            // Niveau d'études (Algérie) : obligatoire, parmi la liste fermée.
            'education_level' => ['required', 'string', \Illuminate\Validation\Rule::in(config('education.levels'))],
            // Phone is mandatory on the first job application: recruiters need
            // a way to contact the candidate. If the user already saved one on
            // their profile we accept it without re-asking; otherwise the
            // client must send it in the body.
            'phone'        => $user->phone ? 'nullable|string|min:6|max:32|regex:/^[0-9+\-\s()]+$/'
                                           : 'required|string|min:6|max:32|regex:/^[0-9+\-\s()]+$/',
        ], [
            'phone.required' => 'Indiquez un numéro de téléphone — les recruteurs s\'en serviront pour vous contacter.',
            'phone.regex'    => 'Le numéro de téléphone contient des caractères invalides.',
            'education_level.required' => 'Sélectionnez votre niveau d\'études.',
            'education_level.in'       => 'Niveau d\'études invalide.',
        ]);

        // Persist the phone on the user when supplied — this makes future
        // applications a 1-click affair and feeds the contact info shown to
        // recruiters on the application list.
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

        // Validate resume belongs to user
        if ($resumeId && ! Resume::where('id', $resumeId)->where('userId', $user->id)->exists()) {
            return response()->json(['message' => 'CV invalide.'], 403);
        }

        $application = JobApplication::create([
            'jobVacancyId'       => $id,
            'userId'             => $user->id,
            'resumeId'           => $resumeId,
            'cover_letter'       => $data['cover_letter'] ?? null,
            'education_level'    => $data['education_level'],
            'status'             => 'pending',
            'aiGeneratedScore'   => 0,
            'aiGeneratedFeedback' => '',
        ]);

        // Analyse IA en arrière-plan (queue)
        AnalyzeJobApplicationJob::dispatch($application->id);

        // Notify the company-owner (in-app bell + email). Wrapped so a mail
        // transport failure (e.g. SMTP timeout) never rolls back the
        // application the candidate just submitted — the bell row is what
        // matters most; email is best-effort.
        $companyOwner = $job->company?->owner;
        if ($companyOwner) {
            try {
                $companyOwner->notify(new \App\Notifications\JobApplicationReceived($application));
            } catch (\Throwable $e) {
                report($e);
            }
        }

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
            'status' => 'required|in:pending,reviewed,shortlisted,accepted,rejected',
        ]);

        $previousStatus = $application->status;
        $application->update($data);

        // Notify the candidate only when the status actually changes, and skip
        // intermediate `reviewed` (just "seen") to avoid spamming the user
        // unless an entreprise wants a paper trail. Tweak this if Product
        // decides reviewed should also notify.
        if ($previousStatus !== $application->status && $application->user) {
            try {
                $application->user->notify(new \App\Notifications\JobApplicationStatusChanged($application->fresh()));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // Return a lean, guaranteed-encodable payload. We deliberately avoid
        // re-serializing the whole model graph (jobVacancy description / AI
        // feedback can hold bytes that trip Laravel's strict JSON encoder).
        // The SPA only needs the new status to update its local row.
        return response()->json([
            'id'     => $application->id,
            'status' => $application->status,
        ]);
    }

    // ─── Recommandations d'offres (selon le CV du candidat) ───────────────────

    public function recommended(Request $request): JsonResponse
    {
        $user = $request->user();

        $resume = Resume::where('userId', $user->id)
            ->whereNull('deleted_at')
            ->latest()
            ->first();

        // Mots-clés du CV analysé (compétences + résumé + formation).
        $cvText = $resume
            ? mb_strtolower(trim(($resume->skills ?: '') . ' ' . ($resume->summary ?: '') . ' ' . ($resume->education ?: '')))
            : '';
        $cvWords = $this->keywords($cvText);

        $jobs = JobVacancy::with(['company', 'jobCategory'])
            ->whereNull('deleted_at')
            ->latest()
            ->get();

        $recommended = $jobs->map(function ($j) use ($cvWords) {
            // Score spécialité/catégorie : mots-clés CV ↔ (titre + catégorie + lieu).
            $target = mb_strtolower(($j->title ?: '') . ' ' . optional($j->jobCategory)->name . ' ' . ($j->location ?: ''));
            $score  = empty($cvWords) ? 0 : count(array_intersect($cvWords, $this->keywords($target)));

            return ['job' => $j, 'score' => $score];
        })
        ->sortByDesc('score')
        ->take(8)
        ->map(fn ($x) => $x['job'])
        ->values();

        return response()->json([
            'data'       => $recommended,
            'has_resume' => (bool) $resume,
        ]);
    }

    /** Mots-clés significatifs (>= 4 lettres, hors mots vides) d'un texte. */
    private function keywords(string $text): array
    {
        $stop = ['avec','pour','dans','des','les','une','aux','sur','par','est','son','ses','the','and','for','with','sans','plus','vous','nous','elle'];
        $words = preg_split('/[^a-z0-9àâçéèêëîïôûùüÿñæœ]+/u', $text) ?: [];
        $words = array_filter($words, fn ($w) => mb_strlen($w) >= 4 && ! in_array($w, $stop, true));

        return array_values(array_unique($words));
    }
}
