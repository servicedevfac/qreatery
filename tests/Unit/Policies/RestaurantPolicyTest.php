<?php

namespace Tests\Unit\Policies;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use App\Policies\RestaurantPolicy;
use App\RestaurantRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RestaurantPolicyTest extends TestCase
{
    use LazilyRefreshDatabase;

    public static function rolesAllowedToUpdate(): array
    {
        return [
            'owner' => [RestaurantRole::Owner],
            'admin' => [RestaurantRole::Admin],
        ];
    }

    public static function rolesForbiddenToUpdate(): array
    {
        return [
            'manager' => [RestaurantRole::Manager],
            'staff' => [RestaurantRole::Staff],
            'cashier' => [RestaurantRole::Cashier],
        ];
    }

    #[DataProvider('rolesAllowedToUpdate')]
    public function test_allows_authorized_roles_to_update(RestaurantRole $role): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->for($restaurant)->for($user)->create(['role' => $role]);

        $response = (new RestaurantPolicy)->update($user, $restaurant);

        $this->assertTrue($response->allowed());
    }

    #[DataProvider('rolesForbiddenToUpdate')]
    public function test_denies_operational_roles_from_updating(RestaurantRole $role): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();
        RestaurantMembership::factory()->for($restaurant)->for($user)->create(['role' => $role]);

        $response = (new RestaurantPolicy)->update($user, $restaurant);

        $this->assertTrue($response->denied());
    }

    public function test_hides_restaurant_from_user_without_membership(): void
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create();

        $response = (new RestaurantPolicy)->view($user, $restaurant);

        $this->assertTrue($response->denied());
        $this->assertSame(404, $response->status());
    }
}
