<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuditLogIndexRequest;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function __invoke(AuditLogIndexRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();
        $logs = AuditLog::query()
            ->with('actor')
            ->when(isset($data['restaurant_id']), fn ($query) => $query->where('restaurant_id', $data['restaurant_id']))
            ->when(isset($data['action']), fn ($query) => $query->where('action', $data['action']))
            ->when(isset($data['actor_id']), fn ($query) => $query->where('actor_id', $data['actor_id']))
            ->when(isset($data['date_from']), fn ($query) => $query->whereDate('created_at', '>=', $data['date_from']))
            ->when(isset($data['date_to']), fn ($query) => $query->whereDate('created_at', '<=', $data['date_to']))
            ->latest('id')
            ->paginate();

        return AuditLogResource::collection($logs);
    }
}
