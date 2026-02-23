<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Click>
 */
class ClickFactory extends Factory
{
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'product_id' => Product::factory(),
            'referrer' => fake()->url(),
            'ua' => fake()->userAgent(),
            'ip_hash' => hash('sha256', fake()->ipv4()),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
