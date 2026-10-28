<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminRestaurantRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à créer un restaurant via la Super-Administration.
     */
    public function authorize(): bool
    {
        return $this->user()?->is_platform_admin === true;
    }

    /**
     * Règles de validation pour la création d'un établissement par le Super-Admin.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'slug' => ['nullable', 'string', 'min:2', 'max:160', 'alpha_dash', 'unique:restaurants,slug'],
            'description' => ['nullable', 'string', 'max:2000'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
            'timezone' => ['nullable', 'string', 'timezone'],
            'status' => ['nullable', 'string', Rule::in(['active', 'draft', 'suspended', 'archived'])],
            'owner_name' => ['nullable', 'string', 'max:120'],
            'owner_email' => ['nullable', 'email', 'max:255'],
            'owner_password' => ['nullable', 'string', 'min:6'],
            'plan_id' => ['nullable', 'integer', 'exists:plans,id'],
        ];
    }

    /**
     * Messages d'erreur personnalisés en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du restaurant est obligatoire.',
            'name.min' => 'Le nom du restaurant doit comporter au moins 2 caractères.',
            'slug.unique' => 'Cet identifiant / slug est déjà utilisé par un autre restaurant.',
            'slug.alpha_dash' => 'Le slug ne doit comporter que des lettres, chiffres, tirets et underscores.',
            'email.email' => 'L\'adresse email de contact n\'est pas valide.',
            'owner_email.email' => 'L\'adresse email du propriétaire n\'est pas valide.',
            'owner_password.min' => 'Le mot de passe du propriétaire doit comporter au moins 6 caractères.',
            'plan_id.exists' => 'Le plan d\'abonnement sélectionné n\'existe pas.',
        ];
    }
}
