<?php

namespace Database\Factories;

use App\Models\ProductOptionType;
use App\Models\ProductOptionValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductOptionValue>
 */
class ProductOptionValueFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_option_type_id' => ProductOptionType::factory(),
            'label' => fake()->randomElement(['Small', 'Medium', 'Large', 'Red', 'Blue', 'Black']),
            'price_modifier' => fake()->randomElement([0, 0, 0, 100, 150, 200]),
            'sort_order' => fake()->numberBetween(0, 5),
            'is_default' => false,
        ];
    }
}
