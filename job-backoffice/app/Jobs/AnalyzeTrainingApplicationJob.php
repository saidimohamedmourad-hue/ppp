<?php

namespace App\Jobs;

use App\Models\TrainingApplication;
use App\Services\ResumeAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeTrainingApplicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public string $applicationId) {}

    public function handle(ResumeAnalysisService $service): void
    {
        $application = TrainingApplication::with([
            'resume',
            'trainingSession.trainingCategory',
            'trainingSession.school',
        ])->find($this->applicationId);

        if (! $application || ! $application->resume || ! $application->trainingSession) {
            Log::warning("AnalyzeTrainingApplicationJob: données manquantes pour {$this->applicationId}");
            return;
        }

        $resume = $application->resume;

        $resumeData = [
            'summary'    => $resume->summary    ?: '',
            'skills'     => $resume->skills     ?: '',
            'experience' => $resume->experience ?: '',
            'education'  => $resume->education  ?: '',
        ];

        $isEmpty = empty(trim($resumeData['summary']))
                && empty(trim($resumeData['skills']))
                && empty(trim($resumeData['experience']))
                && empty(trim($resumeData['education']));

        if ($isEmpty && $resume->fileUri) {
            $extractedInfo = $service->extractResumeInformation($resume->fileUri);
            $resume->update($extractedInfo);
            $resumeData = $extractedInfo;
        }

        $evaluation = $service->analyzeResumeForTraining($resumeData, $application->trainingSession);

        $application->update([
            'aiGeneratedScore'    => $evaluation['aiGeneratedScore'],
            'aiGeneratedFeedback' => $evaluation['aiGeneratedFeedback'],
        ]);

        Log::info("Candidature formation {$this->applicationId} analysée — score : {$evaluation['aiGeneratedScore']}/100");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("AnalyzeTrainingApplicationJob échoué pour {$this->applicationId} : " . $e->getMessage());
    }
}
