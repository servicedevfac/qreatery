<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ManagePlatformAdmin extends Command
{
    protected $signature = 'platform:admin {email : Adresse e-mail du compte} {--revoke : Retirer les droits de super administrateur}';

    protected $description = 'Accorde ou retire les droits de super administrateur de la plateforme';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $user = User::query()->where('email', $email)->first();

        if (! $user instanceof User) {
            $this->error("Aucun utilisateur ne correspond à l’adresse {$email}.");

            return self::FAILURE;
        }

        $isPlatformAdmin = ! $this->option('revoke');
        $user->forceFill(['is_platform_admin' => $isPlatformAdmin])->save();

        $this->info($isPlatformAdmin
            ? "Le compte {$email} est maintenant super administrateur."
            : "Les droits de super administrateur ont été retirés du compte {$email}.");

        return self::SUCCESS;
    }
}
