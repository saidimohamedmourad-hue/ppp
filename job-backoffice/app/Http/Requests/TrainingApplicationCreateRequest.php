<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TrainingApplicationCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,accepted,rejected',
            'aiGeneratedScore' => 'nullable|numeric|min:0|max:99.99',
            'aiGeneratedFeedback' => 'nullable|string|max:1000',
            'trainingSessionId' => 'required|string|max:255',
            'userId' => 'required|string|max:255',
            'resumeId' => 'required|string|max:255',
        ];
    }
}
