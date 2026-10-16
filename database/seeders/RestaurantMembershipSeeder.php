<?php

namespace Database\Seeders;

use App\Models\RestaurantMembership;
use Illuminate\Database\Seeder;

class RestaurantMembershipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RestaurantMembership::factory()->create();
    }
}
