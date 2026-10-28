<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuestSessionPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_session_does_not_display_real_live_orders_or_customer_names(): void
    {
        $restaurant = Restaurant::query()->where('slug', 'restaurant-repos')->firstOrFail();

        // Création d'une vraie commande confidentielle en base
        Order::create([
            'restaurant_id' => $restaurant->id,
            'number' => 'ORD-REAL-CONFIDENTIAL',
            'tracking_token' => Str::random(32),
            'mode' => 'dine_in',
            'preparation_status' => 'preparing',
            'payment_status' => 'paid',
            'customer_name' => 'Sory ben khaled',
            'customer_phone' => '+225 07 00 00 99',
            'total_minor' => 851500,
            'subtotal_minor' => 851500,
            'tax_minor' => 0,
            'currency' => 'XOF',
        ]);

        // Un visiteur non connecté (Session invité) accède au tableau de bord
        $response = $this->get('/?view=restaurant');

        $response->assertOk()
            ->assertSee('Session invité (Mode Démonstration)')
            ->assertSee('Données factices')
            ->assertDontSee('Sory ben khaled')
            ->assertDontSee('ORD-REAL-CONFIDENTIAL')
            ->assertSee('Client Démo');
    }

    public function test_guest_session_orders_section_does_not_leak_real_orders(): void
    {
        $restaurant = Restaurant::query()->where('slug', 'restaurant-repos')->firstOrFail();

        Order::create([
            'restaurant_id' => $restaurant->id,
            'number' => 'ORD-PRIVATE-999',
            'tracking_token' => Str::random(32),
            'mode' => 'dine_in',
            'preparation_status' => 'preparing',
            'payment_status' => 'paid',
            'customer_name' => 'Client Réel Privé',
            'customer_phone' => '+225 05 55 55 55',
            'total_minor' => 99900,
            'subtotal_minor' => 99900,
            'tax_minor' => 0,
            'currency' => 'XOF',
        ]);

        $response = $this->get('/?view=restaurant&section=orders');

        $response->assertOk()
            ->assertDontSee('Client Réel Privé')
            ->assertDontSee('ORD-PRIVATE-999')
            ->assertSee('Client Démo 1');
    }

    public function test_guest_session_cannot_snoop_other_real_restaurants(): void
    {
        // Création d'un autre établissement confidentiel
        Restaurant::create([
            'name' => 'Restaurant Secret VIP',
            'slug' => 'restaurant-secret-vip',
            'currency' => 'XOF',
            'timezone' => 'Africa/Abidjan',
            'status' => 'active',
        ]);

        $response = $this->get('/?view=restaurant');

        $response->assertOk()
            ->assertDontSee('Restaurant Secret VIP');
    }

    public function test_authenticated_owner_can_see_real_restaurant_data(): void
    {
        $restaurant = Restaurant::query()->where('slug', 'restaurant-repos')->firstOrFail();

        Order::create([
            'restaurant_id' => $restaurant->id,
            'number' => 'ORD-OWNER-REAL',
            'tracking_token' => Str::random(32),
            'mode' => 'dine_in',
            'preparation_status' => 'preparing',
            'payment_status' => 'paid',
            'customer_name' => 'Client Réel Propriétaire',
            'customer_phone' => '+225 01 02 03 04',
            'total_minor' => 450000,
            'subtotal_minor' => 450000,
            'tax_minor' => 0,
            'currency' => 'XOF',
        ]);

        $owner = User::query()->where('email', 'owner@repos.test')->first()
            ?? User::query()->where('email', 'owner@qreatery.test')->first()
            ?? User::query()->where('email', 'like', 'owner%')->firstOrFail();

        $response = $this->actingAs($owner)->get('/?view=restaurant');

        $response->assertOk()
            ->assertDontSee('Session invité (Mode Démonstration)')
            ->assertSee('Client Réel Propriétaire');
    }
}
