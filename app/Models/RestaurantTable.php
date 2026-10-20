<?php

namespace App\Models;

use Database\Factories\RestaurantTableFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['restaurant_id', 'label', 'capacity', 'is_active', 'archived_at'])]
class RestaurantTable extends Model
{
    /** @use HasFactory<RestaurantTableFactory> */
    use HasFactory;

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function assistanceRequests(): HasMany
    {
        return $this->hasMany(AssistanceRequest::class);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }
}
