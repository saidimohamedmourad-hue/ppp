<?php

namespace App\Http\Controllers;

use App\Models\JobCategory;
use Illuminate\Http\Request;
use App\models\JobVacancy;
use App\Http\Requests\JobVacancyCreateRequest;
use App\Http\Requests\JobVacancyUpdateRequest;
use App\Models\Company;


class JobVacancyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request )
    {
        //active
        $query = JobVacancy::latest();
        if (auth()->user()->role == 'company-owner'){
            $query->where('companyId',auth()->user()->company->id);
        }
        //archive
        if($request->input('archived')=='true'){
            $query->onlyTrashed();
        }

        $jobVacancies = $query->paginate(10)->onEachSide(1);

        return view('job-vacancy.index', compact('jobVacancies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = company::all();
        $categories = JobCategory::all();
        return view('job-vacancy.create',compact('companies','categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JobVacancyCreateRequest $request)
    {
        $validated = $request->validated();
        JobVacancy::create($validated);
        return redirect()->route('job-vacancy.index')->with('success','Job vacancy created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $jobVacancy = JobVacancy::findOrFail($id);
        return view('job-vacancy.show', compact('jobVacancy'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $jobVacancy = JobVacancy::findOrFail($id);
      $companies = company::all();
        $categories = JobCategory::all();
        return view('job-vacancy.edit',compact('jobVacancy','companies','categories'));   
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JobVacancyUpdateRequest $request, string $id)
    {

        $validated = $request->validated();
        $jobVacancy = JobVacancy::findOrFail($id);
        $jobVacancy->update($validated);
        
          if ($request->query('redirectTolist')=='false'){
            return redirect()->route('job-vacancy.show', $jobVacancy->id)->with('success', 'job-vacancy updated successfully.');
        }
        
        return redirect()->route('job-vacancy.index')->with('success', 'job-vacancy updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
     public function destroy(string $id)
    {
        $jobVacancy = JobVacancy::findOrFail($id);
        $jobVacancy->delete();
        return redirect()->route('job-vacancy.index')->with('success', 'job vacancy archived successfully.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
       $jobVacancy= JobVacancy::withTrashed()->findOrFail($id);
       $jobVacancy->restore();
        return redirect()->route('job-vacancy.index', ['archived' => 'true'])->with('success', 'job vacancy restored successfully.');
    }
}
