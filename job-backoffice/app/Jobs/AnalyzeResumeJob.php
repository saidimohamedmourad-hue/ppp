<?php

namespace App\Jobs;

use App\Models\Resume;
use App\Services\ResumeAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Analyse un CV ajouté au profil : extrait (via IA) résumé, compétences,
 * expérience et formation, et les enregistre sur le CV. Ces champs servent
 * ensuite à recommander des formations au candidat (spécialité / catégorie /
 * niveau d'études).
 */
class AnalyzeResumeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public string $resumeId) {}

    public function handle(ResumeAnalysisService $service): void
    {
        $resume = Resume::find($this->resumeId);

        if (! $resume || ! $resume->fileUri) {
            Log::warning("AnalyzeResumeJob: CV introuvable pour {$this->resumeId}");
            return;
        }

        // Idempotent : ne ré-analyse pas un CV déjà renseigné.
        $alreadyDone = trim((string) $resume->summary) !== ''
            || trim((string) $resume->skills) !== ''
            || trim((string) $resume->education) !== '';

        if ($alreadyDone) {
            return;
        }

        $info = $service->extractResumeInformation($resume->fileUri);
        $resume->update($info);

        Log::info("CV {$this->resumeId} analysé (compétences / formation extraites).");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("AnalyzeResumeJob échoué pour {$this->resumeId} : " . $e->getMessage());
    }
}
