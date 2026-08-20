<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\DeliveryArea;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(700, 5000);
        $deliveryFee = fake()->randomElement([0, 50, 100]);

        return [
            'user_id' => null,
            'customer_name' => fake()->name(),
            'phone' => '98'.fake()->numerify('########'),
            'email' => fake()->optional()->safeEmail(),
            'delivery_address' => fake()->address(),
            'delivery_area_id' => DeliveryArea::factory(),
            'delivery_date' => fake()->dateTimeBetween('now', '+7 days'),
            'delivery_time_slot' => fake()->randomElement(['10 AM - 12 PM', '12 PM - 3 PM', '3 PM - 6 PM']),
            'payment_method' => PaymentMethod::Cod,
            'payment_status' => PaymentStatus::Pending,
            'status' => OrderStatus::Pending,
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $subtotal + $deliveryFee,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
