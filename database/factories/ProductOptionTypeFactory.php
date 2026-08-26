<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductOptionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductOptionType>
 */
class ProductOptionTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['Size', 'Color', 'Material', 'Style']),
            'sort_order' => fake()->numberBetween(0, 5),
            'is_required' => false,
        ];
    }
}
