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
        $words = fake()->unique()->words(3);
        $name = ucfirst(is_array($words) ? implode(' ', $words) : $words);

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 100000),
            'description' => fake()->paragraph(),
            'base_price' => fake()->numberBetween(700, 3500),
            'has_custom_field' => true,
            'custom_field_label' => 'Special Instructions',
            'custom_field_max_length' => 40,
            'is_featured' => fake()->boolean(20),
            'is_active' => true,
        ];
    }
}
