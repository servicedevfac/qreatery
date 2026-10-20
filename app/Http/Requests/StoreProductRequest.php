<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('manageMenu', $this->route('restaurant'));

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $restaurantId = $this->route('restaurant')->id;

        return [
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('restaurant_id', $restaurantId)->whereNull('archived_at')],
            'name' => ['required', 'string', 'min:2', 'max:160'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price_minor' => ['required', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::in(['draft', 'active', 'archived'])],
            'is_available' => ['sometimes', 'boolean'],
            'preparation_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'availability' => ['nullable', 'array'],
            'variants' => ['sometimes', 'array', 'max:50'],
            'variants.*.name' => ['required', 'string', 'max:120', 'distinct'],
            'variants.*.price_delta_minor' => ['sometimes', 'integer'],
            'variants.*.is_default' => ['sometimes', 'boolean'],
            'variants.*.is_active' => ['sometimes', 'boolean'],
            'variants.*.position' => ['sometimes', 'integer', 'min:0'],
            'option_group_ids' => ['sometimes', 'array'],
            'option_group_ids.*' => ['integer', 'distinct', Rule::exists('option_groups', 'id')->where('restaurant_id', $restaurantId)],
        ];
    }
}
