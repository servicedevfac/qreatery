<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditLogIndexRequest;
use App\Http\Resources\AuditLogResource;
use App\Models\Restaurant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class AuditLogController extends Controller
{
    public function __invoke(AuditLogIndexRequest $request, Restaurant $restaurant): AnonymousResourceCollection
    {
        Gate::authorize('viewAuditLogs', $restaurant);
        $data = $request->validated();
        $logs = $restaurant->auditLogs()
            ->with('actor')
            ->when(isset($data['action']), fn ($query) => $query->where('action', $data['action']))
            ->when(isset($data['actor_id']), fn ($query) => $query->where('actor_id', $data['actor_id']))
            ->when(isset($data['date_from']), fn ($query) => $query->whereDate('created_at', '>=', $data['date_from']))
            ->when(isset($data['date_to']), fn ($query) => $query->whereDate('created_at', '<=', $data['date_to']))
            ->latest('id')
            ->paginate();

        return AuditLogResource::collection($logs);
    }
}
