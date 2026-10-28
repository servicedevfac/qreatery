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
        $reposRestaurant = Restaurant::query()->where('slug', 'restaurant-repos')->first();
        if (! $reposRestaurant) {
            return;
        }

        $tables = RestaurantTable::query()->where('restaurant_id', $reposRestaurant->id)->get();

        foreach ($tables as $index => $table) {
            // Token SHA-256 déterministe de 64 caractères conforme à la validation size:64
            $token = hash('sha256', "qreatery-repos-{$reposRestaurant->slug}-table-{$table->id}");

            QrCode::query()->updateOrCreate(
                ['token' => $token],
                [
                    'restaurant_id' => $reposRestaurant->id,
                    'restaurant_table_id' => $table->id,
                    'type' => 'table',
                    'status' => 'active',
                ]
            );
        }

        // QR Code pour commandes à emporter (takeaway, 64 caractères)
        $takeawayToken = hash('sha256', "qreatery-repos-{$reposRestaurant->slug}-takeaway");
        QrCode::query()->updateOrCreate(
            ['token' => $takeawayToken],
            [
                'restaurant_id' => $reposRestaurant->id,
                'restaurant_table_id' => null,
                'type' => 'takeaway',
                'status' => 'active',
            ]
        );
    }
}
