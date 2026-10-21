<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['actor_id', 'restaurant_id', 'action', 'auditable_type', 'auditable_id', 'ip_address', 'user_agent', 'metadata'])]
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
}
