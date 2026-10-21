<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'price_minor', 'currency', 'billing_period', 'billing_interval', 'limits', 'features', 'terms', 'is_active'])]
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    protected $attributes = [
        'currency' => 'XOF',
        'billing_interval' => 1,
        'is_active' => true,
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    protected function casts(): array
    {
        return [
            'price_minor' => 'integer',
            'billing_interval' => 'integer',
            'limits' => 'array',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
