<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use App\RestaurantPermission;
use App\RestaurantRole;
use Illuminate\Database\Seeder;

class RestaurantMembershipSeeder extends Seeder
{
    /**
     * Rattachement de l'ensemble des membres d'équipe au restaurant unique.
     */
    public function run(): void
    {
        $allPermissions = array_map(fn ($p) => $p->value, RestaurantPermission::cases());
        $restaurant = Restaurant::query()->where('slug', 'restaurant-repos')->first();

        if (! $restaurant) {
            return;
        }

        // 1. Propriétaire (Tous les droits de gestion)
        $owner = User::query()->where('email', 'owner@repos.test')->first();
        if ($owner) {
            RestaurantMembership::query()->updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'user_id' => $owner->id],
                ['role' => RestaurantRole::Owner, 'permissions' => $allPermissions, 'is_active' => true]
            );
        }

        // 2. Manager de Salle
        $manager = User::query()->where('email', 'manager@repos.test')->first();
        if ($manager) {
            RestaurantMembership::query()->updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'user_id' => $manager->id],
                [
                    'role' => RestaurantRole::Manager,
                    'permissions' => [
                        RestaurantPermission::RestaurantView->value,
                        RestaurantPermission::MembersView->value,
                        RestaurantPermission::MenuManage->value,
                        RestaurantPermission::TablesManage->value,
                        RestaurantPermission::QrCodesManage->value,
                        RestaurantPermission::OrdersView->value,
                        RestaurantPermission::OrdersManage->value,
                        RestaurantPermission::PaymentsManage->value,
                        RestaurantPermission::ReportsView->value,
                    ],
                    'is_active' => true,
                ]
            );
        }

        // 3. Caissier (Caisse POS)
        $cashier = User::query()->where('email', 'cashier@repos.test')->first();
        if ($cashier) {
            RestaurantMembership::query()->updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'user_id' => $cashier->id],
                [
                    'role' => RestaurantRole::Cashier,
                    'permissions' => [
                        RestaurantPermission::RestaurantView->value,
                        RestaurantPermission::OrdersView->value,
                        RestaurantPermission::OrdersManage->value,
                        RestaurantPermission::PaymentsManage->value,
                    ],
                    'is_active' => true,
                ]
            );
        }

        // 4. Chef Cuisinier (Écran Cuisine KDS)
        $cook = User::query()->where('email', 'cook@repos.test')->first();
        if ($cook) {
            RestaurantMembership::query()->updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'user_id' => $cook->id],
                [
                    'role' => RestaurantRole::Staff,
                    'permissions' => [
                        RestaurantPermission::RestaurantView->value,
                        RestaurantPermission::OrdersView->value,
                        RestaurantPermission::OrdersManage->value,
                    ],
                    'is_active' => true,
                ]
            );
        }

        // 5. Serveur de Salle
        $waiter = User::query()->where('email', 'waiter@repos.test')->first();
        if ($waiter) {
            RestaurantMembership::query()->updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'user_id' => $waiter->id],
                [
                    'role' => RestaurantRole::Staff,
                    'permissions' => [
                        RestaurantPermission::RestaurantView->value,
                        RestaurantPermission::OrdersView->value,
                        RestaurantPermission::OrdersManage->value,
                    ],
                    'is_active' => true,
                ]
            );
        }
    }
}
