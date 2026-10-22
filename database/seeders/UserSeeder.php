<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Utilisateurs nécessaires pour la gestion du restaurant et de la plateforme SaaS :
     * Mot de passe standard : 'password' (ou 'Password123!')
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // Supprimer les utilisateurs des anciens restaurants pour n'avoir que les comptes nécessaires
        User::query()->whereIn('email', [
            'owner2@qreatery.test',
            'staff2@qreatery.test',
            'owner3@qreatery.test',
            'owner4@qreatery.test',
        ])->delete();

        // 1. Super-Administrateur Plateforme SaaS
        $admin = User::query()->firstOrNew(['email' => 'admin@qreatery.test']);
        $admin->name = 'Super Administrateur Qreatery';
        $admin->password = $password;
        $admin->is_active = true;
        $admin->email_verified_at = now();
        $admin->forceFill(['is_platform_admin' => true]);
        $admin->save();

        // 2. Propriétaire du Restaurant
        $owner = User::query()->firstOrNew(['email' => 'owner@qreatery.test']);
        $owner->name = 'Alexandre Danho (Propriétaire)';
        $owner->password = $password;
        $owner->is_active = true;
        $owner->email_verified_at = now();
        $owner->forceFill(['is_platform_admin' => false]);
        $owner->save();

        // 3. Manager / Responsable de Salle
        $manager = User::query()->firstOrNew(['email' => 'manager@qreatery.test']);
        $manager->name = 'Marc Kouassi (Manager de Salle)';
        $manager->password = $password;
        $manager->is_active = true;
        $manager->email_verified_at = now();
        $manager->forceFill(['is_platform_admin' => false]);
        $manager->save();

        // 4. Caissier (Poste Caisse Tactile POS)
        $cashier = User::query()->firstOrNew(['email' => 'cashier@qreatery.test']);
        $cashier->name = 'Aminata Diallo (Caissière)';
        $cashier->password = $password;
        $cashier->is_active = true;
        $cashier->email_verified_at = now();
        $cashier->forceFill(['is_platform_admin' => false]);
        $cashier->save();

        // 5. Chef Cuisinier (Écran Cuisine KDS)
        $cook = User::query()->firstOrNew(['email' => 'cook@qreatery.test']);
        $cook->name = 'Chef Ibrahim Traoré (Cuisine KDS)';
        $cook->password = $password;
        $cook->is_active = true;
        $cook->email_verified_at = now();
        $cook->forceFill(['is_platform_admin' => false]);
        $cook->save();

        // 6. Serveur de Salle
        $waiter = User::query()->firstOrNew(['email' => 'waiter@qreatery.test']);
        $waiter->name = 'Jean-Luc Yao (Serveur de Salle)';
        $waiter->password = $password;
        $waiter->is_active = true;
        $waiter->email_verified_at = now();
        $waiter->forceFill(['is_platform_admin' => false]);
        $waiter->save();
    }
}
