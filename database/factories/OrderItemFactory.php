<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 5);
        $price = fake()->randomFloat(2, 20, 500);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'qty' => $qty,
            'unit_price' => $price,
            'line_total' => round($qty * $price, 2),
        ];
    }
}
