<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

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
