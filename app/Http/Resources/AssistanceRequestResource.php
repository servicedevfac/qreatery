<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssistanceRequestResource extends JsonResource
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
            'type' => $this->type,
            'status' => $this->status,
            'message' => $this->message,
            'table' => new RestaurantTableResource($this->whenLoaded('restaurantTable')),
            'order_number' => $this->whenLoaded('order', fn () => $this->order?->number),
            'handler' => new UserResource($this->whenLoaded('handler')),
            'handled_at' => $this->handled_at,
            'resolved_at' => $this->resolved_at,
            'created_at' => $this->created_at,
        ];
    }
}
