<?php

use App\Http\Controllers\Api\V1\RestaurantController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->name('api.v1.')->group(function (): void {
    Route::apiResource('restaurants', RestaurantController::class)->only([
        'index',
        'store',
        'show',
        'update',
    ]);
});
