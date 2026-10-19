<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRestaurantConfigurationRequest;
use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;

class RestaurantConfigurationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateRestaurantConfigurationRequest $request, Restaurant $restaurant): RestaurantResource
    {
        $restaurant->update([
            'settings' => array_replace($restaurant->settings ?? [], $request->validated()),
        ]);

        return new RestaurantResource($restaurant->refresh());
    }
}
