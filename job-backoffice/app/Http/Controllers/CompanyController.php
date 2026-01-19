<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use Illuminate\Http\Request;
use App\Http\Requests\CompanyCreateRequest;
use App\Http\Requests\CompanyUpdateRequest;
use App\models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

 

class CompanyController extends Controller
{
    public $industries = [
            'Technology',
            'Healthcare',
            'Finance',
            'Education',
            'Retail',
            'Manufacturing',
            'Hospitality',
            'Transportation',
            'Construction',
            'Real Estate',
        ];
    
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request )
    {
        //active
        $query = Company::latest();
        //archive
        if($request->input('archived')=='true'){
            $query->onlyTrashed();
        }

        $companies = $query->paginate(5)->onEachSide(1);

        return view('company.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    { 
        $industries = $this-> industries;
       
        return view('company.create', compact('industries'));
    }

    /**
     * Store a newly created resource in storage.
     */
     public function store(CompanyCreateRequest $request)

    {
        
        $validated = $request->validated();
        //create owner

        $owner = User::create([
            'name' => $validated['owner_name'],
            'email' => $validated['owner_email'],
            'password' => Hash::make($validated['owner_password']),
            'role' => 'company-owner',
        ]);

        // return error if owner not created
        if (!$owner) {
            return redirect()->route('company.create')->with('error', 'Failed to create company owner.');
        }

        //create company


        Company::create([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'industry' => $validated['industry'],
            'website' => $validated['website'] ?? null,
            'ownerId' => $owner->id,
        ]);
        return redirect()->route('company.index')->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id = null)
    {   if($id){
        $company = Company::findOrFail($id);
    }else{
        $company = company::where('ownerId',auth()->user()->id)->first();
    }
        
       // $applications = JobApplication::with('user')->wherein('jobvacancyId',$company->jobVacancies->pluck('id'))->get();
        return view('company.show', compact('company', ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id = null)
    {
         if($id){
        $company = Company::findOrFail($id);
    }else{
        $company = company::where('ownerId',auth()->user()->id)->first();
    }
        $industries = $this-> industries;
        return view('company.edit', compact('company','industries'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompanyUpdateRequest $request, string $id = null)
    {
        $validated = $request->validated();
        $company = $this->getCompany($id);
         
        //update company info

        $company->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'industry' => $validated['industry'],
            'website' => $validated['website'] ?? null,
        ]);

        //update owner info
        $ownerData=[];
        $ownerData['name']=$validated['owner_name'];
        if($validated['owner_password']){
            $ownerData['password'] = Hash::make($validated['owner_password']);
        }
        $company->owner->update($ownerData);
       if (auth()->user()->role == 'company-owner'){
             return redirect()->route('my-company.show')->with('success', 'Company updated successfully.'); 
        }

        if ($request->query('redirectTolist')=='false'){
            return redirect()->route('company.show', $company->id)->with('success', 'Company updated successfully.');
        }
        
        return redirect()->route('company.index')->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $company = Company::findOrFail($id);
        $company->delete();
        return redirect()->route('company.index')->with('success', 'Company archived successfully.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $company = Company::withTrashed()->findOrFail($id);
        $company->restore();
        return redirect()->route('company.index', ['archived' => 'true'])->with('success', 'Company restored successfully.');
    }

    private function getCompany(string $id = null){
        if($id){
            return company::findOrFail($id);
        }
        return Company::where('ownerId',auth()->user()->id)->first();
    }
}
