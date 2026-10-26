<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicAssistanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'qr_token' => ['nullable', 'required_without:tracking_token', 'string', 'max:64'],
            'tracking_token' => ['nullable', 'required_without:qr_token', 'string', 'max:64'],
            'type' => ['required', Rule::in(['waiter', 'bill', 'other'])],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
