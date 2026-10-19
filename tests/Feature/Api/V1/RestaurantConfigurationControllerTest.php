<?php

namespace Tests\Feature\Api\V1;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RestaurantConfigurationControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_update_configuration_without_erasing_existing_settings(): void
    {
        $owner = User::factory()->create();
        $restaurant = Restaurant::factory()->create(['settings' => ['existing' => true]]);
        RestaurantMembership::factory()->owner()->for($restaurant)->for($owner)->create();

        $this->actingAs($owner)->patchJson("/api/v1/restaurants/{$restaurant->id}/configuration", [
            'order_modes' => ['dine_in', 'takeaway'],
            'estimated_preparation_minutes' => 30,
            'tax_rate' => 18,
        ])->assertOk()->assertJsonPath('data.settings.existing', true)
            ->assertJsonPath('data.settings.estimated_preparation_minutes', 30);
    }

    public function test_manager_without_permission_cannot_update_configuration(): void
    {
        $manager = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->manager()->for($restaurant)->for($manager)->create();

        $this->actingAs($manager)
            ->patchJson("/api/v1/restaurants/{$restaurant->id}/configuration", ['tax_rate' => 10])
            ->assertForbidden();
    }
}
