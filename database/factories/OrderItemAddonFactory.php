<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItemAddon>
 */
class OrderItemAddonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory(),
            'addon_name' => fake()->randomElement(['Birthday Candles', 'Greeting Card', 'Chocolate Bar']),
            'addon_price' => fake()->numberBetween(50, 300),
        ];
    }
}
