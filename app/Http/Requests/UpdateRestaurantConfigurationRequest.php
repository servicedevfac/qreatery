<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateRestaurantConfigurationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('update', $this->route('restaurant'));

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
            'order_modes' => ['sometimes', 'array', 'min:1'],
            'order_modes.*' => ['required', 'distinct', 'in:dine_in,takeaway'],
            'estimated_preparation_minutes' => ['sometimes', 'integer', 'min:1', 'max:1440'],
            'order_delay_minutes' => ['sometimes', 'integer', 'min:1', 'max:1440'],
            'cancellation_policy' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'required_customer_fields' => ['sometimes', 'array'],
            'required_customer_fields.*' => ['required', 'distinct', 'in:name,phone'],
            'tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'service_fee_minor' => ['sometimes', 'integer', 'min:0'],
            'notification_channels' => ['sometimes', 'array'],
            'notification_channels.*' => ['required', 'distinct', 'in:in_app,email'],
            'qr_options' => ['sometimes', 'array'],
            'qr_options.allow_table_ordering' => ['sometimes', 'boolean'],
            'qr_options.allow_waiter_call' => ['sometimes', 'boolean'],
            'qr_options.qr_color' => ['sometimes', 'string', 'max:20'],
            'qr_options.display_logo' => ['sometimes', 'boolean'],
        ];
    }
}
