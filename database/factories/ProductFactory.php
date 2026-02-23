<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'category_id' => null,
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(10, 999),
            'description' => fake()->sentence(18),
            'price' => fake()->randomFloat(2, 20, 1000),
            'stock' => fake()->numberBetween(5, 250),
            'status' => Product::STATUS_ACTIVE,
            'is_featured' => fake()->boolean(20),
            'is_best_seller' => fake()->boolean(20),
            'default_commission_type' => 'percentage',
            'default_commission_value' => fake()->numberBetween(5, 18),
        ];
    }
}
