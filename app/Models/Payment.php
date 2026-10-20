<?php

namespace App\Models;

use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['restaurant_id', 'order_id', 'operator_id', 'original_payment_id', 'type', 'amount_minor', 'currency', 'method', 'external_reference', 'idempotency_key', 'status', 'failure_reason', 'metadata', 'processed_at'])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $attributes = [
        'type' => 'charge',
        'status' => 'succeeded',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function originalPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'original_payment_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Payment::class, 'original_payment_id');
    }

    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'metadata' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
