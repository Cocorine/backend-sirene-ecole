<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgrammationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'calendrier_id' => ['nullable', 'exists:calendriers_scolaires,id'],
            'nom_programmation' => ['required', 'string', 'max:255'],
            'horaire_json' => ['nullable', 'array'],
            'horaire_json.*.jour' => ['required_with:horaire_json', 'string', Rule::in(['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'])],
            'horaire_json.*.heure' => ['required_with:horaire_json', 'date_format:H:i'],
            'jours_feries_inclus' => ['boolean'],
            'jours_feries_exceptions' => ['nullable', 'array'],
            'jours_feries_exceptions.*.date' => ['required', 'date_format:Y-m-d'],
            'jours_feries_exceptions.*.action' => ['required', 'string', Rule::in(['include', 'exclude'])],
            'date_debut' => ['required', 'date', 'before_or_equal:date_fin'], //peut etre date de debut de la periode d'evaluation
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'] //peut etre date de debut de la periode d'evaluation
        ];
    }
}
