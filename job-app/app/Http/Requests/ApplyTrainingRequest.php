<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resume_option' => 'required|string',
            'resume_file' => 'required_if:resume_option,new_resume|file|mimes:pdf|max:2048',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'resume_option.required' => 'Please select a resume option.',
            'resume_file.required_if' => 'Please upload a resume file.',
            'resume_file.file' => 'The uploaded file must be a valid file.',
            'resume_file.mimes' => 'The resume file must be a PDF.',
            'resume_file.max' => 'The resume file must not exceed 2MB.',
        ];
    }
}
