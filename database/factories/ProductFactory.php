<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(3, true)).' Cake';

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'description' => fake()->paragraph(),
            'base_price' => fake()->numberBetween(700, 3500),
            'is_eggless' => fake()->boolean(30),
            'allows_message' => true,
            'message_max_length' => 40,
            'is_featured' => fake()->boolean(20),
            'is_active' => true,
        ];
    }
}
