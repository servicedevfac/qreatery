<?php

namespace Tests\Feature\Api\V1;

use App\Models\AssistanceRequest;
use App\Models\QrCode;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AssistanceRequestControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_client_can_request_help_and_owner_can_resolve_it(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['slug' => 'assistance-demo']);
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $restaurantTable = RestaurantTable::factory()->for($restaurant)->create(['label' => 'Table 4']);
        $qrCode = QrCode::factory()->for($restaurant)->create([
            'restaurant_table_id' => $restaurantTable->id,
            'type' => 'table',
        ]);

        $create = $this->postJson('/api/v1/public/restaurants/assistance-demo/assistance-requests', [
            'qr_token' => $qrCode->token,
            'type' => 'waiter',
            'message' => 'Nous souhaitons commander autre chose.',
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.table.label', 'Table 4');
        $assistanceRequestId = $create->json('data.id');

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $owner->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'restaurant_id' => $restaurant->id,
            'action' => 'assistance.requested',
            'auditable_type' => AssistanceRequest::class,
            'auditable_id' => $assistanceRequestId,
        ]);

        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/assistance-requests")
            ->assertOk()->assertJsonCount(1, 'data');

        $this->actingAs($owner)
            ->patchJson("/api/v1/restaurants/{$restaurant->id}/assistance-requests/{$assistanceRequestId}", ['status' => 'resolved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'resolved')
            ->assertJsonPath('data.handler.id', $owner->id);

        $this->assertDatabaseHas('assistance_requests', [
            'id' => $assistanceRequestId,
            'status' => 'resolved',
            'handled_by' => $owner->id,
        ]);
    }

    public function test_invalid_qr_token_returns_422(): void
    {
        Restaurant::factory()->create(['slug' => 'invalid-token']);

        $this->postJson('/api/v1/public/restaurants/invalid-token/assistance-requests', [
            'qr_token' => str_repeat('x', 64),
            'type' => 'bill',
        ])->assertUnprocessable()->assertJsonValidationErrors('qr_token');
    }

    public function test_assistance_request_from_another_restaurant_is_hidden(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $foreignAssistanceRequest = AssistanceRequest::factory()->create();

        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/assistance-requests/{$foreignAssistanceRequest->id}")
            ->assertNotFound();
    }
}
