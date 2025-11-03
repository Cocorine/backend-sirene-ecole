<?php

namespace App\Http\Requests\Sirene;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="CreateSireneRequest",
 *     title="Create Sirene Request",
 *     description="Request body for creating a new sirene",
 *     required={"modele_id", "date_fabrication"},
 *     @OA\Property(
 *         property="modele_id",
 *         type="string",
 *         format="uuid",
 *         description="ID of the sirene model"
 *     ),
 *     @OA\Property(
 *         property="date_fabrication",
 *         type="string",
 *         format="date",
 *         description="Manufacturing date of the sirene"
 *     ),
 *     @OA\Property(
 *         property="notes",
 *         type="string",
 *         nullable=true,
 *         description="Additional notes for the sirene",
 *         maxLength=1000
 *     )
 * )
 */
class CreateSireneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Seul l'admin peut créer des sirènes (générer numéro de série à l'usine)
        return $this->user() && $this->user()->type === 'ADMIN';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'modele_id' => ['sometimes', 'string', 'exists:modeles_sirene,id'],
            'date_fabrication' => ['required', 'date_format:d/m/Y', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'modele_id.required' => 'Le modèle de sirène est requis.',
            'modele_id.exists' => 'Le modèle de sirène sélectionné n\'existe pas.',
            'date_fabrication.required' => 'La date de fabrication est requise.',
            'date_fabrication.date' => 'La date de fabrication doit être une date valide.',
            'date_fabrication.before_or_equal' => 'La date de fabrication ne peut pas être dans le futur.',
        ];
    }
}
