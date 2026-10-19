<?php

namespace App\Models;

use App\RestaurantStatus;
use Database\Factories\RestaurantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'description',
    'logo_path',
    'address',
    'phone',
    'email',
    'currency',
    'timezone',
    'status',
    'settings',
])]
class Restaurant extends Model
{
    /** @use HasFactory<RestaurantFactory> */
    use HasFactory;

    protected $attributes = [
        'currency' => 'XOF',
        'timezone' => 'Africa/Abidjan',
        'status' => 'active',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(RestaurantMembership::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'restaurant_memberships')
            ->withPivot(['role', 'permissions', 'is_active'])
            ->withTimestamps();
    }

    public function openingHours(): HasMany
    {
        return $this->hasMany(RestaurantOpeningHour::class);
    }

    public function closures(): HasMany
    {
        return $this->hasMany(RestaurantClosure::class);
    }

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'status' => RestaurantStatus::class,
        ];
    }
}
