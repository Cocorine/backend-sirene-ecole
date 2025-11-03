<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="VerifyOtpRequest",
 *     title="Verify OTP Request",
 *     description="Request body for verifying an OTP and logging in",
 *     required={"telephone", "otp"},
 *     @OA\Property(
 *         property="telephone",
 *         type="string",
 *         description="User's phone number",
 *         pattern="^[0-9]{8,15}$"
 *     ),
 *     @OA\Property(
 *         property="otp",
 *         type="string",
 *         description="OTP code",
 *         pattern="^[0-9]{6}$",
 *         minLength=6,
 *         maxLength=6
 *     )
 * )
 */
class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'telephone' => 'required|string|regex:/^[0-9]{8,15}$/',
            'otp' => 'required|string|size:6|regex:/^[0-9]{6}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'telephone.required' => 'Le numéro de téléphone est requis.',
            'telephone.regex' => 'Le numéro de téléphone doit contenir entre 8 et 15 chiffres.',
            'otp.required' => 'Le code OTP est requis.',
            'otp.size' => 'Le code OTP doit contenir exactement 6 chiffres.',
            'otp.regex' => 'Le code OTP doit contenir uniquement des chiffres.',
        ];
    }
}
