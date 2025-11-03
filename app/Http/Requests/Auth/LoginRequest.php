<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     title="Login Request",
 *     description="Request body for user login with identifier and password",
 *     required={"identifiant", "mot_de_passe"},
 *     @OA\Property(
 *         property="identifiant",
 *         type="string",
 *         description="User's identifier (e.g., username, email, phone number)",
 *         maxLength=100
 *     ),
 *     @OA\Property(
 *         property="mot_de_passe",
 *         type="string",
 *         format="password",
 *         description="User's password",
 *         minLength=6
 *     )
 * )
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identifiant' => 'required|string|max:100',
            'mot_de_passe' => 'required|string|min:6',
        ];
    }

    public function messages(): array
    {
        return [
            'identifiant.required' => 'L\'identifiant est requis.',
            'mot_de_passe.required' => 'Le mot de passe est requis.',
            'mot_de_passe.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        ];
    }
}
