<?php

use App\Enums\DeliveryMethod;
use App\Models\Category;
use App\Models\DeliveryArea;
use App\Models\Order;
use App\Models\Product;
use Livewire\Livewire;

test('a self pickup order has no delivery fee or delivery area', function () {
    $product = Product::factory()->for(Category::factory())->create(['base_price' => 900]);
    $size = makeSizeValue($product);

    Livewire::test('pages::product.show', ['product' => $product])
        ->set("selectedOptionValueIds.{$size->product_option_type_id}", $size->id)
        ->call('addToCart');

    Livewire::test('pages::checkout')
        ->set('customerName', 'Sabina Rai')
        ->set('phone', '9812345678')
        ->set('deliveryMethod', 'pickup')
        ->set('deliveryDate', now()->addDay()->toDateString())
        ->set('deliveryTimeSlot', '10 AM - 12 PM')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder');

    $order = Order::first();

    expect($order->delivery_method)->toBe(DeliveryMethod::Pickup);
    expect($order->delivery_area_id)->toBeNull();
    expect($order->delivery_fee)->toBe(0);
    expect($order->total)->toBe(900);
    expect($order->delivery_address)->not->toBeEmpty();
});

test('a home delivery order still requires a delivery area', function () {
    $product = Product::factory()->for(Category::factory())->create(['base_price' => 900]);
    $size = makeSizeValue($product);

    Livewire::test('pages::product.show', ['product' => $product])
        ->set("selectedOptionValueIds.{$size->product_option_type_id}", $size->id)
        ->call('addToCart');

    Livewire::test('pages::checkout')
        ->set('customerName', 'Sabina Rai')
        ->set('phone', '9812345678')
        ->set('deliveryMethod', 'delivery')
        ->set('deliveryAddress', 'Traffic Chowk')
        ->set('deliveryDate', now()->addDay()->toDateString())
        ->set('deliveryTimeSlot', '10 AM - 12 PM')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder')
        ->assertHasErrors(['deliveryAreaId']);

    expect(Order::count())->toBe(0);
});

test('a home delivery order applies the delivery area fee', function () {
    $product = Product::factory()->for(Category::factory())->create(['base_price' => 900]);
    $size = makeSizeValue($product);
    $area = DeliveryArea::factory()->create(['fee' => 150]);

    Livewire::test('pages::product.show', ['product' => $product])
        ->set("selectedOptionValueIds.{$size->product_option_type_id}", $size->id)
        ->call('addToCart');

    Livewire::test('pages::checkout')
        ->set('customerName', 'Sabina Rai')
        ->set('phone', '9812345678')
        ->set('deliveryMethod', 'delivery')
        ->set('deliveryAddress', 'Traffic Chowk')
        ->set('deliveryAreaId', $area->id)
        ->set('deliveryDate', now()->addDay()->toDateString())
        ->set('deliveryTimeSlot', '10 AM - 12 PM')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder');

    $order = Order::first();

    expect($order->delivery_method)->toBe(DeliveryMethod::Delivery);
    expect($order->delivery_fee)->toBe(150);
    expect($order->total)->toBe(1050);
});
