<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\RecordAuditLogAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAdminRestaurantStatusRequest;
use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RestaurantController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $restaurants = Restaurant::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = '%'.$request->string('search')->toString().'%';
                $query->where(fn ($inner) => $inner->where('name', 'like', $search)->orWhere('slug', 'like', $search));
            })
            ->latest('id')
            ->paginate();

        return RestaurantResource::collection($restaurants);
    }

    public function updateStatus(
        UpdateAdminRestaurantStatusRequest $request,
        Restaurant $restaurant,
        RecordAuditLogAction $recordAuditLog,
    ): RestaurantResource {
        $fromStatus = $restaurant->status->value;
        $restaurant->update(['status' => $request->validated('status')]);
        $recordAuditLog->handle($request->user(), 'restaurant.status_changed', $restaurant, $restaurant, [
            'from_status' => $fromStatus,
            'to_status' => $restaurant->refresh()->status->value,
        ]);

        return new RestaurantResource($restaurant);
    }
}
