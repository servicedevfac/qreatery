<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreOptionGroupRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('option_groups')->where('restaurant_id', $this->route('restaurant')->id)],
            'minimum_selections' => ['sometimes', 'integer', 'min:0', 'lte:maximum_selections'],
            'maximum_selections' => ['required', 'integer', 'min:1', 'max:50'],
            'is_active' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'options' => ['sometimes', 'array', 'max:100'],
            'options.*.name' => ['required', 'string', 'max:120', 'distinct'],
            'options.*.price_delta_minor' => ['sometimes', 'integer'],
            'options.*.is_active' => ['sometimes', 'boolean'],
            'options.*.position' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
