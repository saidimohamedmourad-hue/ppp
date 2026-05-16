<?php

namespace App\Http\Controllers;

use App\Models\TrainingApplication;

class TrainingApplicationController extends Controller
{
    public function index()
    {
        $trainingApplications = TrainingApplication::with([
            'trainingSession' => fn ($q) => $q->withTrashed()->with(['school', 'trainingCategory']),
            'resume',
        ])
            ->where('userId', auth()->id())
            ->latest()
            ->paginate(10);

        return view('training-applications.index', compact('trainingApplications'));
    }
}
