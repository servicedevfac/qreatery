<?php

namespace Database\Seeders;

use App\Models\Option;
use App\Models\OptionGroup;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class OptionGroupSeeder extends Seeder
{
    public function run(): void
    {
        $reposRestaurant = Restaurant::query()->where('slug', 'restaurant-repos')->first();
        if (! $reposRestaurant) {
            return;
        }

        // 1. Cuisson de la viande
        $groupCuisson = OptionGroup::query()->updateOrCreate(
            ['restaurant_id' => $reposRestaurant->id, 'name' => 'Cuisson de la viande'],
            [
                'minimum_selections' => 1,
                'maximum_selections' => 1,
                'is_active' => true,
                'position' => 1,
            ]
        );

        $cuissons = [
            ['name' => 'Saignant', 'price_delta_minor' => 0, 'position' => 1],
            ['name' => 'À point', 'price_delta_minor' => 0, 'position' => 2],
            ['name' => 'Bien cuit', 'price_delta_minor' => 0, 'position' => 3],
        ];
        foreach ($cuissons as $c) {
            Option::query()->updateOrCreate(
                ['option_group_id' => $groupCuisson->id, 'name' => $c['name']],
                ['price_delta_minor' => $c['price_delta_minor'], 'position' => $c['position'], 'is_active' => true]
            );
        }

        // 2. Sauces d'accompagnement
        $groupSauces = OptionGroup::query()->updateOrCreate(
            ['restaurant_id' => $reposRestaurant->id, 'name' => 'Sauces d\'accompagnement'],
            [
                'minimum_selections' => 0,
                'maximum_selections' => 2,
                'is_active' => true,
                'position' => 2,
            ]
        );

        $sauces = [
            ['name' => 'Sauce Verte Pimentée', 'price_delta_minor' => 0, 'position' => 1],
            ['name' => 'Mayonnaise Maison', 'price_delta_minor' => 0, 'position' => 2],
            ['name' => 'Sauce Barbecue Fumée', 'price_delta_minor' => 50000, 'position' => 3],
        ];
        foreach ($sauces as $s) {
            Option::query()->updateOrCreate(
                ['option_group_id' => $groupSauces->id, 'name' => $s['name']],
                ['price_delta_minor' => $s['price_delta_minor'], 'position' => $s['position'], 'is_active' => true]
            );
        }

        // 3. Suppléments gourmands
        $groupSupplements = OptionGroup::query()->updateOrCreate(
            ['restaurant_id' => $reposRestaurant->id, 'name' => 'Suppléments gourmands'],
            [
                'minimum_selections' => 0,
                'maximum_selections' => 3,
                'is_active' => true,
                'position' => 3,
            ]
        );

        $supplements = [
            ['name' => 'Double Cheddar Fondu', 'price_delta_minor' => 100000, 'position' => 1],
            ['name' => 'Bacon de Bœuf Croustillant', 'price_delta_minor' => 150000, 'position' => 2],
            ['name' => 'Œuf au plat', 'price_delta_minor' => 50000, 'position' => 3],
        ];
        foreach ($supplements as $sup) {
            Option::query()->updateOrCreate(
                ['option_group_id' => $groupSupplements->id, 'name' => $sup['name']],
                ['price_delta_minor' => $sup['price_delta_minor'], 'position' => $sup['position'], 'is_active' => true]
            );
        }
    }
}
