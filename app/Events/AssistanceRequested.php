<?php

namespace App\Events;

use App\Models\AssistanceRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssistanceRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly AssistanceRequest $assistanceRequest) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('restaurants.'.$this->assistanceRequest->restaurant_id)];
    }

    public function broadcastAs(): string
    {
        return 'assistance.requested';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->assistanceRequest->id,
            'type' => $this->assistanceRequest->type,
            'status' => $this->assistanceRequest->status,
            'restaurant_table_id' => $this->assistanceRequest->restaurant_table_id,
            'order_id' => $this->assistanceRequest->order_id,
            'created_at' => $this->assistanceRequest->created_at?->toIso8601String(),
        ];
    }
}
