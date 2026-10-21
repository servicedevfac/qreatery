<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionStatusHistoryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_status' => $this->from_status?->value,
            'to_status' => $this->to_status->value,
            'reason' => $this->reason,
            'actor' => new UserResource($this->whenLoaded('actor')),
            'created_at' => $this->created_at,
        ];
    }
}
