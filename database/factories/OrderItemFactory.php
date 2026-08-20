<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $weightPrice = fake()->numberBetween(700, 3000);
        $quantity = fake()->numberBetween(1, 2);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true).' Cake',
            'weight_label' => fake()->randomElement(['1 lb', '2 lb', '3 lb']),
            'weight_price' => $weightPrice,
            'flavor_label' => fake()->randomElement(['Chocolate', 'Vanilla', 'Red Velvet']),
            'flavor_price_modifier' => 0,
            'message_on_cake' => fake()->optional()->words(3, true),
            'quantity' => $quantity,
            'unit_price' => $weightPrice,
            'line_total' => $weightPrice * $quantity,
        ];
    }
}
