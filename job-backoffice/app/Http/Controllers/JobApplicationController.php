<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobApplicationUpdateRequest;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index(Request $request )
    {
        //active
        $query = JobApplication::latest();
        if (auth()->user()->role == 'company-owner'){
            $query->whereHas('jobVacancy',function($query){
                $query->where('companyId',auth()->user()->company->id);
            });
        }
        //archive
        if($request->input('archived')=='true'){
            $query->onlyTrashed();
        }

        $jobApplications = $query->paginate(10)->onEachSide(1);

        return view('job-application.index', compact('jobApplications'));
    }

  
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $jobApplication = JobApplication::findOrFail(($id));
        return view ('job-application.show',compact('jobApplication'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $jobApplication = JobApplication::findOrFail($id);
        return view('job-application.edit', compact('jobApplication'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JobApplicationUpdateRequest $request, string $id)
    {
        
        $jobApplication = JobApplication::findOrFail($id);
        $jobApplication->update([
            'status' => $request->input('status')
        ]);
        if ($request->query('redirectTolist')=='false'){
            return redirect()->route('job-application.show', $id)->with('success', 'job application updated successfully.');
        }
        
        return redirect()->route('job-application.index')->with('success', 'Job application updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
     public function destroy(string $id)
    {
        $jobApplication = JobApplication::findOrFail($id);
        $jobApplication->delete();
        return redirect()->route('job-application.index')->with('success', 'job application archived successfully.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
       $jobApplication= JobApplication::withTrashed()->findOrFail($id);
       $jobApplication->restore();
        return redirect()->route('job-application.index', ['archived' => 'true'])->with('success', 'job application restored successfully.');
    }
}
