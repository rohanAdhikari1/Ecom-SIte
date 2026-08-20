<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductWeightOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductWeightOption>
 */
class ProductWeightOptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'label' => fake()->randomElement(['1 lb', '1.5 lb', '2 lb', '3 lb', '5 lb']),
            'price' => fake()->numberBetween(700, 4500),
            'sort_order' => fake()->numberBetween(0, 5),
            'is_default' => false,
        ];
    }
}
