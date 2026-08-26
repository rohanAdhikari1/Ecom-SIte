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
        $unitPrice = fake()->numberBetween(700, 3000);
        $quantity = fake()->numberBetween(1, 2);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => ucfirst(fake()->words(3, true)),
            'custom_field_value' => fake()->optional()->words(3, true),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'addons_total' => 0,
            'line_total' => $unitPrice * $quantity,
        ];
    }
}
