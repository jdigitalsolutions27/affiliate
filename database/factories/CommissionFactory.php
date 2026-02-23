<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Commission>
 */
class CommissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'order_item_id' => OrderItem::factory(),
            'affiliate_id' => Affiliate::factory(),
            'commission_type' => 'percentage',
            'rate_value' => fake()->numberBetween(5, 15),
            'amount' => fake()->randomFloat(2, 5, 200),
            'status' => Commission::STATUS_APPROVED,
            'notes' => null,
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
