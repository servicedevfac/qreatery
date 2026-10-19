<?php

namespace App\Http\Requests;

use App\Models\RestaurantMembership;
use App\RestaurantPermission;
use App\RestaurantRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreRestaurantMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('create', [RestaurantMembership::class, $this->route('restaurant')]);

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::enum(RestaurantRole::class)->except([RestaurantRole::Owner])],
            'permissions' => ['sometimes', 'nullable', 'array'],
            'permissions.*' => ['required', 'distinct', Rule::enum(RestaurantPermission::class)],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $restaurant = $this->route('restaurant');
            $email = (string) $this->input('email');

            if ($restaurant->memberships()->whereHas('user', fn ($query) => $query->where('email', $email))->exists()) {
                $validator->errors()->add('email', 'Cette personne appartient déjà au restaurant.');
            }

            $actor = $this->user()->membershipFor($restaurant);
            if ($this->input('role') === RestaurantRole::Admin->value
                && $actor?->role !== RestaurantRole::Owner) {
                $validator->errors()->add('role', 'Seul le propriétaire peut nommer un administrateur.');
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
        ]);
    }
}
