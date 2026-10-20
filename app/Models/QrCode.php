<?php

namespace App\Models;

use Database\Factories\QrCodeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['restaurant_id', 'restaurant_table_id', 'token', 'type', 'status', 'revoked_at'])]
class QrCode extends Model
{
    /** @use HasFactory<QrCodeFactory> */
    use HasFactory;

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function restaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class);
    }

    protected function casts(): array
    {
        return ['revoked_at' => 'datetime'];
    }
}
