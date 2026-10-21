<?php

namespace App\Http\Requests;

use App\SubscriptionPaymentStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_platform_admin === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'amount_minor' => ['required', 'integer', 'min:1'],
            'currency' => ['required', 'string', 'size:3'],
            'method' => ['required', Rule::in(['cash', 'bank_transfer', 'mobile_money', 'card', 'other'])],
            'transaction_reference' => ['nullable', 'string', 'max:191'],
            'idempotency_key' => ['required', 'string', 'min:8', 'max:100'],
            'status' => ['required', Rule::enum(SubscriptionPaymentStatus::class)],
            'failure_reason' => ['nullable', 'string', 'max:1000', 'required_if:status,failed'],
            'metadata' => ['nullable', 'array'],
            'paid_at' => ['nullable', 'date'],
        ];
    }
}
