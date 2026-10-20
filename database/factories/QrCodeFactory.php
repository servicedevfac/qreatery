<?php

namespace Database\Factories;

use App\Models\QrCode;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<QrCode>
 */
class QrCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'restaurant_table_id' => null,
            'token' => Str::random(64),
            'type' => 'takeaway',
            'status' => 'active',
            'revoked_at' => null,
        ];
    }
}
