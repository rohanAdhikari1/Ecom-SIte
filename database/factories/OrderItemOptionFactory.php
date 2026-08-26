<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\OrderItemOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItemOption>
 */
class OrderItemOptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory(),
            'option_type_name' => 'Size',
            'option_value_label' => fake()->randomElement(['Small', 'Medium', 'Large']),
            'price_modifier' => fake()->numberBetween(0, 500),
        ];
    }
}
