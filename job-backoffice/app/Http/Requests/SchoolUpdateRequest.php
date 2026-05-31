<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'website' => $this->filled('website') ? $this->input('website') : null,
            'description' => $this->filled('description') ? $this->input('description') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'industry' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'website' => 'nullable|string|url|max:255',
            'phone' => 'required|string|min:6|max:32|regex:/^[0-9+\-\s()]+$/',
            'owner_name' => 'required|string|max:255',
            'owner_password' => 'nullable|string|min:8',
        ];
    }
}
