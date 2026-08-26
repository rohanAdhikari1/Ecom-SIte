<?php

namespace Database\Factories;

use App\Models\ProductBadge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductBadge>
 */
class ProductBadgeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['New', 'Bestseller', 'Limited Edition', 'Handmade']),
            'color' => 'brand',
        ];
    }
}
