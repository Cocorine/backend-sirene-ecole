<?php

namespace App\Http\Requests\CalendrierScolaire;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateCalendrierScolaireRequest",
 *     title="Update School Calendar Request",
 *     description="Request body for updating an existing school calendar entry",
 *     @OA\Property(
 *         property="pays_id",
 *         type="string",
 *         format="uuid",
 *         nullable=true,
 *         description="ID of the country this calendar belongs to"
 *     ),
 *     @OA\Property(
 *         property="annee_scolaire",
 *         type="string",
 *         nullable=true,
 *         description="Academic year (e.g., '2023-2024')"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         nullable=true,
 *         description="Description of the school calendar"
 *     ),
 *     @OA\Property(
 *         property="date_rentree",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         description="Start date of the academic year"
 *     ),
 *     @OA\Property(
 *         property="date_fin_annee",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         description="End date of the academic year"
 *     ),
 *     @OA\Property(
 *         property="periodes_vacances",
 *         type="array",
 *         nullable=true,
 *         description="Array of vacation periods",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="nom", type="string", example="Vacances de Noël"),
 *             @OA\Property(property="date_debut", type="string", format="date", example="2023-12-20"),
 *             @OA\Property(property="date_fin", type="string", format="date", example="2024-01-05")
 *         )})
 *     ),
 *     @OA\Property(
 *         property="jours_feries_defaut",
 *         type="array",
 *         nullable=true,
 *         description="Array of default public holidays",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="nom", type="string", example="Jour de l'An"),
 *             @OA\Property(property="date", type="string", format="date", example="2024-01-01")
 *         )
 *     ),
 *     @OA\Property(
 *         property="actif",
 *         type="boolean",
 *         nullable=true,
 *         description="Is this calendar active?"
 *     )
 * )
 */
class UpdateCalendrierScolaireRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust authorization logic as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'pays_id' => ['sometimes', 'string', 'exists:pays,id'],
            'annee_scolaire' => ['sometimes', 'string', 'max:20'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'date_rentree' => ['sometimes', 'date'],
            'date_fin_annee' => ['sometimes', 'date', 'after:date_rentree'],
            'periodes_vacances' => ['sometimes', 'nullable', 'array'],
            'periodes_vacances.*.nom' => ['required_with:periodes_vacances', 'string', 'max:100'],
            'periodes_vacances.*.date_debut' => ['required_with:periodes_vacances', 'date'],
            'periodes_vacances.*.date_fin' => ['required_with:periodes_vacances', 'date', 'after_or_equal:periodes_vacances.*.date_debut'],
            'jours_feries_defaut' => ['sometimes', 'nullable', 'array'],
            'jours_feries_defaut.*.nom' => ['required_with:jours_feries_defaut', 'string', 'max:100'],
            'jours_feries_defaut.*.date' => ['required_with:jours_feries_defaut', 'date'],
            'actif' => ['sometimes', 'boolean'],
        ];
    }
}