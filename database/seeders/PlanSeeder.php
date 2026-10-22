<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Formule Starter',
                'slug' => 'starter',
                'description' => 'Idéal pour les petits restaurants et bars démarrant avec les commandes par QR code.',
                'price_minor' => 1500000, // 15 000 FCFA
                'currency' => 'XOF',
                'billing_period' => 'month',
                'billing_interval' => 1,
                'limits' => [
                    'employees' => 5,
                    'tables' => 10,
                ],
                'features' => [
                    'Prise de commande QR à table',
                    'Gestion de plan de salle et tables',
                    'Suivi de commande client en direct',
                    'Support par email 5j/7',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Formule Pro',
                'slug' => 'pro',
                'description' => 'La solution complète pour restaurants dynamiques : KDS cuisine temps réel et caisse POS.',
                'price_minor' => 3500000, // 35 000 FCFA
                'currency' => 'XOF',
                'billing_period' => 'month',
                'billing_interval' => 1,
                'limits' => [
                    'employees' => 20,
                    'tables' => 40,
                ],
                'features' => [
                    'Prise de commande QR table & à emporter',
                    'Écran cuisine KDS en temps réel (WebSocket)',
                    'Caisse tactile POS et division de note',
                    'Paiements Espèces, Mobile Money et Cartes',
                    'Rapports financiers et exports PDF/XLSX',
                    'Support prioritaire 7j/7',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Formule Entreprise',
                'slug' => 'enterprise',
                'description' => 'Accompagnement sur-mesure pour franchises, groupes et établissements à fort volume.',
                'price_minor' => 7500000, // 75 000 FCFA
                'currency' => 'XOF',
                'billing_period' => 'month',
                'billing_interval' => 1,
                'limits' => [
                    'employees' => 100,
                    'tables' => 200,
                ],
                'features' => [
                    'Toutes les fonctionnalités Pro incluses',
                    'Gestion multi-établissements centralisée',
                    'Accompagnement et onboarding dédié',
                    'Rapports analytiques personnalisés',
                    'SLA de disponibilité garanti 99.9%',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Ancienne Formule Standard',
                'slug' => 'legacy-standard',
                'description' => 'Formule historique archivée (non accessible aux nouvelles souscriptions).',
                'price_minor' => 2000000, // 20 000 FCFA
                'currency' => 'XOF',
                'billing_period' => 'month',
                'billing_interval' => 1,
                'limits' => [
                    'employees' => 10,
                    'tables' => 20,
                ],
                'features' => [
                    'Commande QR basique',
                    'Gestion de salle',
                ],
                'is_active' => false,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::query()->updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }
    }
}
