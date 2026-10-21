<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class RecordAuditLogAction
{
    /** @param array<string, mixed> $metadata */
    public function handle(?User $actor, string $action, ?Model $auditable = null, ?Restaurant $restaurant = null, array $metadata = []): AuditLog
    {
        $request = request();

        return AuditLog::query()->create([
            'actor_id' => $actor?->id,
            'restaurant_id' => $restaurant?->id,
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }
}
