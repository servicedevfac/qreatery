<?php

namespace App\Http\Requests;

use App\RestaurantPermission;
use App\RestaurantRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateRestaurantMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('update', $this->route('membership'));

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
            'role' => ['sometimes', Rule::enum(RestaurantRole::class)->except([RestaurantRole::Owner])],
            'permissions' => ['sometimes', 'nullable', 'array'],
            'permissions.*' => ['required', 'distinct', Rule::enum(RestaurantPermission::class)],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $restaurant = $this->route('restaurant');
            $actor = $this->user()->membershipFor($restaurant);

            if ($this->input('role') === RestaurantRole::Admin->value
                && $actor?->role !== RestaurantRole::Owner) {
                $validator->errors()->add('role', 'Seul le propriétaire peut nommer un administrateur.');
            }
        }];
    }
}
