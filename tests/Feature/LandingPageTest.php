<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Votre restaurant.');
        $response->assertSee('Toute votre gestion.');
        $response->assertSee('Un seul espace.');
        $response->assertSee('Menu Numérique');
        $response->assertSee('Écran Cuisine — KDS');
        $response->assertSee('Caisse Tactile — POS');
        $response->assertSee('Formule Starter');
        $response->assertSee('Formule Pro');
        $response->assertSee('Formule Entreprise');
        $response->assertSee('Questions fréquemment posées');
        $response->assertSee('QREATERY');
    }

    public function test_client_view_preserved_with_restaurant_parameter(): void
    {
        $response = $this->get('/?restaurant=restaurant-demo');

        $response->assertStatus(200);
        $response->assertSee('client-menu-container');
    }

    public function test_pos_view_preserved(): void
    {
        $response = $this->get('/?view=pos');

        $response->assertStatus(200);
        $response->assertSee('btn-pos-tab-sale');
    }

    public function test_kds_view_preserved(): void
    {
        $response = $this->get('/?view=kds');

        $response->assertStatus(200);
        $response->assertSee('kds-metric-total');
    }

    public function test_login_portal_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Connexion');
    }
}
