<?php

namespace Database\Seeders;

use App\Models\AssistanceRequest;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssistanceRequestSeeder extends Seeder
{
    public function run(): void
    {
        $reposRestaurant = Restaurant::query()->where('slug', 'restaurant-repos')->first();
        if (! $reposRestaurant) {
            return;
        }

        $waiter = User::query()->where('email', 'waiter@repos.test')->first();

        $table1 = RestaurantTable::query()->where('restaurant_id', $reposRestaurant->id)->where('label', 'Table 1')->first();
        $table2 = RestaurantTable::query()->where('restaurant_id', $reposRestaurant->id)->where('label', 'Table 2')->first();
        $table4 = RestaurantTable::query()->where('restaurant_id', $reposRestaurant->id)->where('label', 'Table 4')->first();

        $order2 = Order::query()->where('number', 'ORD-2026-0002')->first();

        // 1. Appel serveur en attente (Table 2 - PENDING)
        // Types autorisés : 'waiter', 'bill', 'other'
        if ($table2) {
            AssistanceRequest::query()->updateOrCreate(
                [
                    'restaurant_id' => $reposRestaurant->id,
                    'restaurant_table_id' => $table2->id,
                    'type' => 'waiter',
                    'status' => 'pending',
                ],
                [
                    'order_id' => $order2?->id,
                    'message' => 'Besoin d\'une carafe d\'eau fraîche et de serviettes supplémentaires.',
                    'handled_by' => null,
                    'handled_at' => null,
                    'resolved_at' => null,
                    'created_at' => now()->subMinutes(8),
                ]
            );
        }

        // 2. Demande d'addition prise en charge (Table 4 - ACKNOWLEDGED)
        if ($table4) {
            AssistanceRequest::query()->updateOrCreate(
                [
                    'restaurant_id' => $reposRestaurant->id,
                    'restaurant_table_id' => $table4->id,
                    'type' => 'bill',
                ],
                [
                    'status' => 'acknowledged',
                    'order_id' => null,
                    'message' => 'Demande d\'addition pour paiement Mobile Money Wave.',
                    'handled_by' => $waiter?->id,
                    'handled_at' => now()->subMinutes(10),
                    'resolved_at' => null,
                    'created_at' => now()->subMinutes(18),
                ]
            );
        }

        // 3. Demande d'information résolue (Table 1 - RESOLVED)
        if ($table1) {
            AssistanceRequest::query()->updateOrCreate(
                [
                    'restaurant_id' => $reposRestaurant->id,
                    'restaurant_table_id' => $table1->id,
                    'type' => 'other',
                ],
                [
                    'status' => 'resolved',
                    'order_id' => null,
                    'message' => 'Renseignements sur la présence de fruits à coque dans les desserts.',
                    'handled_by' => $waiter?->id,
                    'handled_at' => now()->subMinutes(35),
                    'resolved_at' => now()->subMinutes(30),
                    'created_at' => now()->subMinutes(40),
                ]
            );
        }
    }
}
