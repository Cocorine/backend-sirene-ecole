<?php

namespace App\Http\Requests\Technicien;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTechnicienRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->type === 'ADMIN'; // Only admin can update technicians
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('technicien')->user->id; // Assuming route model binding for technicien

        return [
            'user.nom_utilisateur' => ['sometimes', 'string', 'max:255'],
            'user.identifiant' => ['sometimes', 'string', 'max:255', Rule::unique('users', 'identifiant')->ignore($userId)],
            'user.mot_de_passe' => ['sometimes', 'string', 'min:8'],
            'user.type' => ['sometimes', 'string', Rule::in(['TECHNICIEN'])],
            'user.role_id' => ['sometimes', 'string', 'exists:roles,id'],
            'ville_id' => ['sometimes', 'string', 'exists:villes,id'],
            'specialite' => ['sometimes', 'string', 'max:255'],
            'disponibilite' => ['boolean'],
            'date_embauche' => ['nullable', 'date'],
        ];
    }
}