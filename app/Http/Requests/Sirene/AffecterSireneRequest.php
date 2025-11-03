<?php

namespace App\Http\Requests\Sirene;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="AffecterSireneRequest",
 *     title="Affect Sirene Request",
 *     description="Request body for affecting a sirene to a site",
 *     required={"site_id"},
 *     @OA\Property(
 *         property="site_id",
 *         type="string",
 *         format="uuid",
 *         description="ID of the site to affect the sirene to"
 *     )
 * )
 */
class AffecterSireneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Admin ou technicien peuvent affecter
        return $this->user() && in_array($this->user()->type, ['ADMIN', 'TECHNICIEN']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'site_id' => ['required', 'string', 'exists:sites,id'],
            'ecole_id' => ['required', 'string', 'exists:ecoles,id'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'site_id.required' => 'Le site est requis.',
            'site_id.exists' => 'Le site sélectionné n\'existe pas.',
            'ecole_id.required' => "L'ecole est requis.",
            'ecole_id.exists' => "L'ecole sélectionné n\'existe pas.",
        ];
    }
}
