<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class StoreQrCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('manageQrCodes', $this->route('restaurant'));

        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('type') === 'dine_in') {
            $this->merge(['type' => 'table']);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['table', 'takeaway', 'dine_in'])],
            'restaurant_table_id' => [
                'nullable',
                'required_if:type,table',
                'prohibited_if:type,takeaway',
                Rule::exists('restaurant_tables', 'id')
                    ->where('restaurant_id', $this->route('restaurant')->id)
                    ->whereNull('archived_at'),
            ],
        ];
    }
}
