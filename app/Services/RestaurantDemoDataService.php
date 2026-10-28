<?php

namespace App\Services;

use App\Actions\BuildRestaurantReportAction;
use App\Models\Restaurant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RestaurantDemoDataService
{
    /**
     * Détermine si la session actuelle est en mode démonstration / invité.
     */
    public function isDemoSession(?User $user, ?Restaurant $restaurant): bool
    {
        if (! $user) {
            return true;
        }

        if ($user->is_platform_admin) {
            return false;
        }

        if (! $restaurant) {
            return true;
        }

        return ! $user->restaurantMemberships()
            ->where('restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Retourne la liste des établissements autorisés pour le sélecteur.
     * En session invité, seuls les établissements autorisés sont exposés pour empêcher l'espionnage.
     */
    public function getAccessibleRestaurants(?User $user, ?Restaurant $currentRestaurant): Collection
    {
        if (! $user) {
            return collect([
                (object) [
                    'id' => $currentRestaurant?->id ?? 1,
                    'name' => ($currentRestaurant?->name ?? 'Restaurant').' (DÉMO)',
                    'slug' => $currentRestaurant?->slug ?? 'restaurant-repos',
                    'status' => (object) ['value' => 'active'],
                ],
            ]);
        }

        if ($user->is_platform_admin) {
            return Restaurant::query()->orderBy('name')->get();
        }

        return $user->restaurantMemberships()
            ->where('is_active', true)
            ->with('restaurant')
            ->get()
            ->pluck('restaurant')
            ->filter()
            ->values();
    }

    /**
     * Fournit les données simulées du tableau de bord.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(Restaurant $restaurant, bool $isDemo): array
    {
        if (! $isDemo) {
            $ordersToday = $restaurant->orders()->with(['restaurantTable'])->whereDate('created_at', today())->get();
            $activeOrders = $ordersToday->whereIn('preparation_status', ['pending', 'accepted', 'preparing', 'ready']);

            $chargesTodayMinor = (int) $restaurant->payments()
                ->where('status', 'succeeded')
                ->where('type', 'charge')
                ->whereDate('processed_at', today())
                ->sum('amount_minor');

            $tables = $restaurant->restaurantTables()->where('is_active', true)->whereNull('archived_at')->get();
            $occupiedTables = $activeOrders->whereNotNull('restaurant_table_id')->pluck('restaurant_table_id')->unique()->count();

            $pendingAssistance = $restaurant->assistanceRequests()->where('status', 'pending')->with('restaurantTable')->latest()->take(5)->get();
            $lateOrders = $activeOrders->filter(function ($order) {
                return $order->created_at && $order->created_at->diffInMinutes(now()) >= 45;
            });

            $recentOrders = $restaurant->orders()->with(['restaurantTable', 'items'])->latest()->take(6)->get();

            return [
                'chargesTodayMinor' => $chargesTodayMinor,
                'totalOrdersTodayCount' => $ordersToday->count(),
                'activeOrdersCount' => $activeOrders->count(),
                'totalTables' => $tables->count(),
                'occupiedTables' => $occupiedTables,
                'averageBasketMinor' => $ordersToday->isNotEmpty() ? (int) round($chargesTodayMinor / max(1, $ordersToday->count())) : 0,
                'pendingAssistance' => $pendingAssistance,
                'lateOrders' => $lateOrders,
                'recentOrders' => $recentOrders,
                'tables' => $tables,
                'occupiedTableIds' => $activeOrders->whereNotNull('restaurant_table_id')->pluck('restaurant_table_id')->unique()->toArray(),
            ];
        }

        // --- Données 100% factices et anonymisées pour la Session Invité ---
        $demoTables = $this->getDemoTablesCollection();
        $demoRecentOrders = $this->getDemoRecentOrdersCollection();

        return [
            'chargesTodayMinor' => 4850000, // 48 500 XOF
            'totalOrdersTodayCount' => 5,
            'activeOrdersCount' => 2,
            'totalTables' => $demoTables->count(),
            'occupiedTables' => 2,
            'averageBasketMinor' => 970000, // 9 700 XOF
            'pendingAssistance' => collect([]),
            'lateOrders' => collect([]),
            'recentOrders' => $demoRecentOrders,
            'tables' => $demoTables,
            'occupiedTableIds' => [2, 4],
        ];
    }

    /**
     * Fournit la liste des commandes (réelles ou factices).
     */
    public function getOrdersData(Restaurant $restaurant, bool $isDemo): Collection
    {
        if (! $isDemo) {
            return $restaurant->orders()
                ->with(['restaurantTable', 'items.options', 'payments', 'statusHistories'])
                ->latest('id')
                ->get();
        }

        return $this->getDemoOrdersListCollection();
    }

    /**
     * Fournit les données du rapport d'activité (réelles ou factices).
     *
     * @return array<string, mixed>
     */
    public function getReportsData(Restaurant $restaurant, bool $isDemo): array
    {
        if (! $isDemo) {
            $action = app(BuildRestaurantReportAction::class);

            return $action->handle($restaurant, []);
        }

        return [
            'period' => [
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->toDateString(),
            ],
            'payments' => [
                'charged_minor' => 34500000,
                'refunded_minor' => 1500000,
                'net_collected_minor' => 33000000,
                'currency' => $restaurant->currency ?? 'XOF',
                'by_method' => [
                    'cash' => 18000000,
                    'mobile_money' => 12000000,
                    'card' => 4500000,
                ],
            ],
            'orders' => [
                'total' => 42,
                'completed' => 38,
                'cancelled' => 4,
                'average_basket_minor' => 820000,
                'by_mode' => [
                    'dine_in' => 28,
                    'takeaway' => 14,
                ],
            ],
            'top_products' => [
                [
                    'product_name' => 'Burger Qreatery Signature',
                    'quantity' => 24,
                    'revenue_minor' => 15600000,
                ],
                [
                    'product_name' => 'Poulet Braisé & Alloco',
                    'quantity' => 19,
                    'revenue_minor' => 9500000,
                ],
                [
                    'product_name' => 'Cocktail Passion Démo',
                    'quantity' => 31,
                    'revenue_minor' => 7750000,
                ],
            ],
        ];
    }

    /**
     * Fournit la liste des membres et données d'équipe (réelles ou factices).
     *
     * @return array<string, mixed>
     */
    public function getSettingsData(Restaurant $restaurant, bool $isDemo): array
    {
        if (! $isDemo) {
            return [
                'memberships' => $restaurant->memberships()->with('user')->get(),
                'openingHours' => $restaurant->openingHours()->orderBy('day_of_week')->get(),
                'subscription' => $restaurant->subscriptions()->latest('id')->with('plan')->first(),
                'displayEmail' => $restaurant->email,
                'displayPhone' => $restaurant->phone,
                'displayAddress' => $restaurant->address,
            ];
        }

        return [
            'memberships' => collect([
                (object) [
                    'id' => 901,
                    'is_active' => true,
                    'role' => (object) ['value' => 'owner'],
                    'user' => (object) [
                        'name' => 'Propriétaire Démo',
                        'email' => 'proprietaire-demo@qreatery.test',
                    ],
                ],
                (object) [
                    'id' => 902,
                    'is_active' => true,
                    'role' => (object) ['value' => 'manager'],
                    'user' => (object) [
                        'name' => 'Gérant Démo',
                        'email' => 'gerant-demo@qreatery.test',
                    ],
                ],
                (object) [
                    'id' => 903,
                    'is_active' => true,
                    'role' => (object) ['value' => 'cashier'],
                    'user' => (object) [
                        'name' => 'Caissier Démo',
                        'email' => 'caisse-demo@qreatery.test',
                    ],
                ],
                (object) [
                    'id' => 904,
                    'is_active' => true,
                    'role' => (object) ['value' => 'kitchen'],
                    'user' => (object) [
                        'name' => 'Chef Cuisine Démo',
                        'email' => 'cuisine-demo@qreatery.test',
                    ],
                ],
            ]),
            'openingHours' => $restaurant->openingHours()->orderBy('day_of_week')->get(),
            'subscription' => $restaurant->subscriptions()->latest('id')->with('plan')->first(),
            'displayEmail' => 'demo-contact@qreatery.test',
            'displayPhone' => '+225 00 00 00 00 00',
            'displayAddress' => 'Boulevard de la Démonstration, Abidjan (Mode Démo)',
        ];
    }

    /**
     * Fournit les tables pour l'espace tables & QR codes.
     *
     * @return array<string, mixed>
     */
    public function getTablesData(Restaurant $restaurant, bool $isDemo): array
    {
        if (! $isDemo) {
            $tables = $restaurant->restaurantTables()->whereNull('archived_at')->with(['qrCodes'])->orderBy('id')->get();
            $takeawayQr = $restaurant->qrCodes()->where('type', 'takeaway')->where('status', 'active')->first();
            $qrConfig = $restaurant->settings['qr_options'] ?? [];

            $activeOrdersByTable = $restaurant->orders()
                ->whereIn('preparation_status', ['pending', 'accepted', 'preparing', 'ready'])
                ->whereNotNull('restaurant_table_id')
                ->get()
                ->groupBy('restaurant_table_id');

            return [
                'tables' => $tables,
                'takeawayQr' => $takeawayQr,
                'qrConfig' => $qrConfig,
                'activeOrdersByTable' => $activeOrdersByTable,
            ];
        }

        $tables = $this->getDemoTablesCollection();

        // Occupation simulée sur Table 2 et Table 4 uniquement
        $demoOrdersTable = collect([
            2 => collect([(object) ['id' => 1]]),
            4 => collect([(object) ['id' => 2]]),
        ]);

        return [
            'tables' => $tables,
            'takeawayQr' => (object) [
                'id' => 999,
                'token' => 'DEMO-TAKEAWAY-TOKEN',
                'status' => (object) ['value' => 'active'],
            ],
            'qrConfig' => $restaurant->settings['qr_options'] ?? [],
            'activeOrdersByTable' => $demoOrdersTable,
        ];
    }

    /**
     * Tables d'exemple factices.
     */
    private function getDemoTablesCollection(): Collection
    {
        return collect([
            (object) [
                'id' => 1,
                'label' => 'Table 1',
                'capacity' => 4,
                'zone' => 'Salle Principale',
                'is_active' => true,
                'activeQrCode' => (object) ['token' => 'DEMO-TBL-1'],
                'qrCodes' => collect([(object) ['token' => 'DEMO-TBL-1', 'status' => (object) ['value' => 'active']]]),
            ],
            (object) [
                'id' => 2,
                'label' => 'Table 2',
                'capacity' => 2,
                'zone' => 'Salle Principale',
                'is_active' => true,
                'activeQrCode' => (object) ['token' => 'DEMO-TBL-2'],
                'qrCodes' => collect([(object) ['token' => 'DEMO-TBL-2', 'status' => (object) ['value' => 'active']]]),
            ],
            (object) [
                'id' => 3,
                'label' => 'Table 3',
                'capacity' => 4,
                'zone' => 'Salle Principale',
                'is_active' => true,
                'activeQrCode' => (object) ['token' => 'DEMO-TBL-3'],
                'qrCodes' => collect([(object) ['token' => 'DEMO-TBL-3', 'status' => (object) ['value' => 'active']]]),
            ],
            (object) [
                'id' => 4,
                'label' => 'Table 4',
                'capacity' => 6,
                'zone' => 'Salle Principale',
                'is_active' => true,
                'activeQrCode' => (object) ['token' => 'DEMO-TBL-4'],
                'qrCodes' => collect([(object) ['token' => 'DEMO-TBL-4', 'status' => (object) ['value' => 'active']]]),
            ],
            (object) [
                'id' => 5,
                'label' => 'Terrasse 1',
                'capacity' => 4,
                'zone' => 'Terrasse Extérieure',
                'is_active' => true,
                'activeQrCode' => (object) ['token' => 'DEMO-TER-1'],
                'qrCodes' => collect([(object) ['token' => 'DEMO-TER-1', 'status' => (object) ['value' => 'active']]]),
            ],
            (object) [
                'id' => 6,
                'label' => 'Terrasse 2',
                'capacity' => 2,
                'zone' => 'Terrasse Extérieure',
                'is_active' => true,
                'activeQrCode' => (object) ['token' => 'DEMO-TER-2'],
                'qrCodes' => collect([(object) ['token' => 'DEMO-TER-2', 'status' => (object) ['value' => 'active']]]),
            ],
        ]);
    }

    /**
     * Commandes récentes factices pour le tableau de bord.
     */
    private function getDemoRecentOrdersCollection(): Collection
    {
        return collect([
            (object) [
                'id' => 9901,
                'number' => 'ORD-2026-0001',
                'customer_name' => 'Client Démo 1',
                'created_at' => Carbon::now()->subMinutes(12),
                'mode' => 'dine_in',
                'restaurantTable' => (object) ['label' => 'Table 2'],
                'items' => collect([(object) ['id' => 1], (object) ['id' => 2]]),
                'preparation_status' => 'preparing',
                'total_minor' => 1250000, // 12 500 XOF
            ],
            (object) [
                'id' => 9902,
                'number' => 'ORD-2026-0002',
                'customer_name' => 'Client Démo 2',
                'created_at' => Carbon::now()->subMinutes(25),
                'mode' => 'dine_in',
                'restaurantTable' => (object) ['label' => 'Table 4'],
                'items' => collect([(object) ['id' => 1], (object) ['id' => 2], (object) ['id' => 3]]),
                'preparation_status' => 'ready',
                'total_minor' => 1800000, // 18 000 XOF
            ],
            (object) [
                'id' => 9903,
                'number' => 'ORD-2026-0003',
                'customer_name' => 'Client Démo 3',
                'created_at' => Carbon::now()->subMinutes(50),
                'mode' => 'dine_in',
                'restaurantTable' => (object) ['label' => 'Table 1'],
                'items' => collect([(object) ['id' => 1]]),
                'preparation_status' => 'served',
                'total_minor' => 600000, // 6 000 XOF
            ],
            (object) [
                'id' => 9904,
                'number' => 'ORD-2026-0004',
                'customer_name' => 'Client Démo 4',
                'created_at' => Carbon::now()->subHours(2),
                'mode' => 'takeaway',
                'restaurantTable' => null,
                'items' => collect([(object) ['id' => 1], (object) ['id' => 2]]),
                'preparation_status' => 'served',
                'total_minor' => 1200000, // 12 000 XOF
            ],
        ]);
    }

    /**
     * Liste complète de commandes factices pour la section Commandes.
     */
    private function getDemoOrdersListCollection(): Collection
    {
        return collect([
            (object) [
                'id' => 9901,
                'number' => 'ORD-2026-0001',
                'customer_name' => 'Client Démo 1',
                'customer_phone' => '+225 00 00 00 01',
                'mode' => 'dine_in',
                'restaurantTable' => (object) ['label' => 'Table 2'],
                'preparation_status' => 'preparing',
                'payment_status' => 'paid',
                'total_minor' => 1250000,
                'subtotal_minor' => 1050000,
                'tax_minor' => 200000,
                'notes' => 'Sans oignons s\'il vous plaît (Exemple Démo)',
                'created_at' => Carbon::now()->subMinutes(12),
                'items' => collect([
                    (object) [
                        'id' => 1,
                        'product_name' => 'Burger Qreatery Signature',
                        'quantity' => 1,
                        'unit_price_minor' => 650000,
                        'total_price_minor' => 650000,
                        'options' => collect([(object) ['option_name' => 'Sauce Maison BBQ', 'price_delta_minor' => 0]]),
                    ],
                    (object) [
                        'id' => 2,
                        'product_name' => 'Frites Maison & Boisson',
                        'quantity' => 1,
                        'unit_price_minor' => 400000,
                        'total_price_minor' => 400000,
                        'options' => collect([]),
                    ],
                ]),
                'payments' => collect([
                    (object) [
                        'id' => 1,
                        'method' => 'cash',
                        'amount_minor' => 1250000,
                        'status' => (object) ['value' => 'succeeded'],
                        'processed_at' => Carbon::now()->subMinutes(10),
                    ],
                ]),
                'statusHistories' => collect([
                    (object) [
                        'status' => 'pending',
                        'created_at' => Carbon::now()->subMinutes(12),
                        'author_name' => 'Client Web Démo',
                    ],
                    (object) [
                        'status' => 'accepted',
                        'created_at' => Carbon::now()->subMinutes(11),
                        'author_name' => 'Caissier Démo',
                    ],
                    (object) [
                        'status' => 'preparing',
                        'created_at' => Carbon::now()->subMinutes(10),
                        'author_name' => 'Chef Cuisine Démo',
                    ],
                ]),
            ],
            (object) [
                'id' => 9902,
                'number' => 'ORD-2026-0002',
                'customer_name' => 'Client Démo 2',
                'customer_phone' => '+225 00 00 00 02',
                'mode' => 'dine_in',
                'restaurantTable' => (object) ['label' => 'Table 4'],
                'preparation_status' => 'ready',
                'payment_status' => 'paid',
                'total_minor' => 1800000,
                'subtotal_minor' => 1500000,
                'tax_minor' => 300000,
                'notes' => null,
                'created_at' => Carbon::now()->subMinutes(25),
                'items' => collect([
                    (object) [
                        'id' => 3,
                        'product_name' => 'Brochettes de Filet Braisé',
                        'quantity' => 2,
                        'unit_price_minor' => 600000,
                        'total_price_minor' => 1200000,
                        'options' => collect([]),
                    ],
                    (object) [
                        'id' => 4,
                        'product_name' => 'Cocktail Passion Démo',
                        'quantity' => 2,
                        'unit_price_minor' => 300000,
                        'total_price_minor' => 600000,
                        'options' => collect([]),
                    ],
                ]),
                'payments' => collect([
                    (object) [
                        'id' => 2,
                        'method' => 'mobile_money',
                        'amount_minor' => 1800000,
                        'status' => (object) ['value' => 'succeeded'],
                        'processed_at' => Carbon::now()->subMinutes(20),
                    ],
                ]),
                'statusHistories' => collect([
                    (object) [
                        'status' => 'pending',
                        'created_at' => Carbon::now()->subMinutes(25),
                        'author_name' => 'Client Web Démo',
                    ],
                    (object) [
                        'status' => 'ready',
                        'created_at' => Carbon::now()->subMinutes(5),
                        'author_name' => 'Chef Cuisine Démo',
                    ],
                ]),
            ],
            (object) [
                'id' => 9903,
                'number' => 'ORD-2026-0003',
                'customer_name' => 'Client Démo 3',
                'customer_phone' => '+225 00 00 00 03',
                'mode' => 'dine_in',
                'restaurantTable' => (object) ['label' => 'Table 1'],
                'preparation_status' => 'served',
                'payment_status' => 'paid',
                'total_minor' => 600000,
                'subtotal_minor' => 500000,
                'tax_minor' => 100000,
                'notes' => null,
                'created_at' => Carbon::now()->subMinutes(50),
                'items' => collect([
                    (object) [
                        'id' => 5,
                        'product_name' => 'Salade César Croustillante',
                        'quantity' => 1,
                        'unit_price_minor' => 600000,
                        'total_price_minor' => 600000,
                        'options' => collect([]),
                    ],
                ]),
                'payments' => collect([
                    (object) [
                        'id' => 3,
                        'method' => 'card',
                        'amount_minor' => 600000,
                        'status' => (object) ['value' => 'succeeded'],
                        'processed_at' => Carbon::now()->subMinutes(45),
                    ],
                ]),
                'statusHistories' => collect([
                    (object) [
                        'status' => 'served',
                        'created_at' => Carbon::now()->subMinutes(30),
                        'author_name' => 'Serveur Démo',
                    ],
                ]),
            ],
            (object) [
                'id' => 9904,
                'number' => 'ORD-2026-0004',
                'customer_name' => 'Client Démo 4',
                'customer_phone' => '+225 00 00 00 04',
                'mode' => 'takeaway',
                'restaurantTable' => null,
                'preparation_status' => 'served',
                'payment_status' => 'paid',
                'total_minor' => 1200000,
                'subtotal_minor' => 1000000,
                'tax_minor' => 200000,
                'notes' => 'Commande à emporter prête à 12h00',
                'created_at' => Carbon::now()->subHours(2),
                'items' => collect([
                    (object) [
                        'id' => 6,
                        'product_name' => 'Poulet Braisé & Alloco',
                        'quantity' => 1,
                        'unit_price_minor' => 950000,
                        'total_price_minor' => 950000,
                        'options' => collect([]),
                    ],
                    (object) [
                        'id' => 7,
                        'product_name' => 'Boisson Fraîche',
                        'quantity' => 1,
                        'unit_price_minor' => 250000,
                        'total_price_minor' => 250000,
                        'options' => collect([]),
                    ],
                ]),
                'payments' => collect([
                    (object) [
                        'id' => 4,
                        'method' => 'cash',
                        'amount_minor' => 1200000,
                        'status' => (object) ['value' => 'succeeded'],
                        'processed_at' => Carbon::now()->subHours(2),
                    ],
                ]),
                'statusHistories' => collect([
                    (object) [
                        'status' => 'completed',
                        'created_at' => Carbon::now()->subHour(),
                        'author_name' => 'Caissier Démo',
                    ],
                ]),
            ],
            (object) [
                'id' => 9905,
                'number' => 'ORD-2026-0005',
                'customer_name' => 'Client Démo 5',
                'customer_phone' => '+225 00 00 00 05',
                'mode' => 'dine_in',
                'restaurantTable' => (object) ['label' => 'Table 3'],
                'preparation_status' => 'pending',
                'payment_status' => 'pending',
                'total_minor' => 850000,
                'subtotal_minor' => 700000,
                'tax_minor' => 150000,
                'notes' => 'Table 3 en attente de validation',
                'created_at' => Carbon::now()->subMinutes(3),
                'items' => collect([
                    (object) [
                        'id' => 8,
                        'product_name' => 'Jus Naturel Bissap',
                        'quantity' => 2,
                        'unit_price_minor' => 200000,
                        'total_price_minor' => 400000,
                        'options' => collect([]),
                    ],
                    (object) [
                        'id' => 9,
                        'product_name' => 'Sandwich Club Poulet',
                        'quantity' => 1,
                        'unit_price_minor' => 450000,
                        'total_price_minor' => 450000,
                        'options' => collect([]),
                    ],
                ]),
                'payments' => collect([]),
                'statusHistories' => collect([
                    (object) [
                        'status' => 'pending',
                        'created_at' => Carbon::now()->subMinutes(3),
                        'author_name' => 'Client Web Démo',
                    ],
                ]),
            ],
        ]);
    }
}
