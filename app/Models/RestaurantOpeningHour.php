<?php

namespace App\Models;

use Database\Factories\RestaurantOpeningHourFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['restaurant_id', 'day_of_week', 'opens_at', 'closes_at', 'is_closed'])]
class RestaurantOpeningHour extends Model
{
    /** @use HasFactory<RestaurantOpeningHourFactory> */
    use HasFactory;

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    protected function casts(): array
    {
        return ['is_closed' => 'boolean'];
    }
}
