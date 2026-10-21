<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport - {{ $restaurant->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #222; font-size: 11px; }
        h1 { margin-bottom: 4px; font-size: 22px; }
        h2 { margin-top: 24px; font-size: 15px; color: #444; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 7px; text-align: left; }
        th { background: #f2f2f2; }
        .number { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $restaurant->name }}</h1>
    <div class="muted">
        Rapport du {{ \Carbon\Carbon::parse($report['period']['from'])->format('d/m/Y H:i') }}
        au {{ \Carbon\Carbon::parse($report['period']['to'])->format('d/m/Y H:i') }}
        — Mode : {{ $report['period']['mode'] ?? 'tous' }}
    </div>

    <h2>Commandes</h2>
    <table>
        <tr><th>Total</th><th>Acceptées</th><th>Terminées</th><th>Annulées</th><th>Rejetées</th></tr>
        <tr>
            <td class="number">{{ $report['orders']['total'] }}</td>
            <td class="number">{{ $report['orders']['accepted'] }}</td>
            <td class="number">{{ $report['orders']['completed'] }}</td>
            <td class="number">{{ $report['orders']['cancelled'] }}</td>
            <td class="number">{{ $report['orders']['rejected'] }}</td>
        </tr>
    </table>

    <h2>Finances</h2>
    <table>
        <tr><th>Encaissé</th><th>Remboursé</th><th>Net collecté</th><th>Devise</th></tr>
        <tr>
            <td class="number">{{ number_format($report['payments']['charged_minor'] / 100, 2, ',', ' ') }}</td>
            <td class="number">{{ number_format($report['payments']['refunded_minor'] / 100, 2, ',', ' ') }}</td>
            <td class="number">{{ number_format($report['payments']['net_collected_minor'] / 100, 2, ',', ' ') }}</td>
            <td>{{ $report['payments']['currency'] }}</td>
        </tr>
    </table>

    <h2>Top produits</h2>
    <table>
        <tr><th>Produit</th><th>Quantité</th><th>Chiffre d’affaires</th></tr>
        @forelse ($report['top_products'] as $product)
            <tr>
                <td>{{ $product['product_name'] }}</td>
                <td class="number">{{ $product['quantity'] }}</td>
                <td class="number">{{ number_format($product['revenue_minor'] / 100, 2, ',', ' ') }} {{ $report['payments']['currency'] }}</td>
            </tr>
        @empty
            <tr><td colspan="3">Aucune vente pour cette période.</td></tr>
        @endforelse
    </table>
</body>
</html>
