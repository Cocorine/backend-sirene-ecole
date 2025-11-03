<?php

namespace App\Http\Requests\Technicien;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateTechnicienRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->type === 'ADMIN'; // Only admin can create technicians
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user.nom_utilisateur' => ['required', 'string', 'max:255'],
            'user.identifiant' => ['required', 'string', 'max:255', Rule::unique('users', 'identifiant')],
            'user.mot_de_passe' => ['required', 'string', 'min:8'],
            'user.type' => ['required', 'string', Rule::in(['TECHNICIEN'])],
            'user.role_id' => ['sometimes', 'string', 'exists:roles,id'],
            'ville_id' => ['required', 'string', 'exists:villes,id'],
            'specialite' => ['required', 'string', 'max:255'],
            'disponibilite' => ['boolean'],
            'date_embauche' => ['nullable', 'date'],
        ];
    }
}