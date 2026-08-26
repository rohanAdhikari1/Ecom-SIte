<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\DeliveryArea;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;

test('checkout creates an order with items, totals, and a tracking token', function () {
    $product = Product::factory()->for(Category::factory())->create(['base_price' => 1200]);
    $size = makeSizeValue($product);
    $area = DeliveryArea::factory()->create(['fee' => 100]);

    $cart = new CartService;
    $cart->add($product, [$size->id], [], 'Happy Birthday Sabina', 1);

    $order = (new OrderService($cart))->createFromCart([
        'customer_name' => 'Sabina Rai',
        'phone' => '9812345678',
        'email' => null,
        'delivery_address' => 'Traffic Chowk, Biratnagar',
        'delivery_area_id' => $area->id,
        'delivery_date' => now()->addDay()->toDateString(),
        'delivery_time_slot' => '10 AM - 12 PM',
        'notes' => null,
    ], PaymentMethod::Cod);

    expect($order->order_number)->not->toBeEmpty();
    expect($order->tracking_token)->not->toBeEmpty();
    expect($order->subtotal)->toBe(1200);
    expect($order->delivery_fee)->toBe(100);
    expect($order->total)->toBe(1300);
    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->payment_status)->toBe(PaymentStatus::Pending);
    expect($order->items)->toHaveCount(1);
    expect($order->items->first()->custom_field_value)->toBe('Happy Birthday Sabina');
});

test('checkout clears the cart afterwards', function () {
    $product = Product::factory()->for(Category::factory())->create();
    $size = makeSizeValue($product);

    $cart = new CartService;
    $cart->add($product, [$size->id], [], null, 1);

    (new OrderService($cart))->createFromCart([
        'customer_name' => 'Test Customer',
        'phone' => '9800000000',
        'delivery_address' => 'Biratnagar',
        'delivery_date' => now()->addDay()->toDateString(),
        'delivery_time_slot' => '10 AM - 12 PM',
    ], PaymentMethod::Cod);

    expect($cart->isEmpty())->toBeTrue();
});

test('order status history records the initial pending status', function () {
    $product = Product::factory()->for(Category::factory())->create();
    $size = makeSizeValue($product);

    $cart = new CartService;
    $cart->add($product, [$size->id], [], null, 1);

    $order = (new OrderService($cart))->createFromCart([
        'customer_name' => 'Test Customer',
        'phone' => '9800000000',
        'delivery_address' => 'Biratnagar',
        'delivery_date' => now()->addDay()->toDateString(),
        'delivery_time_slot' => '10 AM - 12 PM',
    ], PaymentMethod::Cod);

    expect($order->statusHistories)->toHaveCount(1);
    expect($order->statusHistories->first()->status)->toBe(OrderStatus::Pending);
});
