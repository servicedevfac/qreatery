<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon Cuisine - {{ $order->number }}</title>
    <style>
        @page {
            size: 80mm 200mm;
            margin: 4mm;
        }
        body {
            font-family: DejaVu Sans, monospace, sans-serif;
            font-size: 11px;
            color: #000000;
            margin: 0;
            padding: 2mm;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .divider {
            border-top: 1px dashed #000000;
            margin: 6px 0;
        }
        .double-divider {
            border-top: 2px solid #000000;
            margin: 8px 0;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .order-number {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
            margin: 4px 0;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 6px;
        }
        .meta-table td {
            padding: 1px 0;
            font-size: 11px;
        }
        .table-badge {
            font-size: 15px;
            font-weight: bold;
            padding: 3px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .items-table th {
            text-align: left;
            border-bottom: 1px solid #000000;
            padding-bottom: 3px;
            font-size: 10px;
        }
        .item-row td {
            padding-top: 6px;
            vertical-align: top;
        }
        .qty {
            font-size: 13px;
            font-weight: bold;
            width: 32px;
        }
        .item-name {
            font-size: 12px;
            font-weight: bold;
        }
        .variant-name {
            font-size: 10px;
            color: #333333;
        }
        .option-item {
            font-size: 10px;
            padding-left: 10px;
            color: #222222;
        }
        .instructions {
            font-size: 11px;
            font-weight: bold;
            font-style: italic;
            padding: 2px 4px;
            margin-top: 2px;
            background: #eeeeee;
            display: inline-block;
        }
        .notes-box {
            margin-top: 8px;
            padding: 4px;
            border: 1px solid #000000;
            font-size: 11px;
            font-weight: bold;
        }
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="title uppercase">{{ $restaurant->name }}</div>
        <div class="bold">*** BON DE COMMANDE CUISINE ***</div>
        <div class="order-number">#{{ $order->number }}</div>
    </div>

    <div class="divider"></div>

    <table class="meta-table">
        <tr>
            <td class="bold">EMPLACEMENT :</td>
            <td class="text-right table-badge">
                @if ($order->restaurantTable)
                    TABLE {{ $order->restaurantTable->label }}
                @else
                    {{ strtoupper($order->mode ?? 'COMPTOIR') }}
                @endif
            </td>
        </tr>
        <tr>
            <td>Date &amp; Heure :</td>
            <td class="text-right">{{ $order->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @if ($order->customer_name)
            <tr>
                <td>Client :</td>
                <td class="text-right bold">{{ $order->customer_name }}</td>
            </tr>
        @endif
        @if ($order->customer_phone)
            <tr>
                <td>Tél :</td>
                <td class="text-right">{{ $order->customer_phone }}</td>
            </tr>
        @endif
        <tr>
            <td>Mode :</td>
            <td class="text-right uppercase">{{ $order->mode }}</td>
        </tr>
    </table>

    <div class="double-divider"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 32px;">QTÉ</th>
                <th>ARTICLE &amp; DÉTAILS</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr class="item-row">
                    <td class="qty">x{{ $item->quantity }}</td>
                    <td>
                        <div class="item-name">{{ $item->product_name }}</div>
                        @if ($item->variant_name)
                            <div class="variant-name">Variante : {{ $item->variant_name }}</div>
                        @endif

                        @if ($item->options && $item->options->isNotEmpty())
                            @foreach ($item->options as $option)
                                <div class="option-item">+ {{ $option->option_name }}</div>
                            @endforeach
                        @endif

                        @if ($item->instructions)
                            <div class="instructions">Note : {{ $item->instructions }}</div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($order->notes)
        <div class="notes-box">
            NOTE COMMANDE : {{ $order->notes }}
        </div>
    @endif

    <div class="double-divider"></div>

    <div class="footer">
        Statut : <strong>{{ strtoupper($order->preparation_status) }}</strong> | Total items : {{ $order->items->sum('quantity') }}
    </div>
</body>
</html>
