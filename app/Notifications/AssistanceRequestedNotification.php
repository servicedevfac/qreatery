<?php

namespace App\Notifications;

use App\Models\AssistanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AssistanceRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly AssistanceRequest $assistanceRequest) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'assistance.requested',
            'restaurant_id' => $this->assistanceRequest->restaurant_id,
            'assistance_request_id' => $this->assistanceRequest->id,
            'restaurant_table_id' => $this->assistanceRequest->restaurant_table_id,
            'order_id' => $this->assistanceRequest->order_id,
            'type' => $this->assistanceRequest->type,
            'message' => $this->assistanceRequest->message,
        ];
    }
}
