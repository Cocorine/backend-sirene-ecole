<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="RequestOtpRequest",
 *     title="Request OTP Request",
 *     description="Request body for requesting an OTP",
 *     required={"telephone"},
 *     @OA\Property(
 *         property="telephone",
 *         type="string",
 *         description="User's phone number",
 *         pattern="^[0-9]{8,15}$"
 *     )
 * )
 */
class RequestOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'telephone' => 'required|string|regex:/^[0-9]{8,15}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'telephone.required' => 'Le numéro de téléphone est requis.',
            'telephone.regex' => 'Le numéro de téléphone doit contenir entre 8 et 15 chiffres.',
        ];
    }
}
