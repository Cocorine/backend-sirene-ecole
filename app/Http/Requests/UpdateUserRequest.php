<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation
Rule;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('id'); // Assuming the route parameter for user ID is 'id'

        return [
            'nom_utilisateur' => ['sometimes', 'string', 'max:255', Rule::unique('users', 'nom_utilisateur')->ignore($userId)],
            'role_id' => ['sometimes', 'string', 'exists:roles,id'],

            // UserInfo related fields (assuming they come in the same request)
            'userInfoData.email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('user_infos', 'email')->ignore($userId, 'user_id')],
            'userInfoData.telephone' => ['sometimes', 'string', 'max:20', Rule::unique('user_infos', 'telephone')->ignore($userId, 'user_id')],
            'userInfoData.prenom' => ['nullable', 'string', 'max:255'],
            'userInfoData.nom' => ['nullable', 'string', 'max:255'],
            'userInfoData.adresse' => ['nullable', 'string', 'max:255'],
            'userInfoData.ville_id' => ['nullable', 'string', 'exists:villes,id']
        ];
    }
}
