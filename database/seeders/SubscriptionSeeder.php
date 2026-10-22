<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionStatusHistory;
use App\Models\User;
use App\SubscriptionPaymentStatus;
use App\SubscriptionStatus;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Abonnement et historique de facturation pour le restaurant unique.
     */
    public function run(): void
    {
        $planPro = Plan::query()->where('slug', 'pro')->first();
        $admin = User::query()->where('email', 'admin@qreatery.test')->first();
        $restaurant = Restaurant::query()->where('slug', 'restaurant-demo')->first();

        if ($restaurant && $planPro) {
            $subDemo = Subscription::query()->updateOrCreate(
                ['restaurant_id' => $restaurant->id],
                [
                    'plan_id' => $planPro->id,
                    'status' => SubscriptionStatus::Active,
                    'starts_at' => now()->subDays(30),
                    'ends_at' => now()->addDays(335),
                    'grace_ends_at' => null,
                    'notes' => 'Abonnement officiel annuel avec support prioritaire.',
                ]
            );

            // Historique de statut
            SubscriptionStatusHistory::query()->firstOrCreate(
                ['subscription_id' => $subDemo->id, 'to_status' => SubscriptionStatus::Active],
                ['actor_id' => $admin?->id, 'from_status' => SubscriptionStatus::Trialing, 'reason' => 'Paiement annuel validé']
            );

            // Paiements d'abonnement
            SubscriptionPayment::query()->updateOrCreate(
                ['idempotency_key' => 'sub-pay-demo-001'],
                [
                    'subscription_id' => $subDemo->id,
                    'recorded_by' => $admin?->id,
                    'amount_minor' => $planPro->price_minor,
                    'currency' => $planPro->currency,
                    'method' => 'bank_transfer',
                    'transaction_reference' => 'TX-SUB-DEMO-001',
                    'status' => SubscriptionPaymentStatus::Succeeded,
                    'paid_at' => now()->subDays(30),
                    'metadata' => ['invoice_number' => 'FAC-2026-0001'],
                ]
            );

            SubscriptionPayment::query()->updateOrCreate(
                ['idempotency_key' => 'sub-pay-demo-002'],
                [
                    'subscription_id' => $subDemo->id,
                    'recorded_by' => $admin?->id,
                    'amount_minor' => $planPro->price_minor,
                    'currency' => $planPro->currency,
                    'method' => 'mobile_money',
                    'transaction_reference' => 'TX-SUB-DEMO-002',
                    'status' => SubscriptionPaymentStatus::Succeeded,
                    'paid_at' => now()->subDays(2),
                    'metadata' => ['operator' => 'Wave'],
                ]
            );
        }
    }
}
