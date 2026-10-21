<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class OrderIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        Gate::authorize('viewOrders', $this->route('restaurant'));

        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['pending', 'accepted', 'preparing', 'ready', 'served', 'completed', 'rejected', 'cancelled'])],
            'mode' => ['nullable', Rule::in(['dine_in', 'takeaway'])],
            'table_id' => ['nullable', 'integer'],
            'number' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            'updated_after' => ['nullable', 'date'],
        ];
    }
}
