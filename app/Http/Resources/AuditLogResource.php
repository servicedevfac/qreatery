<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'restaurant_id' => $this->restaurant_id,
            'actor' => new UserResource($this->whenLoaded('actor')),
            'auditable' => [
                'type' => $this->auditable_type,
                'id' => $this->auditable_id,
            ],
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
        ];
    }
}
