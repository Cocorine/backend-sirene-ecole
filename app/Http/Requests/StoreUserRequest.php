<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
        return [
            /*'nom_utilisateur' => ['sometimes', 'string', 'max:255', 'unique:users,nom_utilisateur'],
            'identifiant' => ['sometimes', 'string', 'max:255', 'unique:users,identifiant'],
            'mot_de_passe' => ['required', 'string', 'min:8'],
            'type' => ['required', 'string', 'max:255'],*/
            'role_id' => ['required', 'string', 'exists:roles,id'],
            /*
            'actif' => ['sometimes', 'boolean'],
            'statut' => ['sometimes', 'integer'],*/

            // UserInfo related fields (assuming they come in the same request)
            'userInfoData.email' => ['nullable', 'string', 'email', 'max:255', 'unique:user_infos,email'],
            'userInfoData.telephone' => ['required', 'string', 'max:20', 'unique:user_infos,telephone'],
            'userInfoData.prenom' => ['required', 'string', 'max:255'],
            'userInfoData.nom' => ['required', 'string', 'max:255'],
            'userInfoData.adresse' => ['nullable', 'string', 'max:255'],
            'userInfoData.ville_id' => ['nullable', 'string', 'exists:villes,id']
        ];
    }
}
