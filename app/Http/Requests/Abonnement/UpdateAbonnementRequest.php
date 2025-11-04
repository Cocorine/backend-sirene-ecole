<?php

namespace App\Http\Requests\Abonnement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAbonnementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // À adapter selon les permissions
    }

    public function rules(): array
    {
        return [
            'date_debut' => 'sometimes|date|before_or_equal:date_fin',
            'date_fin' => 'sometimes|date|after_or_equal:date_debut',
            'montant' => 'sometimes|numeric|min:0',
            'statut' => 'sometimes|in:actif,expire,en_attente,suspendu',
            'auto_renouvellement' => 'sometimes|boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'date_debut.before_or_equal' => 'La date de début doit être antérieure ou égale à la date de fin.',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'montant.min' => 'Le montant doit être supérieur ou égal à 0.',
            'statut.in' => 'Le statut doit être : actif, expire, en_attente ou suspendu.',
        ];
    }
}
