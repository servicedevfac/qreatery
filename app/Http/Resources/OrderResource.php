<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'number' => $this->number,
            'tracking_token' => $this->tracking_token,
            'tracking_url' => route('api.v1.public.orders.show', ['token' => $this->tracking_token]),
            'mode' => $this->mode,
            'preparation_status' => $this->preparation_status,
            'payment_status' => $this->payment_status,
            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'notes' => $this->notes,
            'subtotal_minor' => $this->subtotal_minor,
            'tax_minor' => $this->tax_minor,
            'service_fee_minor' => $this->service_fee_minor,
            'total_minor' => $this->total_minor,
            'currency' => $this->currency,
            'table' => new RestaurantTableResource($this->whenLoaded('restaurantTable')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'status_history' => OrderStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'accepted_at' => $this->accepted_at,
            'ready_at' => $this->ready_at,
            'completed_at' => $this->completed_at,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
