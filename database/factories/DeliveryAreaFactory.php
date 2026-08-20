<?php

namespace Database\Factories;

use App\Models\DeliveryArea;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryArea>
 */
class DeliveryAreaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->streetName(),
            'fee' => fake()->randomElement([0, 50, 100, 150]),
            'eta_text' => fake()->randomElement(['Same day', 'Next day', '2-3 hours']),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
