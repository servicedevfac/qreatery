<?php

namespace App\Actions;

use App\Models\Restaurant;
use App\Models\RestaurantMembership;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateRestaurantMemberAction
{
    /** @param array{name: string, email: string, role: string, permissions?: array<string>|null} $data */
    public function handle(Restaurant $restaurant, array $data): RestaurantMembership
    {
        return DB::transaction(function () use ($restaurant, $data): RestaurantMembership {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => Str::password(32)]
            );

            if (! $user->is_active) {
                $user->forceFill(['is_active' => true])->save();
            }

            return $restaurant->memberships()->create([
                'user_id' => $user->id,
                'role' => $data['role'],
                'permissions' => $data['permissions'] ?? [],
                'is_active' => true,
            ])->load('user');
        });
    }
}
