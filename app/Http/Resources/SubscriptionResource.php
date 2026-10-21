<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'restaurant' => new RestaurantResource($this->whenLoaded('restaurant')),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'status' => $this->status->value,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'grace_ends_at' => $this->grace_ends_at,
            'cancelled_at' => $this->cancelled_at,
            'notes' => $this->notes,
            'payments' => SubscriptionPaymentResource::collection($this->whenLoaded('payments')),
            'status_history' => SubscriptionStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
