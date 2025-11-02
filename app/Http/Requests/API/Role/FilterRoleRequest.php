<?php

namespace App\Http\Requests\API\Role;

use Illuminate\Foundation\Http\FormRequest;

class FilterRoleRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'nom' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255'],
            // Add other filterable fields here, e.g., 'roleable_type' => ['sometimes', 'string']
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
            'nom.string' => 'Le nom du rôle doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du rôle ne doit pas dépasser 255 caractères.',
            'slug.string' => 'Le slug du rôle doit être une chaîne de caractères.',
            'slug.max' => 'Le slug du rôle ne doit pas dépasser 255 caractères.',
        ];
    }
}
