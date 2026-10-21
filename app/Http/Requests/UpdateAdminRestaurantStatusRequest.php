<?php

namespace App\Http\Requests;

use App\RestaurantStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRestaurantStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_platform_admin === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(RestaurantStatus::class)],
            'reason' => [
                'nullable',
                'string',
                'max:1000',
                Rule::requiredIf($this->input('status') === RestaurantStatus::Suspended->value),
            ],
        ];
    }
}
