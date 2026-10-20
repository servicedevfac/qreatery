<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAssistanceRequest;
use App\Http\Resources\AssistanceRequestResource;
use App\Models\AssistanceRequest;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class AssistanceRequestController extends Controller
{
    public function index(Request $request, Restaurant $restaurant): AnonymousResourceCollection
    {
        Gate::authorize('viewOrders', $restaurant);

        return AssistanceRequestResource::collection(
            $restaurant->assistanceRequests()
                ->with(['restaurantTable', 'order', 'handler'])
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
                ->latest('id')
                ->paginate()
        );
    }

    public function show(Restaurant $restaurant, AssistanceRequest $assistanceRequest): AssistanceRequestResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $assistanceRequest);
        Gate::authorize('viewOrders', $restaurant);

        return new AssistanceRequestResource($assistanceRequest->load(['restaurantTable', 'order', 'handler']));
    }

    public function update(UpdateAssistanceRequest $request, Restaurant $restaurant, AssistanceRequest $assistanceRequest): AssistanceRequestResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $assistanceRequest);
        $status = $request->validated('status');
        $assistanceRequest->update([
            'status' => $status,
            'handled_by' => $request->user()->id,
            'handled_at' => $assistanceRequest->handled_at ?? now(),
            'resolved_at' => $status === 'resolved' ? now() : null,
        ]);

        return new AssistanceRequestResource($assistanceRequest->refresh()->load(['restaurantTable', 'order', 'handler']));
    }

    private function ensureBelongsToRestaurant(Restaurant $restaurant, AssistanceRequest $assistanceRequest): void
    {
        abort_unless($assistanceRequest->restaurant_id === $restaurant->id, 404);
    }
}
