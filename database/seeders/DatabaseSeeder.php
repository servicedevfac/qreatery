<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database idempotently for development, testing, and full workflow validation.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PlanSeeder::class,
            RestaurantSeeder::class,
            RestaurantMembershipSeeder::class,
            SubscriptionSeeder::class,
            CategorySeeder::class,
            OptionGroupSeeder::class,
            ProductSeeder::class,
            RestaurantTableSeeder::class,
            QrCodeSeeder::class,
            OrderSeeder::class,
            PaymentSeeder::class,
            AssistanceRequestSeeder::class,
            AuditLogSeeder::class,
        ]);
    }
}
