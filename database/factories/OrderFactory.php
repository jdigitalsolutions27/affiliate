<?php

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 5);
        $price = fake()->randomFloat(2, 20, 500);

        return [
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'product_id' => Product::factory(),
            'qty' => $qty,
            'total_amount' => round($qty * $price, 2),
            'affiliate_id' => Affiliate::factory(),
            'status' => Order::STATUS_PENDING,
            'source' => 'public',
            'flow_type' => Order::FLOW_CHECKOUT_LITE,
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
