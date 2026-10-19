<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRestaurantOpeningHoursRequest;
use App\Http\Resources\RestaurantOpeningHourResource;
use App\Models\Restaurant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class RestaurantOpeningHourController extends Controller
{
    public function index(Restaurant $restaurant): AnonymousResourceCollection
    {
        Gate::authorize('view', $restaurant);

        return RestaurantOpeningHourResource::collection(
            $restaurant->openingHours()->orderBy('day_of_week')->get()
        );
    }

    public function update(
        UpdateRestaurantOpeningHoursRequest $request,
        Restaurant $restaurant,
    ): AnonymousResourceCollection {
        DB::transaction(function () use ($request, $restaurant): void {
            foreach ($request->validated('hours') as $hour) {
                $isClosed = (bool) $hour['is_closed'];
                $restaurant->openingHours()->updateOrCreate(
                    ['day_of_week' => $hour['day_of_week']],
                    [
                        'opens_at' => $isClosed ? null : $hour['opens_at'],
                        'closes_at' => $isClosed ? null : $hour['closes_at'],
                        'is_closed' => $isClosed,
                    ]
                );
            }
        });

        return RestaurantOpeningHourResource::collection(
            $restaurant->openingHours()->orderBy('day_of_week')->get()
        );
    }
}
