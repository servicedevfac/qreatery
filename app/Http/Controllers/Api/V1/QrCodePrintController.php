<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\Restaurant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

class QrCodePrintController extends Controller
{
    public function download(Request $request, Restaurant $restaurant, QrCode $qrCode): Response
    {
        abort_unless($qrCode->restaurant_id === $restaurant->id, 404);
        Gate::authorize('manageQrCodes', $restaurant);

        $qrCode->load('restaurantTable');

        $targetUrl = route('api.v1.public.qr-codes.show', ['token' => $qrCode->token]);
        $svgContent = (string) QrCodeGenerator::size(300)->errorCorrection('H')->generate($targetUrl);

        $format = $request->query('format', 'pdf');

        if ($format === 'svg') {
            $filename = 'qr-'.$restaurant->slug.'-'.($qrCode->restaurantTable?->label ?? 'menu').'.svg';

            return response($svgContent, 200, [
                'Content-Type' => 'image/svg+xml',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        $qrCodeBase64 = base64_encode($svgContent);
        $filename = 'qr-table-'.$restaurant->slug.'-'.($qrCode->restaurantTable?->label ?? 'menu').'.pdf';

        return Pdf::loadView('qr-codes.single-pdf', compact('restaurant', 'qrCode', 'qrCodeBase64', 'targetUrl'))
            ->setPaper('a5', 'portrait')
            ->download($filename);
    }

    public function printSheet(Restaurant $restaurant): Response
    {
        Gate::authorize('manageQrCodes', $restaurant);

        $qrCodes = $restaurant->qrCodes()
            ->where('status', 'active')
            ->with('restaurantTable')
            ->get();

        $cards = [];
        foreach ($qrCodes as $qr) {
            $url = route('api.v1.public.qr-codes.show', ['token' => $qr->token]);
            $svg = (string) QrCodeGenerator::size(200)->errorCorrection('M')->generate($url);
            $cards[] = [
                'label' => $qr->restaurantTable ? 'Table '.$qr->restaurantTable->label : 'Menu Principal',
                'qr_base64' => base64_encode($svg),
            ];
        }

        $filename = 'planche-qr-codes-'.$restaurant->slug.'.pdf';

        return Pdf::loadView('qr-codes.sheet-pdf', compact('restaurant', 'cards'))
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }
}
