<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRestaurantLogoRequest;
use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Storage;

class RestaurantLogoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateRestaurantLogoRequest $request, Restaurant $restaurant): RestaurantResource
    {
        $previousLogo = $restaurant->logo_path;
        $path = $request->file('logo')->storePublicly("restaurants/{$restaurant->id}/logos", 'public');

        $restaurant->update(['logo_path' => $path]);

        if ($previousLogo !== null) {
            Storage::disk('public')->delete($previousLogo);
        }

        return new RestaurantResource($restaurant->refresh());
    }
}
