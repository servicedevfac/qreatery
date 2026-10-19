<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class UpdateRestaurantOpeningHoursRequest extends FormRequest
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
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.day_of_week' => ['required', 'integer', 'between:0,6', 'distinct'],
            'hours.*.is_closed' => ['required', 'boolean'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach ($this->input('hours', []) as $index => $hour) {
                if (($hour['is_closed'] ?? false) === true) {
                    continue;
                }

                if (empty($hour['opens_at']) || empty($hour['closes_at'])) {
                    $validator->errors()->add("hours.$index.opens_at", 'Les heures d’ouverture et de fermeture sont obligatoires.');
                } elseif ($hour['opens_at'] >= $hour['closes_at']) {
                    $validator->errors()->add("hours.$index.closes_at", 'L’heure de fermeture doit être postérieure à l’ouverture.');
                }
            }
        }];
    }
}
