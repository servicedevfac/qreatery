<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QrCodeResource extends JsonResource
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
            'token' => $this->token,
            'public_url' => route('api.v1.public.qr-codes.show', ['token' => $this->token]),
            'restaurant' => [
                'name' => $this->whenLoaded('restaurant', fn () => $this->restaurant->name),
                'slug' => $this->whenLoaded('restaurant', fn () => $this->restaurant->slug),
            ],
            'table' => new RestaurantTableResource($this->whenLoaded('restaurantTable')),
            'revoked_at' => $this->revoked_at,
            'created_at' => $this->created_at,
        ];
    }
}
