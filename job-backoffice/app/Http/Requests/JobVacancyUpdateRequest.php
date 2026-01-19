<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobVacancyUpdateRequest extends FormRequest
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
            'title' => 'required|string|max:255' . $this->route('job-vacancy'),
            'description' => 'required|string|max:1000',
            'type' => 'required|in:Full-time, Contract,Remote,Hybrid',
            'location' => 'required|string|max:255',
            'salary' => 'required|numeric',
            'companyId' =>' required|string|max:255',
            'jobCategoryId'=>'required|string|max:255',
        ];

       
     
    }
    function messages(): array
    {   
        return [

              'title.string' => 'The title must be a string.',
            'description.required' => 'The description is required.',
            'type.required' => 'The type is required.',
            'location.required' => 'The location is required.',
            'salary.required' => 'The salary is required.',
            'companyId.required' => 'The company is required.',
            'jobCategoryId.required' => 'The job category is required.',

            'title.required' => 'The name is required.',
            'description.string' => 'The description must be a string.',
            'type.in' => 'The type must be one of the following: Full-time, Contract,Remote,Hybrid.',
            'location.string' => 'The location must be a string.',
            'salary.numeric' => 'The salary must be a number.',
            'companyId.string' => 'The company must be a string.',
            'jobCategoryId.string' => 'The job category must be a string.',
            
            'title.max' => 'The name may not exceed 255 characters.',
            'description.max' => 'The description may not exceed 1000 characters.',
            'location.max' => 'The location may not exceed 255 characters.',
            'companyId.max' => 'The company may not exceed 255 characters.',
            'jobCategoryId.max' => 'The job category may not exceed 255 characters.',

        ];
    }
 
          
}
