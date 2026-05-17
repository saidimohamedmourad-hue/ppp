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
            'website'     => $this->filled('website') ? $this->input('website') : null,
            'description' => $this->filled('description') ? $this->input('description') : null,
        ]);
    }

    public function rules(): array
    {
        $isExisting = $this->input('owner_mode') === 'existing';

        return [
            'name'           => 'required|string|max:255|unique:schools,name',
            'address'        => 'required|string|max:255',
            'industry'       => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'website'        => 'nullable|url|max:255',
            'owner_mode'     => 'required|in:new,existing',

            // Mode: lier un compte existant
            'owner_id'       => $isExisting ? 'required|uuid|exists:users,id' : 'nullable',

            // Mode: créer un nouveau compte
            'owner_name'     => $isExisting ? 'nullable' : 'required|string|max:255',
            'owner_email'    => $isExisting ? 'nullable' : 'required|string|email|max:255|unique:users,email',
            'owner_password' => $isExisting ? 'nullable' : 'required|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'Le nom de l\'école est requis.',
            'name.unique'            => 'Ce nom d\'école est déjà pris.',
            'address.required'       => 'L\'adresse est requise.',
            'industry.required'      => 'Le secteur est requis.',
            'owner_id.required'      => 'Veuillez sélectionner un utilisateur existant.',
            'owner_id.exists'        => 'L\'utilisateur sélectionné est introuvable.',
            'owner_name.required'    => 'Le nom du propriétaire est requis.',
            'owner_email.required'   => 'L\'email du propriétaire est requis.',
            'owner_email.unique'     => 'Cet email est déjà utilisé.',
            'owner_password.required'=> 'Le mot de passe est requis (min. 8 caractères).',
            'owner_password.min'     => 'Le mot de passe doit contenir au moins 8 caractères.',
        ];
    }
}
