<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::factory()->create([
            'name' => 'Propriétaire Démo',
            'email' => 'owner@qreatery.test',
        ]);

        $restaurant = Restaurant::factory()->create([
            'name' => 'Restaurant Démo',
            'slug' => 'restaurant-demo',
        ]);

        RestaurantMembership::factory()
            ->owner()
            ->for($restaurant)
            ->for($owner)
            ->create();
    }
}
