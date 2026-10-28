<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $reposRestaurant = Restaurant::query()->where('slug', 'restaurant-repos')->first();
        if (! $reposRestaurant) {
            return;
        }

        $cashier = User::query()->where('email', 'cashier@qreatery.test')->first();

        // 1. Paiement intégral en espèces sur la Commande 3 (12 000 FCFA)
        $order3 = Order::query()->where('number', 'ORD-2026-0003')->first();
        if ($order3) {
            Payment::query()->updateOrCreate(
                ['idempotency_key' => 'pay-ord-0003-cash'],
                [
                    'restaurant_id' => $reposRestaurant->id,
                    'order_id' => $order3->id,
                    'operator_id' => $cashier?->id,
                    'type' => 'charge',
                    'amount_minor' => 1200000, // 12 000 FCFA
                    'currency' => 'XOF',
                    'method' => 'cash',
                    'status' => 'succeeded',
                    'external_reference' => 'CASH-REC-0003',
                    'processed_at' => now()->subMinutes(20),
                    'metadata' => [
                        'amount_tendered_minor' => 1500000,
                        'change_returned_minor' => 300000,
                    ],
                ]
            );
        }

        // 2. Paiement partiel sur la Commande 5 (5 000 FCFA payés sur 12 000 FCFA, reste 7 000 FCFA)
        $order5 = Order::query()->where('number', 'ORD-2026-0005')->first();
        if ($order5) {
            Payment::query()->updateOrCreate(
                ['idempotency_key' => 'pay-ord-0005-partial-1'],
                [
                    'restaurant_id' => $reposRestaurant->id,
                    'order_id' => $order5->id,
                    'operator_id' => $cashier?->id,
                    'type' => 'charge',
                    'amount_minor' => 500000, // 5 000 FCFA
                    'currency' => 'XOF',
                    'method' => 'mobile_money',
                    'status' => 'succeeded',
                    'external_reference' => 'MM-WAVE-0005-1',
                    'processed_at' => now()->subMinutes(15),
                    'metadata' => [
                        'operator' => 'Wave',
                        'note' => 'Paiement partiel premier convive',
                    ],
                ]
            );
        }

        // 3. Paiement par carte bancaire sur la Commande 6 (11 000 FCFA)
        $order6 = Order::query()->where('number', 'ORD-2026-0006')->first();
        if ($order6) {
            Payment::query()->updateOrCreate(
                ['idempotency_key' => 'pay-ord-0006-card'],
                [
                    'restaurant_id' => $reposRestaurant->id,
                    'order_id' => $order6->id,
                    'operator_id' => $cashier?->id,
                    'type' => 'charge',
                    'amount_minor' => 1100000, // 11 000 FCFA
                    'currency' => 'XOF',
                    'method' => 'card',
                    'status' => 'succeeded',
                    'external_reference' => 'CB-VISA-0006',
                    'processed_at' => now()->subMinutes(55),
                    'metadata' => [
                        'card_brand' => 'Visa',
                        'last4' => '4242',
                    ],
                ]
            );
        }

        // 4. Paiement en espèces sur la Commande à emporter 7 (13 000 FCFA)
        $order7 = Order::query()->where('number', 'ORD-2026-0007')->first();
        if ($order7) {
            Payment::query()->updateOrCreate(
                ['idempotency_key' => 'pay-ord-0007-cash'],
                [
                    'restaurant_id' => $reposRestaurant->id,
                    'order_id' => $order7->id,
                    'operator_id' => $cashier?->id,
                    'type' => 'charge',
                    'amount_minor' => 1300000, // 13 000 FCFA
                    'currency' => 'XOF',
                    'method' => 'cash',
                    'status' => 'succeeded',
                    'external_reference' => 'CASH-REC-0007',
                    'processed_at' => now()->subHours(2),
                    'metadata' => [
                        'amount_tendered_minor' => 1500000,
                        'change_returned_minor' => 200000,
                    ],
                ]
            );
        }
    }
}
