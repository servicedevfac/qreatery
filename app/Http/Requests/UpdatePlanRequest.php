<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_platform_admin === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        $plan = $this->route('plan');

        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:120'],
            'slug' => ['sometimes', 'string', 'max:160', 'alpha_dash', Rule::unique('plans', 'slug')->ignore($plan)],
            'description' => ['nullable', 'string', 'max:3000'],
            'price_minor' => ['sometimes', 'integer', 'min:0'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'billing_period' => ['sometimes', Rule::in(['day', 'week', 'month', 'year'])],
            'billing_interval' => ['sometimes', 'integer', 'min:1', 'max:36'],
            'limits' => ['nullable', 'array'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:100', 'distinct'],
            'terms' => ['nullable', 'string', 'max:10000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
