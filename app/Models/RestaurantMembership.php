<?php

namespace App\Models;

use App\RestaurantRole;
use Database\Factories\RestaurantMembershipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['restaurant_id', 'user_id', 'role', 'permissions', 'is_active'])]
class RestaurantMembership extends Model
{
    /** @use HasFactory<RestaurantMembershipFactory> */
    use HasFactory;

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'role' => RestaurantRole::class,
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
