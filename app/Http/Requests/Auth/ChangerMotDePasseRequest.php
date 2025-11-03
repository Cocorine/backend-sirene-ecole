<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ChangerMotDePasseRequest",
 *     title="Change Password Request",
 *     description="Request body for changing user password",
 *     required={"nouveau_mot_de_passe", "nouveau_mot_de_passe_confirmation"},
 *     @OA\Property(
 *         property="ancien_mot_de_passe",
 *         type="string",
 *         format="password",
 *         description="User's current password (optional, if admin is changing password)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="nouveau_mot_de_passe",
 *         type="string",
 *         format="password",
 *         description="User's new password",
 *         minLength=8
 *     ),
 *     @OA\Property(
 *         property="nouveau_mot_de_passe_confirmation",
 *         type="string",
 *         format="password",
 *         description="Confirmation of the new password",
 *         minLength=8
 *     )
 * )
 */
class ChangerMotDePasseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // L'utilisateur doit être authentifié (middleware)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ancien_mot_de_passe' => ['sometimes', 'string'],
            'nouveau_mot_de_passe' => ['required', 'string', 'min:8', 'confirmed'],
            'nouveau_mot_de_passe_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nouveau_mot_de_passe.required' => 'Le nouveau mot de passe est requis.',
            'nouveau_mot_de_passe.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'nouveau_mot_de_passe.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'nouveau_mot_de_passe_confirmation.required' => 'La confirmation du mot de passe est requise.',
        ];
    }
}
