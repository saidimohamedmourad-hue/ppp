<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolCreateRequest extends FormRequest
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
            'name' => 'required|string|max:255|unique:schools,name',
            'address' => 'required|string|max:255',
            'industry' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'website' => 'nullable|string|url|max:255',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|string|email|max:255|unique:users,email',
            'owner_password' => 'required|string|min:8',
        ];
    }
}
