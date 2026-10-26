<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\QrCodeResource;
use App\Models\QrCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicQrCodeController extends Controller
{
    public function __invoke(Request $request, string $token): QrCodeResource|RedirectResponse
    {
        $qrCode = QrCode::query()
            ->with(['restaurant', 'restaurantTable'])
            ->where('token', $token)
            ->where('status', 'active')
            ->firstOrFail();

        abort_unless($qrCode->restaurant->status->value === 'active', 404);
        abort_if($qrCode->restaurantTable !== null && (! $qrCode->restaurantTable->is_active || $qrCode->restaurantTable->archived_at !== null), 404);

        if (! $request->expectsJson() && $request->acceptsHtml()) {
            return redirect('/?qr='.$qrCode->token);
        }

        return new QrCodeResource($qrCode);
    }
}
