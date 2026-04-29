<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'resume_option' => 'required|string',
            'resume_file' => 'required_if:resume_option,new_resume|file|mimes:pdf|max:2048',
            
        ];
    }
    public function messages()
    {
        return [
            'resume_option.required' => 'Please select a resume option.',
            'resume_file.required' => 'Please upload a resume file.',
            'resume_file.file' => 'The uploaded file must be a valid file.',
            
        ];
    }
}
