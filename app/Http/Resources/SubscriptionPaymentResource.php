<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPaymentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subscription_id' => $this->subscription_id,
            'amount_minor' => $this->amount_minor,
            'currency' => $this->currency,
            'method' => $this->method,
            'transaction_reference' => $this->transaction_reference,
            'idempotency_key' => $this->idempotency_key,
            'status' => $this->status->value,
            'proof_path' => $this->proof_path,
            'failure_reason' => $this->failure_reason,
            'metadata' => $this->metadata,
            'recorder' => new UserResource($this->whenLoaded('recorder')),
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
        ];
    }
}
