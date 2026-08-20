<?php

use App\Models\Addon;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductFlavorOption;
use App\Models\ProductWeightOption;
use App\Services\Cart\CartService;

function makeProduct(): array
{
    $product = Product::factory()->for(Category::factory())->create();

    $weight = ProductWeightOption::factory()->for($product)->create(['price' => 1000]);
    $flavor = ProductFlavorOption::factory()->for($product)->create(['price_modifier' => 150]);

    return [$product, $weight, $flavor];
}

test('adding an item computes unit price from weight, flavor, and addons', function () {
    [$product, $weight, $flavor] = makeProduct();
    $addon = Addon::factory()->create(['price' => 100]);

    $cart = new CartService;
    $cart->add($product, $weight, $flavor, [$addon->id], 'Happy Birthday', 2);

    $items = $cart->items();

    expect($items)->toHaveCount(1);
    expect($items->first()['unit_price'])->toBe(1000 + 150 + 100);
    expect($items->first()['line_total'])->toBe((1000 + 150 + 100) * 2);
    expect($cart->count())->toBe(2);
    expect($cart->subtotal())->toBe((1000 + 150 + 100) * 2);
});

test('inactive addons are ignored when adding to cart', function () {
    [$product, $weight, $flavor] = makeProduct();
    $inactiveAddon = Addon::factory()->create(['price' => 500, 'is_active' => false]);

    $cart = new CartService;
    $cart->add($product, $weight, $flavor, [$inactiveAddon->id], null, 1);

    expect($cart->items()->first()['unit_price'])->toBe(1000 + 150);
});

test('updating quantity to zero removes the item', function () {
    [$product, $weight, $flavor] = makeProduct();

    $cart = new CartService;
    $cart->add($product, $weight, $flavor, [], null, 1);
    $lineId = $cart->items()->first()['id'];

    $cart->updateQuantity($lineId, 0);

    expect($cart->isEmpty())->toBeTrue();
});

test('removing a line item leaves others intact', function () {
    [$product, $weight, $flavor] = makeProduct();

    $cart = new CartService;
    $cart->add($product, $weight, $flavor, [], null, 1);
    $cart->add($product, $weight, $flavor, [], 'Second cake', 1);

    $firstId = $cart->items()->first()['id'];
    $cart->remove($firstId);

    expect($cart->items())->toHaveCount(1);
    expect($cart->items()->first()['message_on_cake'])->toBe('Second cake');
});
