<?php

namespace App\Models;

use Database\Factories\AssistanceRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['restaurant_id', 'restaurant_table_id', 'order_id', 'handled_by', 'type', 'status', 'message', 'handled_at', 'resolved_at'])]
class AssistanceRequest extends Model
{
    /** @use HasFactory<AssistanceRequestFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'pending',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function restaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }
}
