<?php

namespace App\Models;

use App\SubscriptionStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['subscription_id', 'actor_id', 'from_status', 'to_status', 'reason'])]
class SubscriptionStatusHistory extends Model
{
    public const UPDATED_AT = null;

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    protected function casts(): array
    {
        return [
            'from_status' => SubscriptionStatus::class,
            'to_status' => SubscriptionStatus::class,
        ];
    }
}
