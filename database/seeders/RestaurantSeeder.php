<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionStatusHistory;
use App\RestaurantStatus;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    /**
     * Seeder avec un SEUL restaurant principal actif.
     */
    public function run(): void
    {
        // Supprimer les dépendances et restaurants tiers pour garantir l'unicité
        $staleIds = Restaurant::query()->where('slug', '!=', 'restaurant-repos')->pluck('id');
        if ($staleIds->isNotEmpty()) {
            SubscriptionPayment::whereHas('subscription', fn ($q) => $q->whereIn('restaurant_id', $staleIds))->delete();
            SubscriptionStatusHistory::whereHas('subscription', fn ($q) => $q->whereIn('restaurant_id', $staleIds))->delete();
            Subscription::whereIn('restaurant_id', $staleIds)->delete();
            RestaurantMembership::whereIn('restaurant_id', $staleIds)->delete();
            Restaurant::whereIn('id', $staleIds)->delete();
        }

        Restaurant::query()->updateOrCreate(
            ['slug' => 'restaurant-repos'],
            [
                'name' => 'Repos Restaurant',
                'slug' => 'restaurant-repos',
                'description' => 'Restaurant gastronomique et bistronomique équipé de la suite Qreatery (Commande sur table QR, Caisse tactile POS et Écran Cuisine KDS).',
                'address' => 'Rue des Jardins, Cocody Angré 7e Tranche, Abidjan, Côte d\'Ivoire',
                'phone' => '+225 0700000001',
                'email' => 'contact@restaurantrepos.test',
                'currency' => 'XOF',
                'timezone' => 'Africa/Abidjan',
                'status' => RestaurantStatus::Active,
                'settings' => [
                    'tax_rate' => 0.18,
                    'service_fee_rate' => 0.05,
                    'kds_sound' => true,
                    'auto_accept_orders' => false,
                    'estimated_prep_time_minutes' => 15,
                ],
            ]
        );
    }
}
