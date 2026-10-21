<?php

namespace App\Http\Requests;

use App\SubscriptionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_platform_admin === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
            'reason' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf(in_array($this->input('status'), ['suspended', 'cancelled'], true)),
            ],
        ];
    }
}
