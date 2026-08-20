<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Birthday Cakes', 'Wedding Cakes', 'Cupcakes', 'Cheesecakes',
            'Chocolate Cakes', 'Fondant Cakes', 'Photo Cakes', 'Pastries',
        ]).' '.fake()->unique()->numberBetween(1, 10000);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }
}
