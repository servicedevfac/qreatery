<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('managePayments', $this->route('restaurant'));

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
            'amount_minor' => ['required', 'integer', 'min:1'],
            'method' => ['required', Rule::in(['cash', 'card', 'mobile_money', 'other'])],
            'external_reference' => ['nullable', 'string', 'max:255'],
            'idempotency_key' => ['required', 'string', 'min:8', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
