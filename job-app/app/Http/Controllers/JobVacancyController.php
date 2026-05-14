<?php

namespace App\Http\Controllers;
use App\Models\JobApplication;
use App\Models\JobVacancy;
use Illuminate\Http\Request;
use App\Http\Requests\ApplyJobRequest;
use App\Models\Resume;
use OpenAI\Laravel\Facades\OpenAI;
use App\Services\ResumeAnalysisService;
class JobVacancyController extends Controller
{
    protected $resumeAnalysisService;
    public function __construct( ResumeAnalysisService $resumeAnalysisService){
        $this->resumeAnalysisService = $resumeAnalysisService;
    }
    public function show(string $id)
    {
        $jobVacancy = JobVacancy::findOrFail($id);
        return view('job-vacancies.show', compact('jobVacancy'));
    }

    public function apply(string $id){
        $jobVacancy = JobVacancy::findOrFail($id);
        $resumes = auth()->user()->resumes;
        return view('job-vacancies.apply', compact('jobVacancy', 'resumes'));
    }

    public function processApllication(ApplyJobRequest $request, string $id)  {

        $jobVacancy = JobVacancy::findOrFail($id);
        $resumeId = null;
        $extractedInfo = null;

        if($request->input('resume_option') === 'new_resume') {

        $file= $request->file('resume_file');
        $extension = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $fileName = 'resume_' . time() . '.' . $extension;

        // store in laravel cloud 
        $path = $file->storeAs('resumes', $fileName, 'cloud');

        $fileUrI = config('filesystems.disks.cloud.url') . '/' . $path;

        
        $extractedInfo = $this->resumeAnalysisService->extractResumeInformation($fileUrI);


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
            $resumeId =  $request->input('resume_option');
            
            $resume = Resume::findorFail($resumeId);

            $extractedInfo = [
                'summary' => $resume->summary,
                'skills' => $resume->skills,
                'experience' => $resume->experience,
                'education' => $resume->education,
            ];

        };
         //TODO: evalute Jobapplication with AI and generate score and feedback
         $evaluation = $this->resumeAnalysisService->analyzeResume($extractedInfo,$jobVacancy); 
        JobApplication::create([
            'jobVacancyId' => $id,
            'resumeId' => $resumeId,
            'status' => 'pending',
            'userId' => auth()->id(),
            'aiGeneratedScore' => $evaluation['aiGeneratedScore'],
            'aiGeneratedFeedback' => $evaluation['aiGeneratedFeedback'],
        ]);
        
        return redirect()->route('job-applications.index', $id)->with('success', 'Your application has been submitted successfully.');
    }

//     public function testOpenAI() {  

// $response = OpenAI::chat()->create([
//     'model' => 'gpt-4o-mini',
//     'messages' => [
//         [
//             'role' => 'system',
//             'content' => 'You are a helpful assistant for job applications.',
//         ],
//         [
//             'role' => 'user',
//             'content' => 'Hello! Can you help me with my job application?',
//         ],
//     ],
    
// ]);

//    echo $response->choices[0]->message->content; // Hello! How can I assist you today?
// }
}
