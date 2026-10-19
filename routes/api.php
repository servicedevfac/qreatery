<?php

use App\Http\Controllers\Api\V1\RestaurantClosureController;
use App\Http\Controllers\Api\V1\RestaurantConfigurationController;
use App\Http\Controllers\Api\V1\RestaurantController;
use App\Http\Controllers\Api\V1\RestaurantLogoController;
use App\Http\Controllers\Api\V1\RestaurantMemberController;
use App\Http\Controllers\Api\V1\RestaurantOpeningHourController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified'])->prefix('v1')->name('api.v1.')->group(function (): void {
    Route::apiResource('restaurants', RestaurantController::class)->only([
        'index',
        'store',
        'show',
        'update',
    ]);

    Route::post('restaurants/{restaurant}/members/{membership}/reactivation', [RestaurantMemberController::class, 'reactivate'])
        ->name('restaurants.members.reactivate');
    Route::apiResource('restaurants.members', RestaurantMemberController::class)
        ->parameters(['members' => 'membership'])
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::patch('restaurants/{restaurant}/configuration', RestaurantConfigurationController::class)
        ->name('restaurants.configuration.update');
    Route::get('restaurants/{restaurant}/opening-hours', [RestaurantOpeningHourController::class, 'index'])
        ->name('restaurants.opening-hours.index');
    Route::put('restaurants/{restaurant}/opening-hours', [RestaurantOpeningHourController::class, 'update'])
        ->name('restaurants.opening-hours.update');
    Route::apiResource('restaurants.closures', RestaurantClosureController::class)
        ->parameters(['closures' => 'closure'])
        ->only(['index', 'store', 'destroy']);
    Route::post('restaurants/{restaurant}/logo', RestaurantLogoController::class)
        ->name('restaurants.logo.update');
});
