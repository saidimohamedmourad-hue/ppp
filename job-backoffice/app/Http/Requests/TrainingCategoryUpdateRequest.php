<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingCategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('training_category') ?? $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('training_categories', 'name')->ignore($id)],
            'description' => 'nullable|string|max:1000',
        ];
    }
}
