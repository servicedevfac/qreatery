<?php

namespace App\Actions;

use App\Models\Restaurant;
use App\Models\User;
use App\RestaurantRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateRestaurantAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(User $owner, array $attributes): Restaurant
    {
        return DB::transaction(function () use ($owner, $attributes): Restaurant {
            $baseSlug = Str::slug($attributes['name']) ?: 'restaurant';

            $restaurant = Restaurant::create([
                ...$attributes,
                'slug' => $baseSlug.'-'.Str::lower(Str::random(6)),
            ]);

            $restaurant->memberships()->create([
                'user_id' => $owner->id,
                'role' => RestaurantRole::Owner,
                'is_active' => true,
            ]);

            return $restaurant;
        });
    }
}
