<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price_minor' => $this->price_minor,
            'currency' => $this->currency,
            'billing_period' => $this->billing_period,
            'billing_interval' => $this->billing_interval,
            'limits' => $this->limits,
            'features' => $this->features,
            'terms' => $this->terms,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
