<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrainingSessionCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nullableIfEmpty = ['endDate', 'startTime', 'endTime', 'salary'];
        $merge = [];
        foreach ($nullableIfEmpty as $field) {
            if (! $this->filled($field)) {
                $merge[$field] = null;
            }
        }
        if (auth()->user()->role === 'school-owner' && ($school = auth()->user()->school)) {
            $merge['schoolId'] = $school->id;
        }
        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'location' => 'required|string|max:255',
            'trainingDate' => 'required|date',
            'endDate' => 'nullable|date|after_or_equal:trainingDate',
            'startTime' => 'nullable',
            'endTime' => 'nullable',
            'maxParticipants' => 'required|integer|min:1',
            'currentParticipants' => 'nullable|integer|min:0',
            'status' => 'required|in:open,closed,cancelled',
            'type' => 'required|in:en_ligne,accelerer,presentiel',
            'cancellation_reason' => 'nullable|required_if:status,cancelled|string|max:1000',
            'salary' => 'nullable|string|max:255',
            'trainingCategoryId' => 'required|uuid|exists:training_categories,id',
            'schoolId' => 'required|uuid|exists:schools,id',
        ];
    }
}
