<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJourFerieRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'intitule_journee' => ['required', 'string', 'max:255'],
            'type_jour' => ['required', 'string', Rule::in(['ferie', 'conge_fin_annee', 'conge_detente', 'conge_paque', 'autre_conge'])],
            'date_debut' => [
                'required',
                'date',
                // Unique validation for date_debut, considering ecole_id and type_jour
                Rule::unique('jours_feries')->where(function ($query) {
                    return $query->where('ecole_id', $this->input('ecole_id'))
                                 ->where('type_jour', $this->input('type_jour'));
                }),
            ],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut', 'required_if:type_jour,conge_fin_annee,conge_detente,conge_paque,autre_conge'],
            'description' => ['nullable', 'string'],
            'est_national' => ['boolean'],
            'ecole_id' => ['nullable', 'exists:ecoles,id'],
            'calendrier_id' => ['nullable', 'exists:calendriers_scolaires,id'],
            'pays_id' => ['nullable', 'exists:pays,id'],
            'recurrent' => ['boolean'],
            'actif' => ['boolean'],
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