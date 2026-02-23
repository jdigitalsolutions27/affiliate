<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'amount' => fake()->randomFloat(2, 20, 300),
            'method_text' => fake()->randomElement(['Bank Transfer', 'GCash', 'PayPal']),
            'account_text' => fake()->name().' - '.fake()->numerify('#######'),
            'status' => Withdrawal::STATUS_PENDING,
            'admin_note' => null,
            'paid_reference' => null,
            'processed_by' => null,
            'processed_at' => null,
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
