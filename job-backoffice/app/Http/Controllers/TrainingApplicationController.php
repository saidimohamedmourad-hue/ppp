<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrainingApplicationUpdateRequest;
use App\Models\TrainingApplication;
use Illuminate\Http\Request;

class TrainingApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingApplication::latest();
        if (auth()->user()->role == 'school-owner') {
            $school = auth()->user()->school;
            if ($school) {
                $query->whereHas('trainingSession', function ($q) use ($school) {
                    $q->where('schoolId', $school->id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        if ($request->input('archived') == 'true') {
            $query->onlyTrashed();
        }

        $trainingApplications = $query->paginate(10)->onEachSide(1);

        return view('training-application.index', compact('trainingApplications'));
    }

    public function show(string $id)
    {
        $trainingApplication = $this->resolveTrainingApplication($id, withTrashed: false);
        return view('training-application.show', compact('trainingApplication'));
    }

    public function edit(string $id)
    {
        $trainingApplication = $this->resolveTrainingApplication($id, withTrashed: false);
        return view('training-application.edit', compact('trainingApplication'));
    }

    public function update(TrainingApplicationUpdateRequest $request, string $id)
    {
        $trainingApplication = $this->resolveTrainingApplication($id, withTrashed: false);
        $trainingApplication->update([
            'status' => $request->input('status'),
        ]);
        if ($request->query('redirectTolist') == 'false') {
            return redirect()->route('training-application.show', $id)->with('success', 'Training application updated successfully.');
        }

        return redirect()->route('training-application.index')->with('success', 'Training application updated successfully.');
    }

    public function destroy(string $id)
    {
        $trainingApplication = $this->resolveTrainingApplication($id, withTrashed: false);
        $trainingApplication->delete();
        return redirect()->route('training-application.index')->with('success', 'Training application archived successfully.');
    }

    public function restore(string $id)
    {
        $trainingApplication = $this->resolveTrainingApplication($id, withTrashed: true);
        $trainingApplication->restore();
        return redirect()->route('training-application.index', ['archived' => 'true'])->with('success', 'Training application restored successfully.');
    }

    private function resolveTrainingApplication(string $id, bool $withTrashed): TrainingApplication
    {
        $query = TrainingApplication::query();
        if ($withTrashed) {
            $query->withTrashed();
        }
        $trainingApplication = $query->with('trainingSession')->findOrFail($id);

        if (auth()->user()->role === 'school-owner') {
            $school = auth()->user()->school;
            $session = $trainingApplication->trainingSession;
            if (! $school || ! $session || $session->schoolId !== $school->id) {
                abort(403);
            }
        }

        return $trainingApplication;
    }
}
