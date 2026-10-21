<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['restaurant_id', 'restaurant_table_id', 'number', 'tracking_token', 'mode', 'preparation_status', 'payment_status', 'customer_name', 'customer_phone', 'notes', 'subtotal_minor', 'tax_minor', 'service_fee_minor', 'total_minor', 'currency', 'accepted_at', 'ready_at', 'completed_at', 'cancelled_at', 'late_notified_at'])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $hidden = ['tracking_token'];

    protected $attributes = [
        'preparation_status' => 'pending',
        'payment_status' => 'unpaid',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function restaurantTable(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function assistanceRequests(): HasMany
    {
        return $this->hasMany(AssistanceRequest::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    protected function casts(): array
    {
        return [
            'subtotal_minor' => 'integer',
            'tax_minor' => 'integer',
            'service_fee_minor' => 'integer',
            'total_minor' => 'integer',
            'accepted_at' => 'datetime',
            'ready_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'late_notified_at' => 'datetime',
        ];
    }
}
