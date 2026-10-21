<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Planche de QR Codes - {{ $restaurant->name }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 10mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #1e3a8a;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 11px;
            color: #64748b;
        }
        .grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px;
        }
        .card {
            border: 1px dashed #94a3b8;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            width: 48%;
            vertical-align: top;
            background: #ffffff;
        }
        .restau-title {
            font-size: 13px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 4px;
        }
        .table-label {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            background: #eff6ff;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 8px;
        }
        .qr-img {
            margin: 5px auto;
            display: block;
        }
        .scan-text {
            font-size: 10px;
            color: #475569;
            margin-top: 6px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $restaurant->name }} — Planche QR Codes pour Tables</h1>
        <p>Imprimez, découpez et déposez chaque QR Code sur la table correspondante.</p>
    </div>

    <table class="grid">
        @php
            $items = $cards;
            $chunks = array_chunk($items, 2);
        @endphp
        @foreach ($chunks as $row)
            <tr>
                @foreach ($row as $card)
                    <td class="card">
                        <div class="restau-title">{{ $restaurant->name }}</div>
                        <div class="table-label">{{ $card['label'] }}</div>
                        <div>
                            <img class="qr-img" src="data:image/svg+xml;base64,{{ $card['qr_base64'] }}" width="140" height="140" alt="QR Code" />
                        </div>
                        <div class="scan-text">Scannez pour commander</div>
                    </td>
                @endforeach
                @if (count($row) === 1)
                    <td style="width: 48%;"></td>
                @endif
            </tr>
        @endforeach
    </table>
</body>
</html>
