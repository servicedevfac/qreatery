<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class RestaurantTableSeeder extends Seeder
{
    public function run(): void
    {
        $reposRestaurant = Restaurant::query()->where('slug', 'restaurant-repos')->first();
        if (! $reposRestaurant) {
            return;
        }

        $tables = [
            ['label' => 'Table 1', 'capacity' => 2, 'is_active' => true],
            ['label' => 'Table 2', 'capacity' => 4, 'is_active' => true],
            ['label' => 'Table 3', 'capacity' => 4, 'is_active' => true],
            ['label' => 'Table 4', 'capacity' => 6, 'is_active' => true],
            ['label' => 'Terrasse 1', 'capacity' => 4, 'is_active' => true],
            ['label' => 'Terrasse 2', 'capacity' => 2, 'is_active' => true],
            ['label' => 'Salon VIP', 'capacity' => 8, 'is_active' => true],
        ];

        foreach ($tables as $t) {
            RestaurantTable::query()->updateOrCreate(
                ['restaurant_id' => $reposRestaurant->id, 'label' => $t['label']],
                $t
            );
        }
    }
}
