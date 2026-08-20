<?php

use App\Enums\OrderStatus;
use App\Models\Addon;
use App\Models\Category;
use App\Models\DeliveryArea;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductFlavorOption;
use App\Models\ProductWeightOption;
use App\Models\Setting;
use Livewire\Livewire;

test('a guest can add a configured product to the cart from the product page', function () {
    $product = Product::factory()->for(Category::factory())->create(['allows_message' => true]);
    $weight = ProductWeightOption::factory()->for($product)->create(['price' => 900, 'is_default' => true]);
    $flavor = ProductFlavorOption::factory()->for($product)->create(['price_modifier' => 100]);
    $addon = Addon::factory()->create(['price' => 50]);
    $product->addons()->attach($addon);

    Livewire::test('pages::product.show', ['product' => $product])
        ->set('weightOptionId', $weight->id)
        ->set('flavorOptionId', $flavor->id)
        ->set('selectedAddonIds', [$addon->id])
        ->set('message', 'Happy Birthday!')
        ->set('quantity', 2)
        ->call('addToCart')
        ->assertRedirect(route('cart'));

    Livewire::test('pages::cart')
        ->assertSee($product->name)
        ->assertSee('Happy Birthday!');
});

test('a guest can complete checkout end to end and reach the confirmation page', function () {
    Setting::query()->create(['key' => 'esewa_enabled', 'value' => '1']);

    $product = Product::factory()->for(Category::factory())->create();
    $weight = ProductWeightOption::factory()->for($product)->create(['price' => 1200, 'is_default' => true]);
    $area = DeliveryArea::factory()->create(['fee' => 100]);

    Livewire::test('pages::product.show', ['product' => $product])
        ->set('weightOptionId', $weight->id)
        ->call('addToCart');

    $checkout = Livewire::test('pages::checkout')
        ->set('customerName', 'Sabina Rai')
        ->set('phone', '9812345678')
        ->set('deliveryAddress', 'Traffic Chowk, Biratnagar')
        ->set('deliveryAreaId', $area->id)
        ->set('deliveryDate', now()->addDay()->toDateString())
        ->set('deliveryTimeSlot', '10 AM - 12 PM')
        ->set('paymentMethod', 'cod')
        ->call('placeOrder');

    $order = Order::first();

    expect($order)->not->toBeNull();
    expect($order->total)->toBe(1300);

    $checkout->assertRedirect(route('order.confirmation', $order));

    Livewire::test('pages::order.confirmation', ['order' => $order])
        ->assertSee($order->order_number)
        ->assertSee('Sabina Rai');
});

test('a customer can track their order by number and phone', function () {
    $order = Order::factory()->create([
        'order_number' => 'BRT-TEST-1234',
        'phone' => '9800000000',
        'status' => OrderStatus::Preparing,
    ]);

    Livewire::test('pages::order.track')
        ->set('orderNumber', 'BRT-TEST-1234')
        ->set('phone', '9800000000')
        ->call('search')
        ->assertSee('Preparing');
});

test('tracking fails silently with wrong phone number', function () {
    Order::factory()->create([
        'order_number' => 'BRT-TEST-5678',
        'phone' => '9800000000',
    ]);

    Livewire::test('pages::order.track')
        ->set('orderNumber', 'BRT-TEST-5678')
        ->set('phone', '9899999999')
        ->call('search')
        ->assertSee('No order found');
});
