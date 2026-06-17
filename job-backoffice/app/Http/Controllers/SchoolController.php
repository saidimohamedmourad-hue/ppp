<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolCreateRequest;
use App\Http\Requests\SchoolUpdateRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

 

class SchoolController extends Controller
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
    
    
    public function index(Request $request)
    {
        $query = School::latest();
        if ($request->input('archived') == 'true') {
            $query->onlyTrashed();
        }

        $schools = $query->paginate(5)->onEachSide(1);

        return view('school.index', compact('schools'));
    }

    public function create()
    {
        $industries = $this->industries;
        $unlinkedOwners = User::where('role', 'school-owner')
            ->whereDoesntHave('school')
            ->orderBy('name')
            ->get();

        return view('school.create', compact('industries', 'unlinkedOwners'));
    }

    public function store(SchoolCreateRequest $request)
    {
        $validated = $request->validated();

        if ($validated['owner_mode'] === 'existing') {
            $owner = User::where('role', 'school-owner')
                ->whereDoesntHave('school')
                ->findOrFail($validated['owner_id']);
        } else {
            $owner = User::create([
                'name'     => $validated['owner_name'],
                'email'    => $validated['owner_email'],
                'password' => Hash::make($validated['owner_password']),
                'role'     => 'school-owner',
            ]);
        }

        School::create([
            'name'        => $validated['name'],
            'address'     => $validated['address'],
            'industry'    => $validated['industry'],
            'description' => $validated['description'] ?? null,
            'website'     => $validated['website'] ?? null,
            'phone'       => $validated['phone'],
            'ownerId'     => $owner->id,
        ]);

        return redirect()->route('school.index')->with('success', 'École créée avec succès.');
    }

    public function show(?string $id = null)
    {
        if ($id) {
            $school = School::findOrFail($id);
        } else {
            $school = School::where('ownerId', auth()->user()->id)->firstOrFail();
        }

        // Analytics par formation : vues, inscriptions et inscriptions acceptées.
        $sessionAnalytics = $school->trainingSessions()
            ->whereNull('deleted_at')
            ->withCount([
                'trainingApplications as totalCount',
                'trainingApplications as acceptedCount' => fn ($q) => $q->where('status', 'accepted'),
            ])
            ->orderByDesc('totalCount')
            ->get();

        $analyticsTotals = [
            'views'    => (int) $sessionAnalytics->sum('viewCount'),
            'apps'     => (int) $sessionAnalytics->sum('totalCount'),
            'accepted' => (int) $sessionAnalytics->sum('acceptedCount'),
        ];

        return view('school.show', compact('school', 'sessionAnalytics', 'analyticsTotals'));
    }

    public function edit(?string $id = null)
    {
        $school = $this->getSchool($id);
        $industries = $this->industries;
        return view('school.edit', compact('school', 'industries'));
    }

    public function update(SchoolUpdateRequest $request, ?string $id = null)
    {
        $validated = $request->validated();
        $school = $this->getSchool($id);
         
        $school->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'industry' => $validated['industry'],
            'description' => $validated['description'] ?? null,
            'website' => $validated['website'] ?? null,
            'phone' => $validated['phone'],
        ]);

        $ownerData = [];
        $ownerData['name'] = $validated['owner_name'];
        if (!empty($validated['owner_password'])) {
            $ownerData['password'] = Hash::make($validated['owner_password']);
        }
        $school->owner->update($ownerData);
        if (auth()->user()->role == 'school-owner') {
            return redirect()->route('my-school.show')->with('success', 'School updated successfully.');
        }

        if ($request->query('redirectTolist') == 'false') {
            return redirect()->route('school.show', $school->id)->with('success', 'School updated successfully.');
        }

        return redirect()->route('school.index')->with('success', 'School updated successfully.');
    }

    public function destroy(string $id)
    {
        $school = School::findOrFail($id);
        $school->delete();
        return redirect()->route('school.index')->with('success', 'School archived successfully.');
    }

    public function restore(string $id)
    {
        $school = School::withTrashed()->findOrFail($id);
        $school->restore();
        return redirect()->route('school.index', ['archived' => 'true'])->with('success', 'School restored successfully.');
    }

    private function getSchool(?string $id = null)
    {
        if ($id) {
            return School::findOrFail($id);
        }
        return School::where('ownerId', auth()->user()->id)->firstOrFail();
    }
}
