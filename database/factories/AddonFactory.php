<?php

namespace Database\Factories;

use App\Models\Addon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Addon>
 */
class AddonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Birthday Candles', 'Greeting Card', 'Chocolate Bar', 'Fresh Flowers Bunch',
                'Photo Print Topper', 'Sparkler Candle', 'Balloons Bundle', 'Wine Bottle (Non-Alcoholic)',
            ]),
            'price' => fake()->numberBetween(50, 500),
            'is_active' => true,
        ];
    }
}
