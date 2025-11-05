<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJourFerieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Get the ID of the JourFerie being updated from the route parameters
        $jourFerieId = $this->route('jour_fery') ? $this->route('jour_fery')->id : null;

        return [
            'intitule_journee' => ['sometimes', 'required', 'string', 'max:255'],
            'type_jour' => ['sometimes', 'required', 'string', Rule::in(['ferie', 'conge_fin_annee', 'conge_detente', 'conge_paque', 'autre_conge'])],
            'date_debut' => [
                'sometimes',
                'required',
                'date',
                // Unique validation for date_debut, considering ecole_id and type_jour, ignoring the current record
                Rule::unique('jours_feries')->ignore($jourFerieId)->where(function ($query) {
                    return $query->where('ecole_id', $this->input('ecole_id'))
                                 ->where('type_jour', $this->input('type_jour'));
                }),
            ],
            'date_fin' => ['sometimes', 'nullable', 'date', 'after_or_equal:date_debut', 'required_if:type_jour,conge_fin_annee,conge_detente,conge_paque,autre_conge'],
            'description' => ['sometimes', 'nullable', 'string'],
            'est_national' => ['sometimes', 'boolean'],
            'ecole_id' => ['sometimes', 'nullable', 'exists:ecoles,id'],
            'calendrier_id' => ['sometimes', 'nullable', 'exists:calendriers_scolaires,id'],
            'pays_id' => ['sometimes', 'nullable', 'exists:pays,id'],
            'recurrent' => ['sometimes', 'boolean'],
            'actif' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_debut.unique' => 'Un jour férié ou un congé de ce type existe déjà pour cette date et cette école.',
            'date_fin.required_if' => 'La date de fin est requise pour les types de congés.',
        ];
    }
}