<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UpdateRestaurantTableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        Gate::authorize('manageTables', $this->route('restaurant'));

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
            'label' => [
                'sometimes', 'required', 'string', 'max:80',
                Rule::unique('restaurant_tables')->where('restaurant_id', $this->route('restaurant')->id)->ignore($this->route('restaurantTable')),
            ],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
