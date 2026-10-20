<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'variant_name' => $this->variant_name,
            'unit_price_minor' => $this->unit_price_minor,
            'quantity' => $this->quantity,
            'line_total_minor' => $this->line_total_minor,
            'instructions' => $this->instructions,
            'options' => OrderItemOptionResource::collection($this->whenLoaded('options')),
        ];
    }
}
