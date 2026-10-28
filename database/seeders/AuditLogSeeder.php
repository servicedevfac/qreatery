<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@qreatery.test')->first();
        $owner = User::query()->where('email', 'owner@qreatery.test')->first();
        $manager = User::query()->where('email', 'manager@qreatery.test')->first();

        $reposRestaurant = Restaurant::query()->where('slug', 'restaurant-repos')->first();
        $pauseRestaurant = Restaurant::query()->where('slug', 'la-pause-fraicheur')->first();
        $planPro = Plan::query()->where('slug', 'pro')->first();

        $logs = [
            [
                'actor_id' => $admin?->id,
                'restaurant_id' => null,
                'action' => 'auth.login',
                'auditable_type' => User::class,
                'auditable_id' => $admin?->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130.0.0.0',
                'metadata' => ['guard' => 'sanctum', 'platform' => 'Super-Admin'],
                'created_at' => now()->subDays(1),
            ],
            [
                'actor_id' => $admin?->id,
                'restaurant_id' => null,
                'action' => 'plan.create',
                'auditable_type' => Plan::class,
                'auditable_id' => $planPro?->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Qreatery Super-Admin Console v1',
                'metadata' => ['slug' => 'pro', 'name' => 'Formule Pro', 'price_minor' => 3500000],
                'created_at' => now()->subDays(28),
            ],
            [
                'actor_id' => $admin?->id,
                'restaurant_id' => $pauseRestaurant?->id,
                'action' => 'restaurant.status_update',
                'auditable_type' => Restaurant::class,
                'auditable_id' => $pauseRestaurant?->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Qreatery Super-Admin Console v1',
                'metadata' => [
                    'from' => 'active',
                    'to' => 'suspended',
                    'reason' => 'Facture impayée après dépassement de la période de grâce de 7 jours.',
                ],
                'created_at' => now()->subDays(12),
            ],
            [
                'actor_id' => $owner?->id,
                'restaurant_id' => $reposRestaurant?->id,
                'action' => 'menu.category_create',
                'auditable_type' => Restaurant::class,
                'auditable_id' => $reposRestaurant?->id,
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'metadata' => ['category' => 'Burgers & Street Food'],
                'created_at' => now()->subDays(20),
            ],
            [
                'actor_id' => $manager?->id,
                'restaurant_id' => $reposRestaurant?->id,
                'action' => 'order.cancel',
                'auditable_type' => Restaurant::class,
                'auditable_id' => $reposRestaurant?->id,
                'ip_address' => '192.168.1.102',
                'user_agent' => 'Qreatery POS Tablet Agent',
                'metadata' => [
                    'order_number' => 'ORD-2026-0008',
                    'reason' => 'Client contraint de partir suite à une urgence',
                ],
                'created_at' => now()->subHours(2),
            ],
        ];

        foreach ($logs as $logData) {
            AuditLog::query()->firstOrCreate(
                [
                    'action' => $logData['action'],
                    'created_at' => $logData['created_at'],
                ],
                $logData
            );
        }
    }
}
