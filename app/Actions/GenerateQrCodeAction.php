<?php

namespace App\Actions;

use App\Models\QrCode;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateQrCodeAction
{
    public function handle(Restaurant $restaurant, string $type, ?int $restaurantTableId): QrCode
    {
        return DB::transaction(function () use ($restaurant, $type, $restaurantTableId): QrCode {
            $existingQuery = $restaurant->qrCodes()
                ->where('type', $type)
                ->where('status', 'active');

            if ($restaurantTableId === null) {
                $existingQuery->whereNull('restaurant_table_id');
            } else {
                $existingQuery->where('restaurant_table_id', $restaurantTableId);
            }

            $existingQuery->update([
                'status' => 'revoked',
                'revoked_at' => now(),
            ]);

            return $restaurant->qrCodes()->create([
                'restaurant_table_id' => $restaurantTableId,
                'token' => $this->uniqueToken(),
                'type' => $type,
                'status' => 'active',
            ])->load('restaurantTable');
        });
    }

    private function uniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (QrCode::where('token', $token)->exists());

        return $token;
    }
}
