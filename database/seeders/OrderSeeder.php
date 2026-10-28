<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemOption;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $reposRestaurant = Restaurant::query()->where('slug', 'restaurant-repos')->first();
        if (! $reposRestaurant) {
            return;
        }

        $waiter = User::query()->where('email', 'waiter@qreatery.test')->first();
        $cook = User::query()->where('email', 'cook@qreatery.test')->first();
        $manager = User::query()->where('email', 'manager@qreatery.test')->first();

        $table1 = RestaurantTable::query()->where('restaurant_id', $reposRestaurant->id)->where('label', 'Table 1')->first();
        $table2 = RestaurantTable::query()->where('restaurant_id', $reposRestaurant->id)->where('label', 'Table 2')->first();
        $table3 = RestaurantTable::query()->where('restaurant_id', $reposRestaurant->id)->where('label', 'Table 3')->first();
        $table4 = RestaurantTable::query()->where('restaurant_id', $reposRestaurant->id)->where('label', 'Table 4')->first();

        $prodBurger = Product::query()->where('restaurant_id', $reposRestaurant->id)->where('slug', 'burger-qreatery-signature')->first();
        $prodCesar = Product::query()->where('restaurant_id', $reposRestaurant->id)->where('slug', 'salade-cesar-croustillante')->first();
        $prodNems = Product::query()->where('restaurant_id', $reposRestaurant->id)->where('slug', 'nems-crevettes-fraiches')->first();
        $prodCote = Product::query()->where('restaurant_id', $reposRestaurant->id)->where('slug', 'cote-de-boeuf-braisee')->first();
        $prodMerou = Product::query()->where('restaurant_id', $reposRestaurant->id)->where('slug', 'filet-merou-braise')->first();
        $prodKedjenou = Product::query()->where('restaurant_id', $reposRestaurant->id)->where('slug', 'poulet-kedjenou-traditionnel')->first();
        $prodMojito = Product::query()->where('restaurant_id', $reposRestaurant->id)->where('slug', 'mojito-passion-frais')->first();
        $prodBissap = Product::query()->where('restaurant_id', $reposRestaurant->id)->where('slug', 'bissap-frais-artisanal')->first();
        $prodFondant = Product::query()->where('restaurant_id', $reposRestaurant->id)->where('slug', 'fondant-chocolat-glace-vanille')->first();

        // ---------------------------------------------------------------------
        // Commande 1 : PENDING (KDS Vert, reçue il y a 5 min, Table 1)
        // ---------------------------------------------------------------------
        $order1 = Order::query()->updateOrCreate(
            ['number' => 'ORD-2026-0001'],
            [
                'restaurant_id' => $reposRestaurant->id,
                'restaurant_table_id' => $table1?->id,
                'tracking_token' => hash('sha256', 'qreatery-track-order-0001'),
                'mode' => 'dine_in',
                'preparation_status' => 'pending',
                'payment_status' => 'unpaid',
                'customer_name' => 'Alice Martin',
                'customer_phone' => '+225 0102030405',
                'notes' => 'Sauce à part s\'il vous plaît.',
                'subtotal_minor' => 550000,
                'tax_minor' => 0,
                'service_fee_minor' => 0,
                'total_minor' => 550000,
                'currency' => 'XOF',
                'created_at' => now()->subMinutes(5),
                'updated_at' => now()->subMinutes(5),
            ]
        );
        $this->syncOrderItems($order1, [
            ['product' => $prodCesar, 'qty' => 1, 'unit_price' => 400000],
            ['product' => $prodBissap, 'qty' => 1, 'unit_price' => 150000],
        ]);
        OrderStatusHistory::query()->firstOrCreate(
            ['order_id' => $order1->id, 'to_status' => 'pending'],
            ['from_status' => null, 'reason' => 'Commande passée par le client via QR code']
        );

        // ---------------------------------------------------------------------
        // Commande 2 : ACCEPTED (KDS Vert, acceptée il y a 10 min, Table 2)
        // ---------------------------------------------------------------------
        $order2 = Order::query()->updateOrCreate(
            ['number' => 'ORD-2026-0002'],
            [
                'restaurant_id' => $reposRestaurant->id,
                'restaurant_table_id' => $table2?->id,
                'tracking_token' => hash('sha256', 'qreatery-track-order-0002'),
                'mode' => 'dine_in',
                'preparation_status' => 'accepted',
                'payment_status' => 'unpaid',
                'customer_name' => 'Bernard Touré',
                'customer_phone' => '+225 0506070809',
                'notes' => null,
                'subtotal_minor' => 1150000,
                'tax_minor' => 0,
                'service_fee_minor' => 0,
                'total_minor' => 1150000,
                'currency' => 'XOF',
                'accepted_at' => now()->subMinutes(10),
                'created_at' => now()->subMinutes(12),
                'updated_at' => now()->subMinutes(10),
            ]
        );
        $this->syncOrderItems($order2, [
            [
                'product' => $prodBurger,
                'qty' => 1,
                'unit_price' => 650000,
                'options' => [
                    ['name' => 'Sauce Barbecue Fumée', 'delta' => 50000],
                    ['name' => 'Double Cheddar Fondu', 'delta' => 100000],
                ],
            ],
            ['product' => $prodMojito, 'qty' => 1, 'unit_price' => 350000],
        ]);
        OrderStatusHistory::query()->firstOrCreate(
            ['order_id' => $order2->id, 'to_status' => 'accepted'],
            ['user_id' => $waiter?->id, 'from_status' => 'pending', 'reason' => 'Commande validée par le personnel']
        );

        // ---------------------------------------------------------------------
        // Commande 3 : PREPARING (KDS Orange 15-30m, créée il y a 22 min, Table 3, Payée)
        // ---------------------------------------------------------------------
        $order3 = Order::query()->updateOrCreate(
            ['number' => 'ORD-2026-0003'],
            [
                'restaurant_id' => $reposRestaurant->id,
                'restaurant_table_id' => $table3?->id,
                'tracking_token' => hash('sha256', 'qreatery-track-order-0003'),
                'mode' => 'dine_in',
                'preparation_status' => 'preparing',
                'payment_status' => 'paid',
                'customer_name' => 'Claire Konan',
                'customer_phone' => null,
                'notes' => 'Viande saignante bien saisie.',
                'subtotal_minor' => 1200000,
                'tax_minor' => 0,
                'service_fee_minor' => 0,
                'total_minor' => 1200000,
                'currency' => 'XOF',
                'accepted_at' => now()->subMinutes(20),
                'created_at' => now()->subMinutes(22),
                'updated_at' => now()->subMinutes(18),
            ]
        );
        $this->syncOrderItems($order3, [
            [
                'product' => $prodCote,
                'qty' => 1,
                'unit_price' => 1200000,
                'options' => [
                    ['name' => 'Saignant', 'delta' => 0],
                ],
            ],
        ]);
        OrderStatusHistory::query()->firstOrCreate(
            ['order_id' => $order3->id, 'to_status' => 'preparing'],
            ['user_id' => $cook?->id, 'from_status' => 'accepted', 'reason' => 'Prise en charge par le chef grillardin']
        );

        // ---------------------------------------------------------------------
        // Commande 4 : PREPARING CRITIQUE (KDS Rouge > 45m, créée il y a 50 min, Table 4)
        // ---------------------------------------------------------------------
        $order4 = Order::query()->updateOrCreate(
            ['number' => 'ORD-2026-0004'],
            [
                'restaurant_id' => $reposRestaurant->id,
                'restaurant_table_id' => $table4?->id,
                'tracking_token' => hash('sha256', 'qreatery-track-order-0004'),
                'mode' => 'dine_in',
                'preparation_status' => 'preparing',
                'payment_status' => 'unpaid',
                'customer_name' => 'David Mensah',
                'customer_phone' => '+225 0708091011',
                'notes' => 'Attention : grande tablée 6 personnes.',
                'subtotal_minor' => 1500000,
                'tax_minor' => 0,
                'service_fee_minor' => 0,
                'total_minor' => 1500000,
                'currency' => 'XOF',
                'accepted_at' => now()->subMinutes(48),
                'created_at' => now()->subMinutes(50),
                'updated_at' => now()->subMinutes(45),
            ]
        );
        $this->syncOrderItems($order4, [
            ['product' => $prodKedjenou, 'qty' => 2, 'unit_price' => 750000],
        ]);
        OrderStatusHistory::query()->firstOrCreate(
            ['order_id' => $order4->id, 'to_status' => 'preparing'],
            ['user_id' => $cook?->id, 'from_status' => 'accepted', 'reason' => 'Cuisson longue canari']
        );

        // ---------------------------------------------------------------------
        // Commande 5 : READY (Prête pour service, créée il y a 32 min, Table 1, Partiellement payée)
        // ---------------------------------------------------------------------
        $order5 = Order::query()->updateOrCreate(
            ['number' => 'ORD-2026-0005'],
            [
                'restaurant_id' => $reposRestaurant->id,
                'restaurant_table_id' => $table1?->id,
                'tracking_token' => hash('sha256', 'qreatery-track-order-0005'),
                'mode' => 'dine_in',
                'preparation_status' => 'ready',
                'payment_status' => 'partially_paid',
                'customer_name' => 'Emma Sanogo',
                'customer_phone' => null,
                'subtotal_minor' => 1200000,
                'tax_minor' => 0,
                'service_fee_minor' => 0,
                'total_minor' => 1200000,
                'currency' => 'XOF',
                'accepted_at' => now()->subMinutes(28),
                'ready_at' => now()->subMinutes(5),
                'created_at' => now()->subMinutes(32),
                'updated_at' => now()->subMinutes(5),
            ]
        );
        $this->syncOrderItems($order5, [
            ['product' => $prodMerou, 'qty' => 1, 'unit_price' => 850000],
            ['product' => $prodFondant, 'qty' => 1, 'unit_price' => 350000],
        ]);
        OrderStatusHistory::query()->firstOrCreate(
            ['order_id' => $order5->id, 'to_status' => 'ready'],
            ['user_id' => $cook?->id, 'from_status' => 'preparing', 'reason' => 'Plats dressés sous la cloche de passe']
        );

        // ---------------------------------------------------------------------
        // Commande 6 : SERVED (Servie au client, créée il y a 65 min, Table 2, Payée)
        // ---------------------------------------------------------------------
        $order6 = Order::query()->updateOrCreate(
            ['number' => 'ORD-2026-0006'],
            [
                'restaurant_id' => $reposRestaurant->id,
                'restaurant_table_id' => $table2?->id,
                'tracking_token' => hash('sha256', 'qreatery-track-order-0006'),
                'mode' => 'dine_in',
                'preparation_status' => 'served',
                'payment_status' => 'paid',
                'customer_name' => 'Fabrice Zadi',
                'customer_phone' => null,
                'subtotal_minor' => 1100000,
                'tax_minor' => 0,
                'service_fee_minor' => 0,
                'total_minor' => 1100000,
                'currency' => 'XOF',
                'accepted_at' => now()->subMinutes(60),
                'ready_at' => now()->subMinutes(40),
                'created_at' => now()->subMinutes(65),
                'updated_at' => now()->subMinutes(35),
            ]
        );
        $this->syncOrderItems($order6, [
            ['product' => $prodBurger, 'qty' => 1, 'unit_price' => 650000],
            ['product' => $prodNems, 'qty' => 1, 'unit_price' => 450000],
        ]);
        OrderStatusHistory::query()->firstOrCreate(
            ['order_id' => $order6->id, 'to_status' => 'served'],
            ['user_id' => $waiter?->id, 'from_status' => 'ready', 'reason' => 'Plats servis en salle']
        );

        // ---------------------------------------------------------------------
        // Commande 7 : COMPLETED (À emporter, payée et remise, il y a 2h)
        // ---------------------------------------------------------------------
        $order7 = Order::query()->updateOrCreate(
            ['number' => 'ORD-2026-0007'],
            [
                'restaurant_id' => $reposRestaurant->id,
                'restaurant_table_id' => null,
                'tracking_token' => hash('sha256', 'qreatery-track-order-0007'),
                'mode' => 'takeaway',
                'preparation_status' => 'completed',
                'payment_status' => 'paid',
                'customer_name' => 'Gérard Boli',
                'customer_phone' => '+225 0102030499',
                'subtotal_minor' => 1300000,
                'tax_minor' => 0,
                'service_fee_minor' => 0,
                'total_minor' => 1300000,
                'currency' => 'XOF',
                'accepted_at' => now()->subHours(2)->addMinutes(5),
                'ready_at' => now()->subHours(2)->addMinutes(25),
                'completed_at' => now()->subHours(1)->subMinutes(30),
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(1)->subMinutes(30),
            ]
        );
        $this->syncOrderItems($order7, [
            ['product' => $prodBurger, 'qty' => 2, 'unit_price' => 650000],
        ]);
        OrderStatusHistory::query()->firstOrCreate(
            ['order_id' => $order7->id, 'to_status' => 'completed'],
            ['user_id' => $waiter?->id, 'from_status' => 'ready', 'reason' => 'Commande remise au client pour emport']
        );

        // ---------------------------------------------------------------------
        // Commande 8 : CANCELLED (Annulée avec motif)
        // ---------------------------------------------------------------------
        $order8 = Order::query()->updateOrCreate(
            ['number' => 'ORD-2026-0008'],
            [
                'restaurant_id' => $reposRestaurant->id,
                'restaurant_table_id' => $table3?->id,
                'tracking_token' => hash('sha256', 'qreatery-track-order-0008'),
                'mode' => 'dine_in',
                'preparation_status' => 'cancelled',
                'payment_status' => 'unpaid',
                'customer_name' => 'Hélène Koffi',
                'customer_phone' => null,
                'subtotal_minor' => 350000,
                'tax_minor' => 0,
                'service_fee_minor' => 0,
                'total_minor' => 350000,
                'currency' => 'XOF',
                'cancelled_at' => now()->subHours(2)->subMinutes(50),
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(2)->subMinutes(50),
            ]
        );
        $this->syncOrderItems($order8, [
            ['product' => $prodMojito, 'qty' => 1, 'unit_price' => 350000],
        ]);
        OrderStatusHistory::query()->firstOrCreate(
            ['order_id' => $order8->id, 'to_status' => 'cancelled'],
            ['user_id' => $manager?->id, 'from_status' => 'pending', 'reason' => 'Client contraint de partir suite à une urgence']
        );
    }

    private function syncOrderItems(Order $order, array $items): void
    {
        $order->items()->delete();

        foreach ($items as $itemData) {
            $product = $itemData['product'];
            if (! $product) {
                continue;
            }

            $qty = $itemData['qty'] ?? 1;
            $unitPrice = $itemData['unit_price'] ?? $product->price_minor;
            $options = $itemData['options'] ?? [];

            $optionsTotal = array_sum(array_column($options, 'delta'));
            $lineTotal = ($unitPrice + $optionsTotal) * $qty;

            $orderItem = OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price_minor' => $unitPrice,
                'quantity' => $qty,
                'line_total_minor' => $lineTotal,
                'instructions' => null,
            ]);

            foreach ($options as $opt) {
                OrderItemOption::query()->create([
                    'order_item_id' => $orderItem->id,
                    'option_id' => null,
                    'option_name' => $opt['name'],
                    'price_delta_minor' => $opt['delta'] ?? 0,
                ]);
            }
        }
    }
}
