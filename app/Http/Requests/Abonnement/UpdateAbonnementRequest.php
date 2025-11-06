<?php

namespace App\Http\Requests\Abonnement;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateAbonnementRequest",
 *     title="Update Abonnement Request",
 *     description="Request body for updating an abonnement",
 *     @OA\Property(property="date_debut", type="string", format="date", description="Date de début de l'abonnement"),
 *     @OA\Property(property="date_fin", type="string", format="date", description="Date de fin de l'abonnement"),
 *     @OA\Property(property="montant", type="number", format="float", description="Montant de l'abonnement"),
 *     @OA\Property(property="statut", type="string", enum={"actif", "expire", "en_attente", "suspendu"}, description="Statut de l'abonnement"),
 *     @OA\Property(property="auto_renouvellement", type="boolean", description="Indique si le renouvellement automatique est activé"),
 *     @OA\Property(property="notes", type="string", nullable=true, description="Notes additionnelles"),
 * )
 */
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
