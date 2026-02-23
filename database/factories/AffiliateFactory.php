<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Affiliate>
 */
class AffiliateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => 'AFF-'.strtoupper(Str::random(6)),
            'status' => Affiliate::STATUS_ACTIVE,
            'default_commission_type' => 'percentage',
            'default_commission_value' => fake()->numberBetween(5, 20),
        ];
    }
}
