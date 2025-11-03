<?php

namespace App\Http\Requests\API\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Ecole; // Assuming Ecole model exists

/**
 * @OA\Schema(
 *     schema="CreateRoleRequest",
 *     title="Create Role Request",
 *     description="Request body for creating a new role",
 *     required={"nom"},
 *     @OA\Property(
 *         property="nom",
 *         type="string",
 *         description="Name of the role"
 *     ),
 *     @OA\Property(
 *         property="permission_ids",
 *         type="array",
 *         description="Array of permission IDs to assign to the role",
 *         @OA\Items(
 *             type="string",
 *             format="uuid"
 *         )
 *     )
 * )
 */
class CreateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'roleable_id' => auth()->check() && auth()->user()->userAccount ? auth()->user()->userAccount->id : null,
            'roleable_type' => auth()->check() && auth()->user()->userAccount ? get_class(auth()->user()->userAccount) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'nom' => [
                'required',
                'string',
                'max:255',
            ],
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['string', 'max:26', 'exists:permissions,id'], // ULID
        ];

        // Add unique rule for 'nom' if the user is an Ecole
        if (auth()->check() && auth()->user()->userAccount instanceof Ecole) {
            $rules['nom'][] = Rule::unique('roles', 'nom')->where(function ($query) {
                return $query->where('roleable_id', auth()->user()->userAccount->id)
                             ->where('roleable_type', get_class(auth()->user()->userAccount));
            });
        } else {
            // For global roles, ensure 'nom' is unique across null roleable_id/type
            $rules['nom'][] = Rule::unique('roles', 'nom')->where(function ($query) {
                return $query->whereNull('roleable_id')->whereNull('roleable_type');
            });
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du rôle est obligatoire.',
            'nom.string' => 'Le nom du rôle doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du rôle ne doit pas dépasser 255 caractères.',
            'nom.unique' => 'Ce nom de rôle existe déjà pour cette entité.',
            'permission_ids.array' => 'Les permissions doivent être un tableau.',
            'permission_ids.*.string' => 'Chaque ID de permission doit être une chaîne de caractères.',
            'permission_ids.*.max' => 'Chaque ID de permission ne doit pas dépasser 26 caractères.',
            'permission_ids.*.exists' => 'Une ou plusieurs permissions spécifiées n\'existent pas.',
        ];
    }
}
