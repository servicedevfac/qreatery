<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('restaurants.{restaurantId}', function ($user, int $restaurantId): bool {
    return $user->restaurantMemberships()
        ->where('restaurant_id', $restaurantId)
        ->where('is_active', true)
        ->exists();
});
