<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOptionGroupRequest;
use App\Http\Requests\UpdateOptionGroupRequest;
use App\Http\Resources\OptionGroupResource;
use App\Models\OptionGroup;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OptionGroupController extends Controller
{
    public function index(Restaurant $restaurant): AnonymousResourceCollection
    {
        Gate::authorize('view', $restaurant);

        return OptionGroupResource::collection(
            $restaurant->optionGroups()->with('options')->orderBy('position')->orderBy('name')->paginate()
        );
    }

    public function store(StoreOptionGroupRequest $request, Restaurant $restaurant): JsonResponse
    {
        $optionGroup = DB::transaction(function () use ($request, $restaurant): OptionGroup {
            $data = $request->validated();
            $options = Arr::pull($data, 'options', []);
            $optionGroup = $restaurant->optionGroups()->create($data);
            $optionGroup->options()->createMany($options);

            return $optionGroup->load('options');
        });

        return (new OptionGroupResource($optionGroup))->response()->setStatusCode(201);
    }

    public function show(Restaurant $restaurant, OptionGroup $optionGroup): OptionGroupResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $optionGroup);
        Gate::authorize('view', $restaurant);

        return new OptionGroupResource($optionGroup->load('options'));
    }

    public function update(UpdateOptionGroupRequest $request, Restaurant $restaurant, OptionGroup $optionGroup): OptionGroupResource
    {
        $this->ensureBelongsToRestaurant($restaurant, $optionGroup);

        DB::transaction(function () use ($request, $optionGroup): void {
            $data = $request->validated();
            $options = Arr::pull($data, 'options');
            $minimum = $data['minimum_selections'] ?? $optionGroup->minimum_selections;
            $maximum = $data['maximum_selections'] ?? $optionGroup->maximum_selections;
            abort_if($minimum > $maximum, 422, 'Le minimum de choix ne peut pas dépasser le maximum.');
            $optionGroup->update($data);

            if (is_array($options)) {
                $optionGroup->options()->delete();
                $optionGroup->options()->createMany($options);
            }
        });

        return new OptionGroupResource($optionGroup->refresh()->load('options'));
    }

    public function destroy(Restaurant $restaurant, OptionGroup $optionGroup): Response
    {
        $this->ensureBelongsToRestaurant($restaurant, $optionGroup);
        Gate::authorize('manageMenu', $restaurant);
        $optionGroup->update(['is_active' => false]);

        return response()->noContent();
    }

    private function ensureBelongsToRestaurant(Restaurant $restaurant, OptionGroup $optionGroup): void
    {
        abort_unless($optionGroup->restaurant_id === $restaurant->id, 404);
    }
}
