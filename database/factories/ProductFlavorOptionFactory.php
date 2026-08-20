<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductFlavorOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductFlavorOption>
 */
class ProductFlavorOptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['Chocolate', 'Vanilla', 'Red Velvet', 'Butterscotch', 'Black Forest', 'Mango']),
            'price_modifier' => fake()->randomElement([0, 0, 0, 100, 150, 200]),
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}
