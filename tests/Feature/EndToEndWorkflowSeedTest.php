<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\QrCode;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class EndToEndWorkflowSeedTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Exécute le jeu complet de seeders de démonstration
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Scénario 1 : Authentification et rôles des comptes de démonstration
     */
    public function test_scenario_01_authentication_and_roles_are_properly_configured(): void
    {
        $admin = User::query()->where('email', 'admin@qreatery.test')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->is_platform_admin);

        $owner = User::query()->where('email', 'owner@qreatery.test')->first();
        $this->assertNotNull($owner);
        $this->assertFalse($owner->is_platform_admin);

        $demoRestaurant = Restaurant::query()->where('slug', 'restaurant-demo')->first();
        $this->assertNotNull($demoRestaurant);

        $membership = $owner->membershipFor($demoRestaurant);
        $this->assertNotNull($membership);
        $this->assertSame('owner', $membership->role->value);
    }

    /**
     * Scénario 2 : Tableau de bord Super-Admin (KPIs, Abonnements & Finances)
     */
    public function test_scenario_02_super_admin_dashboard_metrics(): void
    {
        $admin = User::query()->where('email', 'admin@qreatery.test')->first();

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/dashboard');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'restaurants' => ['total', 'active', 'suspended'],
                    'subscriptions' => ['active', 'grace_period', 'expired', 'expiring_within_7_days'],
                    'subscription_revenue' => ['current_month_minor', 'total_minor'],
                ],
            ]);

        $this->assertGreaterThanOrEqual(1, $response->json('data.restaurants.total'));
        $this->assertGreaterThanOrEqual(1, $response->json('data.subscriptions.active'));
    }

    /**
     * Scénario 3 : Annuaire des restaurants Super-Admin
     */
    public function test_scenario_03_super_admin_restaurants_directory(): void
    {
        $admin = User::query()->where('email', 'admin@qreatery.test')->first();

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/restaurants');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['slug' => 'restaurant-demo']);
    }

    /**
     * Scénario 4 : Gestion des plans SaaS (Consultation, Filtre, Création)
     */
    public function test_scenario_04_super_admin_saas_plans(): void
    {
        $admin = User::query()->where('email', 'admin@qreatery.test')->first();

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/plans');

        $response->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonFragment(['slug' => 'pro'])
            ->assertJsonFragment(['slug' => 'starter']);

        // Filtre plans actifs uniquement
        $activeOnly = $this->actingAs($admin)->getJson('/api/v1/admin/plans?active=1');
        $activeOnly->assertOk()->assertJsonCount(3, 'data');
    }

    /**
     * Scénario 5 : Consultation des abonnements et paiements SaaS
     */
    public function test_scenario_05_subscriptions_and_payments_consultation(): void
    {
        $admin = User::query()->where('email', 'admin@qreatery.test')->first();
        $demoRestaurant = Restaurant::query()->where('slug', 'restaurant-demo')->first();

        $response = $this->actingAs($admin)->getJson("/api/v1/admin/restaurants/{$demoRestaurant->id}/subscriptions");

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'status', 'starts_at', 'ends_at']]]);

        $subId = $response->json('data.0.id');
        $paymentsResponse = $this->actingAs($admin)->getJson("/api/v1/admin/subscriptions/{$subId}/payments");
        $paymentsResponse->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /**
     * Scénario 6 : Consultation des journaux d'audit de la plateforme
     */
    public function test_scenario_06_super_admin_audit_logs(): void
    {
        $admin = User::query()->where('email', 'admin@qreatery.test')->first();

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/audit-logs');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'action', 'created_at']]]);
        $this->assertNotEmpty($response->json('data'));
    }

    /**
     * Scénario 7 : Menu public accessible par QR Code
     */
    public function test_scenario_07_public_menu_and_qr_code_resolution(): void
    {
        $qrCode = QrCode::query()->where('type', 'table')->first();
        $this->assertNotNull($qrCode);

        // 1. Résolution du QR code
        $qrResponse = $this->getJson("/api/v1/public/qr-codes/{$qrCode->token}");
        $qrResponse->assertOk()
            ->assertJsonPath('data.restaurant.slug', 'restaurant-demo')
            ->assertJsonPath('data.table.label', 'Table 1');

        // 2. Consultation du menu public du restaurant
        $menuResponse = $this->getJson('/api/v1/public/restaurants/restaurant-demo/menu');
        $menuResponse->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'restaurant' => ['name', 'slug', 'currency'],
                    'categories' => [
                        ['id', 'name', 'slug', 'products'],
                    ],
                ],
            ]);
    }

    /**
     * Scénario 8 : Création d'une commande client par QR Code
     */
    public function test_scenario_08_client_creates_order_via_public_api(): void
    {
        $burger = Product::query()->where('slug', 'burger-qreatery-signature')->first();
        $this->assertNotNull($burger);

        $cookingOption = $burger->optionGroups()
            ->where('minimum_selections', '>=', 1)
            ->first()
            ?->options()
            ->first();

        $qrCode = QrCode::query()->where('type', 'table')->first();
        $this->assertNotNull($qrCode);

        $response = $this->postJson('/api/v1/public/restaurants/restaurant-demo/orders', [
            'mode' => 'dine_in',
            'qr_token' => $qrCode->token,
            'customer_name' => 'Jean Client Test',
            'customer_phone' => '+225 0102030405',
            'items' => [
                [
                    'product_id' => $burger->id,
                    'quantity' => 1,
                    'option_ids' => $cookingOption ? [$cookingOption->id] : [],
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.preparation_status', 'pending')
            ->assertJsonPath('data.customer_name', 'Jean Client Test')
            ->assertJsonStructure(['data' => ['id', 'number', 'tracking_token']]);
    }

    /**
     * Scénario 9 : Suivi de commande en direct par token client
     */
    public function test_scenario_09_order_tracking_by_token(): void
    {
        $order = Order::query()->where('number', 'ORD-2026-0001')->first();
        $this->assertNotNull($order);

        $response = $this->getJson("/api/v1/public/orders/{$order->tracking_token}");

        $response->assertOk()
            ->assertJsonPath('data.number', 'ORD-2026-0001')
            ->assertJsonPath('data.preparation_status', 'pending')
            ->assertJsonStructure(['data' => ['items', 'status_history', 'table']]);
    }

    /**
     * Scénario 10 : Réception et consultation des commandes KDS cuisine
     */
    public function test_scenario_10_kds_kitchen_orders_reception(): void
    {
        $cook = User::query()->where('email', 'cook@qreatery.test')->first();
        $restaurant = Restaurant::query()->where('slug', 'restaurant-demo')->first();

        $response = $this->actingAs($cook)->getJson("/api/v1/restaurants/{$restaurant->id}/orders");

        $response->assertOk()
            ->assertJsonStructure(['data' => [['id', 'number', 'preparation_status', 'items']]]);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    /**
     * Scénario 11 : Progression des statuts de préparation KDS
     */
    public function test_scenario_11_kds_order_status_progression(): void
    {
        $cook = User::query()->where('email', 'cook@qreatery.test')->first();
        $restaurant = Restaurant::query()->where('slug', 'restaurant-demo')->first();
        $order = Order::query()->where('number', 'ORD-2026-0001')->first();

        // 1. Passage de pending à accepted
        $resAccepted = $this->actingAs($cook)->patchJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/status", [
            'status' => 'accepted',
        ]);
        $resAccepted->assertOk()->assertJsonPath('data.preparation_status', 'accepted');

        // 2. Passage de accepted à preparing
        $resPreparing = $this->actingAs($cook)->patchJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/status", [
            'status' => 'preparing',
        ]);
        $resPreparing->assertOk()->assertJsonPath('data.preparation_status', 'preparing');
    }

    /**
     * Scénario 12 : Consultation des commandes au POS (Caisse tactile)
     */
    public function test_scenario_12_pos_orders_management(): void
    {
        $cashier = User::query()->where('email', 'cashier@qreatery.test')->first();
        $restaurant = Restaurant::query()->where('slug', 'restaurant-demo')->first();
        $order = Order::query()->where('number', 'ORD-2026-0002')->first();

        $response = $this->actingAs($cashier)->getJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('data.number', 'ORD-2026-0002')
            ->assertJsonStructure(['data' => ['id', 'items', 'total_minor', 'payment_status']]);
    }

    /**
     * Scénario 13 : Encaissement et paiements partiels au POS
     */
    public function test_scenario_13_pos_payment_and_partial_payments(): void
    {
        $cashier = User::query()->where('email', 'cashier@qreatery.test')->first();
        $restaurant = Restaurant::query()->where('slug', 'restaurant-demo')->first();
        $order = Order::query()->where('number', 'ORD-2026-0002')->first(); // 11 500 FCFA

        // Paiement partiel de 5 000 FCFA
        $response = $this->actingAs($cashier)->postJson("/api/v1/restaurants/{$restaurant->id}/orders/{$order->id}/payments", [
            'amount_minor' => 500000,
            'currency' => 'XOF',
            'method' => 'cash',
            'idempotency_key' => 'idemp-test-pos-pay-01',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.amount_minor', 500000)
            ->assertJsonPath('data.status', 'succeeded');

        // Vérification de la mise à jour de la commande
        $this->assertSame('partially_paid', $order->refresh()->payment_status);
    }

    /**
     * Scénario 14 : Rapports de vente et analyse financière
     */
    public function test_scenario_14_sales_reports_overview(): void
    {
        $owner = User::query()->where('email', 'owner@qreatery.test')->first();
        $restaurant = Restaurant::query()->where('slug', 'restaurant-demo')->first();

        $response = $this->actingAs($owner)->getJson("/api/v1/restaurants/{$restaurant->id}/reports/overview");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'restaurant' => ['id', 'name'],
                    'orders' => ['total', 'average_basket_minor'],
                    'payments' => ['charged_minor', 'net_collected_minor', 'currency'],
                ],
            ]);

        $this->assertGreaterThanOrEqual(1, $response->json('data.orders.total'));
    }

    /**
     * Scénario 15 : Demandes d'assistance (Création par le client et traitement)
     */
    public function test_scenario_15_assistance_requests_workflow(): void
    {
        $qrCode = QrCode::query()->where('type', 'table')->first();
        $this->assertNotNull($qrCode);

        // 1. Client émet une demande d'assistance
        $publicRes = $this->postJson('/api/v1/public/restaurants/restaurant-demo/assistance-requests', [
            'qr_token' => $qrCode->token,
            'type' => 'waiter',
            'message' => 'Besoin de sauce pimentée s\'il vous plaît.',
        ]);
        $publicRes->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $reqId = $publicRes->json('data.id');

        // 2. Serveur prend en charge la demande
        $waiter = User::query()->where('email', 'waiter@qreatery.test')->first();
        $restaurant = Restaurant::query()->where('slug', 'restaurant-demo')->first();

        $patchRes = $this->actingAs($waiter)->patchJson("/api/v1/restaurants/{$restaurant->id}/assistance-requests/{$reqId}", [
            'status' => 'acknowledged',
        ]);
        $patchRes->assertOk()->assertJsonPath('data.status', 'acknowledged');
    }

    /**
     * Scénario 16 : Isolation multi-tenant stricte
     */
    public function test_scenario_16_strict_multi_tenant_isolation(): void
    {
        // Utilisateur externe sans appartenance au restaurant
        $owner2 = User::query()->where('email', 'owner2@qreatery.test')->first() ?? User::factory()->create();
        // Restaurant 1 (Restaurant Démo)
        $restoDemo = Restaurant::query()->where('slug', 'restaurant-demo')->first();

        // 1. Tente d'accéder aux commandes du Restaurant 1 sans appartenance
        $response = $this->actingAs($owner2)->getJson("/api/v1/restaurants/{$restoDemo->id}/orders");

        // Doit être masqué avec 404 (denyAsNotFound pour protection anti-énumération)
        $response->assertNotFound();

        // 2. Membre authentifié mais sans la permission requise (ex: serveur tente d'accéder aux rapports financiers)
        $waiter = User::query()->where('email', 'waiter@qreatery.test')->first();
        $forbiddenRes = $this->actingAs($waiter)->getJson("/api/v1/restaurants/{$restoDemo->id}/reports/overview");
        $forbiddenRes->assertForbidden();
    }

    /**
     * Scénario 17 : Gestion des cas d'erreur et contrôles de sécurité
     */
    public function test_scenario_17_error_handling_and_security(): void
    {
        // 1. Non authentifié sur endpoint protégé -> 401 Unauthorized
        $res401 = $this->getJson('/api/v1/admin/dashboard');
        $res401->assertUnauthorized();

        // 2. Authentifié mais non-admin sur endpoint Super-Admin -> 403 Forbidden
        $owner = User::query()->where('email', 'owner@qreatery.test')->first();
        $res403 = $this->actingAs($owner)->getJson('/api/v1/admin/dashboard');
        $res403->assertForbidden();

        // 3. Ressource inexistante -> 404 Not Found
        $res404 = $this->getJson('/api/v1/public/qr-codes/qr-non-existent-token-xyz');
        $res404->assertNotFound();

        // 4. Données invalides (ex: payload vide sur commande) -> 422 Unprocessable Entity
        $res422 = $this->postJson('/api/v1/public/restaurants/restaurant-demo/orders', []);
        $res422->assertUnprocessable();
    }
}
