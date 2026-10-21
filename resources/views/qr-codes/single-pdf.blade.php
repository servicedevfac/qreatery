<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>QR Code - {{ $restaurant->name }} - {{ $qrCode->restaurantTable?->label ?? 'Menu' }}</title>
    <style>
        @page {
            size: a5 portrait;
            margin: 15mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            text-align: center;
            color: #1e293b;
            background: #ffffff;
            margin: 0;
            padding: 20px;
        }
        .card {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 30px 20px;
            background: #ffffff;
        }
        .restaurant-name {
            font-size: 26px;
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 6px;
        }
        .table-badge {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            padding: 6px 18px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 25px;
        }
        .qr-wrapper {
            margin: 20px auto;
            display: inline-block;
            padding: 15px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
        }
        .instructions {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 20px;
            margin-bottom: 6px;
        }
        .sub-instructions {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 20px;
        }
        .footer-url {
            font-size: 10px;
            color: #94a3b8;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="restaurant-name">{{ $restaurant->name }}</div>
        
        <div class="table-badge">
            @if ($qrCode->restaurantTable)
                Table {{ $qrCode->restaurantTable->label }}
            @else
                Menu &amp; Commande
            @endif
        </div>

        <div class="qr-wrapper">
            <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" width="240" height="240" alt="QR Code" />
        </div>

        <div class="instructions">Scannez ce QR Code avec votre smartphone</div>
        <div class="sub-instructions">Consultez notre carte, commandez et réglez en quelques clics.</div>

        <div class="footer-url">{{ $targetUrl }}</div>
    </div>
</body>
</html>
