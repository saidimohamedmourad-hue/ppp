<?php

namespace App\Jobs;

use App\Models\JobApplication;
use App\Services\ResumeAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeJobApplicationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public string $applicationId) {}

    public function handle(ResumeAnalysisService $service): void
    {
        $application = JobApplication::with(['resume', 'jobVacancy.jobCategory'])->find($this->applicationId);

        if (! $application || ! $application->jobVacancy) {
            Log::warning("AnalyzeJobApplicationJob: données manquantes pour {$this->applicationId}");
            return;
        }

        if (! $application->resume) {
            $application->update([
                'aiGeneratedScore'    => 0,
                'aiGeneratedFeedback' => '__no_cv__',
            ]);
            return;
        }

        $resume = $application->resume;

        // Extraire les infos structurées du CV si elles sont vides
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

        // Analyser la candidature
        $evaluation = $service->analyzeResume($resumeData, $application->jobVacancy);

        $application->update([
            'aiGeneratedScore'    => $evaluation['aiGeneratedScore'],
            'aiGeneratedFeedback' => $evaluation['aiGeneratedFeedback'],
        ]);

        Log::info("Candidature {$this->applicationId} analysée — score : {$evaluation['aiGeneratedScore']}/100");
    }

    public function failed(\Throwable $e): void
    {
        Log::error("AnalyzeJobApplicationJob échoué pour {$this->applicationId} : " . $e->getMessage());
    }
}
