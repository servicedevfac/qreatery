<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            'order_id' => $this->order_id,
            'original_payment_id' => $this->original_payment_id,
            'type' => $this->type,
            'amount_minor' => $this->amount_minor,
            'currency' => $this->currency,
            'method' => $this->method,
            'external_reference' => $this->external_reference,
            'idempotency_key' => $this->idempotency_key,
            'status' => $this->status,
            'failure_reason' => $this->failure_reason,
            'metadata' => $this->metadata,
            'operator' => new UserResource($this->whenLoaded('operator')),
            'processed_at' => $this->processed_at,
            'created_at' => $this->created_at,
        ];
    }
}
