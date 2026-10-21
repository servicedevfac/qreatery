<?php

namespace Tests\Feature\Api\V1;

use App\Models\AssistanceRequest;
use App\Models\User;
use App\Notifications\AssistanceRequestedNotification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_user_can_list_and_mark_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $assistanceRequest = AssistanceRequest::factory()->create();
        $user->notify(new AssistanceRequestedNotification($assistanceRequest));
        $notification = $user->notifications()->firstOrFail();

        $this->actingAs($user)
            ->getJson('/api/v1/notifications?unread_only=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.data.event', 'assistance.requested');

        $response = $this->actingAs($user)
            ->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertOk();
        $this->assertNotNull($response->json('read_at'));
        $this->assertNotNull($notification->refresh()->read_at);

        $this->actingAs($user)
            ->getJson('/api/v1/notifications?unread_only=1')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherUser->notify(new AssistanceRequestedNotification(AssistanceRequest::factory()->create()));
        $notification = $otherUser->notifications()->firstOrFail();

        $this->actingAs($user)
            ->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertNotFound();
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new AssistanceRequestedNotification(AssistanceRequest::factory()->create()));
        $user->notify(new AssistanceRequestedNotification(AssistanceRequest::factory()->create()));

        $this->actingAs($user)
            ->patchJson('/api/v1/notifications/read-all')
            ->assertNoContent();

        $this->assertSame(0, $user->unreadNotifications()->count());
    }
}
