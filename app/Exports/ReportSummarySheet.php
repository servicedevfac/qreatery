<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReportSummarySheet implements FromArray, WithHeadings, WithTitle
{
    /** @param array<string, mixed> $report */
    public function __construct(private readonly array $report) {}

    public function array(): array
    {
        $orders = $this->report['orders'];
        $payments = $this->report['payments'];

        return [
            ['Restaurant', $this->report['restaurant']['name']],
            ['Début', $this->report['period']['from']],
            ['Fin', $this->report['period']['to']],
            ['Mode', $this->report['period']['mode'] ?? 'Tous'],
            ['Commandes', $orders['total']],
            ['Acceptées', $orders['accepted']],
            ['Terminées', $orders['completed']],
            ['Annulées', $orders['cancelled']],
            ['Rejetées', $orders['rejected']],
            ['Panier moyen (unité mineure)', $orders['average_basket_minor']],
            ['Préparation moyenne (secondes)', $orders['average_preparation_seconds'] ?? 'N/A'],
            ['Encaissé (unité mineure)', $payments['charged_minor']],
            ['Remboursé (unité mineure)', $payments['refunded_minor']],
            ['Net collecté (unité mineure)', $payments['net_collected_minor']],
            ['Devise', $payments['currency']],
        ];
    }

    public function headings(): array
    {
        return ['Indicateur', 'Valeur'];
    }

    public function title(): string
    {
        return 'Résumé';
    }
}
