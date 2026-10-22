<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RestaurantInternalManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_restaurant_dashboard_renders_successfully(): void
    {
        $response = $this->get('/?view=restaurant');
        $response->assertOk()
            ->assertSee('Tableau de bord')
            ->assertSee('Actions Rapides du Restaurant')
            ->assertSee('Caisse POS Tactile')
            ->assertSee('Écran Cuisine KDS');
    }

    public function test_restaurant_menu_section_renders_successfully(): void
    {
        $response = $this->get('/?view=restaurant&section=menu');
        $response->assertOk()
            ->assertSee('Plats &amp; Boissons', false)
            ->assertSee('Catégories')
            ->assertSee('Options &amp; Suppléments', false)
            ->assertSee('Burger Qreatery Signature');
    }

    public function test_restaurant_tables_section_renders_successfully(): void
    {
        $response = $this->get('/?view=restaurant&section=tables');
        $response->assertOk()
            ->assertSee('Plan de Salle &amp; QR Codes', false)
            ->assertSee('QR Code « À Emporter » (Takeaway)', false)
            ->assertSee('Table 1');
    }

    public function test_restaurant_orders_section_renders_successfully(): void
    {
        $response = $this->get('/?view=restaurant&section=orders');
        $response->assertOk()
            ->assertSee('Gestion des Commandes')
            ->assertSee('N° Commande')
            ->assertSee('ORD-2026-0001');
    }

    public function test_restaurant_reports_section_renders_successfully(): void
    {
        $response = $this->get('/?view=restaurant&section=reports');
        $response->assertOk()
            ->assertSee('Rapports &amp; Performances Financières', false)
            ->assertSee('Chiffre d\'Affaires Net', false)
            ->assertSee('Télécharger PDF');
    }

    public function test_restaurant_settings_section_renders_successfully(): void
    {
        $response = $this->get('/?view=restaurant&section=settings');
        $response->assertOk()
            ->assertSee('Configuration de l\'Établissement', false)
            ->assertSee('Équipe du Restaurant')
            ->assertSee('Propriétaire Démo')
            ->assertSee('Horaires de Service Hebdomadaires');
    }
}
