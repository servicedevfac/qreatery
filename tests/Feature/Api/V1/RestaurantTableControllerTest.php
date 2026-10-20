<?php

namespace Tests\Feature\Api\V1;

use App\Models\QrCode;
use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RestaurantTableControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_create_update_and_archive_table(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();

        $create = $this->actingAs($owner)->postJson("/api/v1/restaurants/{$restaurant->id}/tables", [
            'label' => 'Terrasse 1',
            'capacity' => 4,
        ]);

        $create->assertCreated()->assertJsonPath('data.label', 'Terrasse 1');
        $tableId = $create->json('data.id');
        $this->actingAs($owner)
            ->patchJson("/api/v1/restaurants/{$restaurant->id}/tables/{$tableId}", ['capacity' => 6])
            ->assertOk()->assertJsonPath('data.capacity', 6);

        QrCode::factory()->for($restaurant)->create(['restaurant_table_id' => $tableId, 'type' => 'table']);
        $this->actingAs($owner)
            ->deleteJson("/api/v1/restaurants/{$restaurant->id}/tables/{$tableId}")
            ->assertNoContent();

        $this->assertDatabaseHas('restaurant_tables', ['id' => $tableId, 'is_active' => false]);
        $this->assertDatabaseHas('qr_codes', ['restaurant_table_id' => $tableId, 'status' => 'revoked']);
    }

    public function test_table_from_another_restaurant_is_hidden(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();
        $foreignTable = RestaurantTable::factory()->create();

        $this->actingAs($owner)
            ->getJson("/api/v1/restaurants/{$restaurant->id}/tables/{$foreignTable->id}")
            ->assertNotFound();
    }
}
