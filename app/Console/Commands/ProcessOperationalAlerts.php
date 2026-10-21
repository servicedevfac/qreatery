<?php

namespace App\Console\Commands;

use App\Actions\ProcessOperationalAlertsAction;
use Illuminate\Console\Command;

class ProcessOperationalAlerts extends Command
{
    protected $signature = 'alerts:process';

    protected $description = 'Détecte les retards, échéances et paiements échoués puis envoie les alertes';

    public function handle(ProcessOperationalAlertsAction $processAlerts): int
    {
        $results = $processAlerts->handle();

        $this->table(['Type', 'Nombre'], [
            ['Commandes en retard', $results['delayed_orders']],
            ['Abonnements bientôt expirés', $results['expiring_subscriptions']],
            ['Abonnements transitionnés', $results['transitioned_subscriptions']],
            ['Paiements échoués', $results['failed_payments']],
        ]);

        return self::SUCCESS;
    }
}
