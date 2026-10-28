<?php

namespace App\Http\Requests;

use App\SubscriptionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_platform_admin === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'restaurant_id' => [
                'sometimes',
                Rule::exists('restaurants', 'id'),
            ],
            'plan_id' => [
                'required',
                Rule::exists('plans', 'id')->where('is_active', true),
            ],
            'status' => ['sometimes', Rule::enum(SubscriptionStatus::class)],
            'starts_at' => ['sometimes', 'date'],
            'grace_days' => ['sometimes', 'integer', 'min:0', 'max:90'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];
    }
}
