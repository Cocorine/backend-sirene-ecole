<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgrammationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ecole_id' => ['sometimes', 'required', 'exists:ecoles,id'],
            'site_id' => ['sometimes', 'required', 'exists:sites,id'],
            'abonnement_id' => ['sometimes', 'required', 'exists:abonnements,id'],
            'calendrier_id' => ['sometimes', 'nullable', 'exists:calendriers_scolaires,id'],
            'nom_programmation' => ['sometimes', 'required', 'string', 'max:255'],
            'horaire_json' => ['sometimes', 'nullable', 'array'],
            'horaire_json.*.jour' => ['required_with:horaire_json', 'string', Rule::in(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'])],
            'horaire_json.*.heure' => ['required_with:horaire_json', 'date_format:H:i'],
            'horaires_sonneries' => ['sometimes', 'nullable', 'array'],
            'horaires_sonneries.*' => ['date_format:H:i'],
            'horaire_debut' => ['sometimes', 'nullable', 'date_format:H:i'],
            'horaire_fin' => ['sometimes', 'nullable', 'date_format:H:i', 'after:horaire_debut'],
            'jour_semaine' => ['sometimes', 'nullable', 'array'],
            'jour_semaine.*' => ['string', Rule::in(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'])],
            'jours_feries_inclus' => ['sometimes', 'boolean'],
            'vacances' => ['sometimes', 'nullable', 'array'],
            'vacances.*' => ['string', 'max:255'],
            'types_etablissement' => ['sometimes', 'nullable', 'array'],
            'types_etablissement.*' => ['string', 'max:255'],
            'chaine_programmee' => ['sometimes', 'nullable', 'string'],
            'chaine_cryptee' => ['sometimes', 'nullable', 'string'],
            'date_debut' => ['sometimes', 'required', 'date', 'before_or_equal:date_fin'],
            'date_fin' => ['sometimes', 'required', 'date', 'after_or_equal:date_debut'],
            'actif' => ['sometimes', 'boolean'],
            'cree_par' => ['sometimes', 'required', 'exists:users,id'],
        ];
    }
}