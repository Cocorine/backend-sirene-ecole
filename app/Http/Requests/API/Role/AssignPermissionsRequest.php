<?php

namespace App\Http\Requests\API\Role;

use Illuminate\Foundation\Http\FormRequest;

class AssignPermissionsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust authorization logic as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['string', 'max:26', 'exists:permissions,id'], // ULID
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'permission_ids.required' => 'Les IDs des permissions sont obligatoires.',
            'permission_ids.array' => 'Les permissions doivent être un tableau.',
            'permission_ids.*.string' => 'Chaque ID de permission doit être une chaîne de caractères.',
            'permission_ids.*.max' => 'Chaque ID de permission ne doit pas dépasser 26 caractères.',
            'permission_ids.*.exists' => 'Une ou plusieurs permissions spécifiées n\'existent pas.',
        ];
    }
}