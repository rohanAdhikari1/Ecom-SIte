<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Home', 'Office', 'Other']),
            'address' => fake()->address(),
            'lat' => fake()->latitude(26.4, 26.5),
            'lng' => fake()->longitude(87.2, 87.3),
            'is_default' => false,
        ];
    }
}
