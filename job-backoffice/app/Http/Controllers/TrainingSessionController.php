<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrainingSessionCreateRequest;
use App\Http\Requests\TrainingSessionUpdateRequest;
use App\Models\School;
use App\Models\TrainingCategory;
use App\Models\TrainingSession;
use Illuminate\Http\Request;

class TrainingSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingSession::latest();
        if (auth()->user()->role == 'school-owner') {
            $school = auth()->user()->school;
            if ($school) {
                $query->where('schoolId', $school->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        if ($request->input('archived') == 'true') {
            $query->onlyTrashed();
        }

        $trainingSessions = $query->paginate(10)->onEachSide(1);

        return view('training-session.index', compact('trainingSessions'));
    }

    public function create()
    {
        $schools = $this->schoolsForForm();
        $categories = TrainingCategory::all();
        return view('training-session.create', compact('schools', 'categories'));
    }

    public function store(TrainingSessionCreateRequest $request)
    {
        $validated = $request->validated();
        TrainingSession::create($validated);
        return redirect()->route('training-session.index')->with('success', 'Training session created successfully');
    }

    public function show(string $id)
    {
        $trainingSession = $this->resolveTrainingSession($id, withTrashed: false);
        return view('training-session.show', compact('trainingSession'));
    }

    public function edit(string $id)
    {
        $trainingSession = $this->resolveTrainingSession($id, withTrashed: false);
        $schools = $this->schoolsForForm();
        $categories = TrainingCategory::all();
        return view('training-session.edit', compact('trainingSession', 'schools', 'categories'));
    }

    public function update(TrainingSessionUpdateRequest $request, string $id)
    {
        $validated = $request->validated();
        $trainingSession = $this->resolveTrainingSession($id, withTrashed: false);
        $trainingSession->update($validated);

        if ($request->query('redirectTolist') == 'false') {
            return redirect()->route('training-session.show', $trainingSession->id)->with('success', 'Training session updated successfully.');
        }

        return redirect()->route('training-session.index')->with('success', 'Training session updated successfully.');
    }

    public function destroy(string $id)
    {
        $trainingSession = $this->resolveTrainingSession($id, withTrashed: false);
        $trainingSession->delete();
        return redirect()->route('training-session.index')->with('success', 'Training session archived successfully.');
    }

    public function restore(string $id)
    {
        $trainingSession = $this->resolveTrainingSession($id, withTrashed: true);
        $trainingSession->restore();
        return redirect()->route('training-session.index', ['archived' => 'true'])->with('success', 'Training session restored successfully.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, School>
     */
    private function schoolsForForm()
    {
        if (auth()->user()->role === 'school-owner') {
            $school = auth()->user()->school;
            return $school ? collect([$school]) : collect();
        }

        return School::all();
    }

    private function resolveTrainingSession(string $id, bool $withTrashed): TrainingSession
    {
        $query = TrainingSession::query();
        if ($withTrashed) {
            $query->withTrashed();
        }
        $trainingSession = $query->findOrFail($id);

        if (auth()->user()->role === 'school-owner') {
            $school = auth()->user()->school;
            if (! $school || $trainingSession->schoolId !== $school->id) {
                abort(403);
            }
        }

        return $trainingSession;
    }
}
