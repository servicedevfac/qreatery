<?php

namespace Tests\Feature\Api\V1;

use App\Models\QrCode;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class QrCodeControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_generating_new_table_qr_revokes_previous_code_and_public_route_resolves_context(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $restaurantTable = RestaurantTable::factory()->for($restaurant)->create(['label' => 'Table 12']);
        $oldQrCode = QrCode::factory()->for($restaurant)->create([
            'restaurant_table_id' => $restaurantTable->id,
            'type' => 'table',
        ]);

        $response = $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/qr-codes", [
            'type' => 'table',
            'restaurant_table_id' => $restaurantTable->id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.type', 'table')
            ->assertJsonPath('data.table.label', 'Table 12');
        $token = $response->json('data.token');
        $this->assertSame(64, strlen($token));
        $this->assertDatabaseHas('qr_codes', ['id' => $oldQrCode->id, 'status' => 'revoked']);

        $this->getJson("/api/v1/public/qr-codes/{$token}")
            ->assertOk()
            ->assertJsonPath('data.restaurant.slug', $restaurant->slug)
            ->assertJsonPath('data.table.label', 'Table 12');
    }

    public function test_revoked_qr_code_is_not_publicly_resolvable(): void
    {
        $qrCode = QrCode::factory()->create(['status' => 'revoked', 'revoked_at' => now()]);

        $this->getJson("/api/v1/public/qr-codes/{$qrCode->token}")
            ->assertNotFound();
    }

    public function test_takeaway_qr_rejects_table_id_with_422(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $restaurantTable = RestaurantTable::factory()->for($restaurant)->create();

        $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/qr-codes", [
            'type' => 'takeaway',
            'restaurant_table_id' => $restaurantTable->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('restaurant_table_id');
    }
}
