<?php

namespace App\Models;

use App\SubscriptionPaymentStatus;
use Database\Factories\SubscriptionPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['subscription_id', 'recorded_by', 'amount_minor', 'currency', 'method', 'transaction_reference', 'idempotency_key', 'status', 'proof_path', 'failure_reason', 'metadata', 'paid_at'])]
class SubscriptionPayment extends Model
{
    /** @use HasFactory<SubscriptionPaymentFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => 'pending',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    protected function casts(): array
    {
        return [
            'amount_minor' => 'integer',
            'status' => SubscriptionPaymentStatus::class,
            'metadata' => 'array',
            'paid_at' => 'datetime',
        ];
    }
}
