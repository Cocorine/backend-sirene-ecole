<?php

namespace App\Http\Requests\Ecole;

use App\Enums\TypeEtablissement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="InscriptionEcoleRequest",
 *     title="School Registration Request",
 *     description="Request body for registering a new school",
 *     required={
 *         "nom", "nom_complet", "telephone_contact", "types_etablissement",
 *         "responsable_nom", "responsable_prenom", "responsable_telephone",
 *         "site_principal"
 *     },
 *     @OA\Property(
 *         property="nom",
 *         type="string",
 *         description="Name of the school",
 *         maxLength=100
 *     ),
 *     @OA\Property(
 *         property="nom_complet",
 *         type="string",
 *         description="Full name of the school"
 *     ),
 *     @OA\Property(
 *         property="telephone_contact",
 *         type="string",
 *         description="Contact phone number for the school",
 *         maxLength=20
 *     ),
 *     @OA\Property(
 *         property="email_contact",
 *         type="string",
 *         format="email",
 *         nullable=true,
 *         description="Contact email for the school",
 *         maxLength=100
 *     ),
 *     @OA\Property(
 *         property="types_etablissement",
 *         type="array",
 *         description="Array of establishment type IDs",
 *         @OA\Items(
 *             type="string",
 *             format="uuid"
 *         )
 *     ),
 *     @OA\Property(
 *         property="responsable_nom",
 *         type="string",
 *         description="Last name of the person in charge",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="responsable_prenom",
 *         type="string",
 *         description="First name of the person in charge",
 *         maxLength=255
 *     ),
 *     @OA\Property(
 *         property="responsable_telephone",
 *         type="string",
 *         description="Phone number of the person in charge",
 *         maxLength=20
 *     ),
 *     @OA\Property(
 *         property="site_principal",
 *         ref="#/components/schemas/SitePrincipalRequest"
 *     ),
 *     @OA\Property(
 *         property="sites_annexe",
 *         type="array",
 *         nullable=true,
 *         description="Array of annex sites (optional for multi-site schools)",
 *         @OA\Items(
 *             ref="#/components/schemas/SitesAnnexeRequest"
 *         )
 *     )
 * )
 */
class InscriptionEcoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Informations de l'école (basées sur la migration)
            'nom' => ['required', 'string', 'max:100'],
            'nom_complet' => ['required', 'string'],
            'reference' => ['nullable', 'string'],
            'telephone_contact' => ['required', 'string', 'max:20', 'unique:ecoles,telephone_contact'],
            'email_contact' => ['nullable', 'email', 'max:100', 'unique:ecoles,email_contact'],
            'types_etablissement' => ['required', 'array'],
            'types_etablissement.*' => ['string', Rule::in(TypeEtablissement::values())],

            // Informations du responsable
            'responsable_nom' => ['required', 'string', 'max:255'],
            'responsable_prenom' => ['required', 'string', 'max:255'],
            'responsable_telephone' => ['required', 'string', 'max:20'],

            // Site principal (obligatoire avec une sirène)
            'site_principal' => ['required', 'array'],
            'site_principal.adresse' => ['required', 'string', 'max:500'],
            'site_principal.ville_id' => ['required', 'string', 'exists:villes,id'],
            'site_principal.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'site_principal.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'site_principal.sirene' => ['required', 'array'],
            'site_principal.sirene.numero_serie' => ['required', 'string', 'exists:sirenes,numero_serie'],

            // Sites annexes (optionnel pour multi-sites)
            'sites_annexe' => ['nullable', 'array'],
            'sites_annexe.*.nom' => ['required', 'string', 'max:255'],
            'sites_annexe.*.adresse' => ['nullable', 'string', 'max:500'],
            'sites_annexe.*.ville_id' => ['nullable', 'string', 'exists:villes,id'],
            'sites_annexe.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'sites_annexe.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'sites_annexe.*.sirene' => ['required', 'array'],
            'sites_annexe.*.sirene.numero_serie' => ['required', 'string', 'exists:sirenes,numero_serie']
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de l\'école est requis.',
            'telephone.required' => 'Le numéro de téléphone est requis.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'types_etablissement.required' => 'Le type d\'établissement est requis.',
            'responsable_nom.required' => 'Le nom du responsable est requis.',
            'responsable_prenom.required' => 'Le prénom du responsable est requis.',
            'responsable_telephone.required' => 'Le téléphone du responsable est requis.',
            'site_principal.required' => 'Le site principal est requis.',
            'site_principal.sirene.required' => 'La sirène du site principal est requise.',
            'site_principal.sirene.numero_serie.required' => 'Le numéro de série de la sirène du site principal est requis.',
            'site_principal.sirene.numero_serie.exists' => 'Le numéro de série fourni n\'existe pas.',
            'sites_annexe.*.nom.required' => 'Le nom du site annexe est requis.',
            'sites_annexe.*.sirene.numero_serie.required' => 'Le numéro de série de la sirène est requis pour chaque site annexe.',
            'sites_annexe.*.sirene.numero_serie.exists' => 'Le numéro de série fourni n\'existe pas.',
        ];
    }
}
