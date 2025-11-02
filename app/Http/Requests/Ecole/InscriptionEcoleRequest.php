<?php

namespace App\Http\Requests\Ecole;

use Illuminate\Foundation\Http\FormRequest;

class InscriptionEcoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Informations de l'école (basées sur le modèle Ecole)
            'nom' => 'required|string|max:255',
            'nom_complet' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'telephone' => 'required|string|max:20',
            'email_contact' => 'nullable|email|max:255',
            'telephone_contact' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:500',
            'types_etablissement' => 'required|array',
            'types_etablissement.*' => 'string|exists:types_etablissement,id',
            
            // Informations du responsable
            'responsable_nom' => 'required|string|max:255',
            'responsable_prenom' => 'required|string|max:255',
            'responsable_telephone' => 'required|string|max:20',
            
            // Localisation
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            
            // Sites additionnels (optionnel pour multi-sites)
            'sites' => 'nullable|array',
            'sites.*.nom' => 'required|string|max:255',
            'sites.*.adresse' => 'nullable|string|max:500',
            'sites.*.ville_id' => 'nullable|string|exists:villes,id',
            'sites.*.latitude' => 'nullable|numeric|between:-90,90',
            'sites.*.longitude' => 'nullable|numeric|between:-180,180',
            
            // Affectation de sirènes (au moins une sirène requise)
            'sirenes' => 'required|array|min:1',
            'sirenes.*.numero_serie' => 'required|string|exists:sirenes,numero_serie',
            'sirenes.*.site_nom' => 'nullable|string', // Nom du site pour multi-sites
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de l\'école est requis.',
            'telephone.required' => 'Le numéro de téléphone est requis.',
            'types_etablissement.required' => 'Le type d\'établissement est requis.',
            'responsable_nom.required' => 'Le nom du responsable est requis.',
            'responsable_prenom.required' => 'Le prénom du responsable est requis.',
            'responsable_telephone.required' => 'Le téléphone du responsable est requis.',
            'sirenes.required' => 'Au moins une sirène doit être affectée.',
            'sirenes.min' => 'Au moins une sirène doit être affectée.',
            'sirenes.*.numero_serie.required' => 'Le numéro de série de la sirène est requis.',
            'sirenes.*.numero_serie.exists' => 'Le numéro de série fourni n\'existe pas.',
        ];
    }
}
