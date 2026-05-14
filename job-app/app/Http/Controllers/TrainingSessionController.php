<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyTrainingRequest;
use App\Models\Resume;
use App\Models\TrainingApplication;
use App\Models\TrainingSession;
use App\Services\ResumeAnalysisService;
use Illuminate\Http\Request;

class TrainingSessionController extends Controller
{
    public function __construct(protected ResumeAnalysisService $resumeAnalysisService)
    {
    }

    public function index(Request $request)
    {
        $query = TrainingSession::query()
            ->with(['trainingCategory', 'school'])
            ->where('status', 'open');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%')
                    ->orWhereHas('school', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('trainingCategory', function ($q) use ($search) {
                        $q->where('name', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($request->filled('category')) {
            $query->where('trainingCategoryId', $request->input('category'));
        }

        $trainingSessions = $query->latest()->paginate(10)->withQueryString();

        return view('training-sessions.index', compact('trainingSessions'));
    }

    public function show(string $id)
    {
        $trainingSession = TrainingSession::with(['trainingCategory', 'school'])
            ->where('status', 'open')
            ->findOrFail($id);

        $trainingSession->increment('viewCount');

        return view('training-sessions.show', compact('trainingSession'));
    }

    public function apply(string $id)
    {
        $trainingSession = TrainingSession::where('status', 'open')->findOrFail($id);

        $alreadyApplied = TrainingApplication::where('userId', auth()->id())
            ->where('trainingSessionId', $trainingSession->id)
            ->exists();

        if ($alreadyApplied) {
            return redirect()
                ->route('training-applications.index')
                ->with('error', 'You have already applied to this training session.');
        }

        $resumes = auth()->user()->resumes;

        return view('training-sessions.apply', compact('trainingSession', 'resumes'));
    }

    public function processApplication(ApplyTrainingRequest $request, string $id)
    {
        $trainingSession = TrainingSession::with(['trainingCategory', 'school'])
            ->where('status', 'open')
            ->findOrFail($id);

        $alreadyApplied = TrainingApplication::where('userId', auth()->id())
            ->where('trainingSessionId', $trainingSession->id)
            ->exists();

        if ($alreadyApplied) {
            return redirect()
                ->route('training-applications.index')
                ->with('error', 'You have already applied to this training session.');
        }

        $resumeId = null;
        $extractedInfo = null;

        if ($request->input('resume_option') === 'new_resume') {
            $file = $request->file('resume_file');
            $extension = $file->getClientOriginalExtension();
            $originalName = $file->getClientOriginalName();
            $fileName = 'resume_'.time().'.'.$extension;

            $path = $file->storeAs('resumes', $fileName, 'cloud');

            $fileUri = config('filesystems.disks.cloud.url').'/'.$path;

            $extractedInfo = $this->resumeAnalysisService->extractResumeInformation($fileUri);

            $resume = Resume::create([
                'filename' => $originalName,
                'fileUri' => $path,
                'userId' => auth()->id(),
                'contactDetails' => json_encode([
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ]),
                'summary' => $extractedInfo['summary'],
                'skills' => $extractedInfo['skills'],
                'experience' => $extractedInfo['experience'],
                'education' => $extractedInfo['education'],
            ]);

            $resumeId = $resume->id;
        } else {
            $resumeId = $request->input('resume_option');
            $resume = Resume::where('userId', auth()->id())->findOrFail($resumeId);

            $extractedInfo = [
                'summary' => $resume->summary,
                'skills' => $resume->skills,
                'experience' => $resume->experience,
                'education' => $resume->education,
            ];
        }

        $evaluation = $this->resumeAnalysisService->analyzeResumeForTrainingSession($extractedInfo, $trainingSession);

        TrainingApplication::create([
            'trainingSessionId' => $trainingSession->id,
            'resumeId' => $resumeId,
            'status' => 'pending',
            'userId' => auth()->id(),
            'aiGeneratedScore' => $evaluation['aiGeneratedScore'],
            'aiGeneratedFeedback' => $evaluation['aiGeneratedFeedback'],
        ]);

        return redirect()
            ->route('training-applications.index')
            ->with('success', 'Your training application has been submitted successfully.');
    }
}
