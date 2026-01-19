<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateRequest extends FormRequest
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
             'name' => 'required|string|max:255|unique:companies,name,' . $this->route('company'),
            'address' => 'required|string|max:255',
            'industry' => 'required|string|max:255',
            'website' => 'nullable|string|url|max:255',
            'owner_name' => 'required|string|max:255',
            'owner_password' => 'nullable|string|min:8|max:255',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'The company name is required.',
            'name.string' => 'The company name must be a string.',
            'name.max' => 'The company name may not be greater than 255 characters.',
            'name.unique' => 'The company name has already been taken.',
            'address.required' => 'The address is required.',
            'address.string' => 'The address must be a string.',
            'address.max' => 'The address may not be greater than 255 characters.', 
            'industry.required' => 'The industry is required.',
            'industry.string' => 'The industry must be a string.',
            'industry.max' => 'The industry may not be greater than 255 characters.',
            'website.string' => 'The website must be a string.',
            'website.url' => 'The website must be a valid URL.',
            'website.max' => 'The website may not be greater than 255 characters.',
            'owner_name.required' => 'The owner name is required.',
            'owner_name.string' => 'The owner name must be a string.',
            'owner_name.max' => 'The owner name may not be greater than 255 characters.',
            'owner_password.string' => 'The owner password must be a string.',
            'owner_password.min' => 'The owner password must be at least 8 characters.',
            'owner_password.max' => 'The owner password may not be greater than 255 characters.',

        ];
    }
}
