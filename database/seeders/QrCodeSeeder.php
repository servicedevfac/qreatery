<?php

namespace Database\Seeders;

use App\Models\QrCode;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Illuminate\Database\Seeder;

class QrCodeSeeder extends Seeder
{
    public function run(): void
    {
        $demoRestaurant = Restaurant::query()->where('slug', 'restaurant-demo')->first();
        if (! $demoRestaurant) {
            return;
        }

        $tables = RestaurantTable::query()->where('restaurant_id', $demoRestaurant->id)->get();

        foreach ($tables as $index => $table) {
            // Token SHA-256 déterministe de 64 caractères conforme à la validation size:64
            $token = hash('sha256', "qreatery-demo-{$demoRestaurant->slug}-table-{$table->id}");

            QrCode::query()->updateOrCreate(
                ['token' => $token],
                [
                    'restaurant_id' => $demoRestaurant->id,
                    'restaurant_table_id' => $table->id,
                    'type' => 'table',
                    'status' => 'active',
                ]
            );
        }

        // QR Code pour commandes à emporter (takeaway, 64 caractères)
        $takeawayToken = hash('sha256', "qreatery-demo-{$demoRestaurant->slug}-takeaway");
        QrCode::query()->updateOrCreate(
            ['token' => $takeawayToken],
            [
                'restaurant_id' => $demoRestaurant->id,
                'restaurant_table_id' => null,
                'type' => 'takeaway',
                'status' => 'active',
            ]
        );
    }
}
