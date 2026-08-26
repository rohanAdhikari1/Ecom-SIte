<?php

use App\Models\Addon;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOptionType;
use App\Models\ProductOptionValue;
use App\Services\Cart\CartService;
use Symfony\Component\HttpKernel\Exception\HttpException;

function makeProduct(): array
{
    $product = Product::factory()->for(Category::factory())->create(['base_price' => 1000]);

    $sizeType = ProductOptionType::factory()->for($product)->create(['name' => 'Size', 'is_required' => true]);
    $size = ProductOptionValue::factory()->for($sizeType, 'optionType')->create(['price_modifier' => 0, 'is_default' => true]);

    $flavorType = ProductOptionType::factory()->for($product)->create(['name' => 'Flavor', 'is_required' => false]);
    $flavor = ProductOptionValue::factory()->for($flavorType, 'optionType')->create(['price_modifier' => 150]);

    return [$product, $size, $flavor];
}

test('adding an item computes unit price from base price and selected options, with add-ons charged once per line', function () {
    [$product, $size, $flavor] = makeProduct();
    $addon = Addon::factory()->create(['price' => 100]);
    $product->addons()->attach($addon);

    $cart = new CartService;
    $cart->add($product, [$size->id, $flavor->id], [$addon->id], 'Happy Birthday', 2);

    $items = $cart->items();

    expect($items)->toHaveCount(1);
    expect($items->first()['unit_price'])->toBe(1000 + 150);
    expect($items->first()['addons_total'])->toBe(100);
    expect($items->first()['line_total'])->toBe((1000 + 150) * 2 + 100);
    expect($cart->count())->toBe(2);
    expect($cart->subtotal())->toBe((1000 + 150) * 2 + 100);
});

test('inactive addons are ignored when adding to cart', function () {
    [$product, $size, $flavor] = makeProduct();
    $inactiveAddon = Addon::factory()->create(['price' => 500, 'is_active' => false]);
    $product->addons()->attach($inactiveAddon);

    $cart = new CartService;
    $cart->add($product, [$size->id, $flavor->id], [$inactiveAddon->id], null, 1);

    expect($cart->items()->first()['unit_price'])->toBe(1000 + 150);
});

test('addons not offered on the product are ignored even if active', function () {
    [$product, $size, $flavor] = makeProduct();
    $unrelatedAddon = Addon::factory()->create(['price' => 999, 'is_active' => true]);

    $cart = new CartService;
    $cart->add($product, [$size->id, $flavor->id], [$unrelatedAddon->id], null, 1);

    expect($cart->items()->first()['unit_price'])->toBe(1000 + 150);
});

test('an option value belonging to a different product is rejected', function () {
    [$product] = makeProduct();
    [, $otherSize] = makeProduct();

    $cart = new CartService;

    expect(fn () => $cart->add($product, [$otherSize->id], [], null, 1))
        ->toThrow(HttpException::class);
});

test('updating quantity to zero removes the item', function () {
    [$product, $size, $flavor] = makeProduct();

    $cart = new CartService;
    $cart->add($product, [$size->id, $flavor->id], [], null, 1);
    $lineId = $cart->items()->first()['id'];

    $cart->updateQuantity($lineId, 0);

    expect($cart->isEmpty())->toBeTrue();
});

test('removing a line item leaves others intact', function () {
    [$product, $size, $flavor] = makeProduct();

    $cart = new CartService;
    $cart->add($product, [$size->id, $flavor->id], [], null, 1);
    $cart->add($product, [$size->id, $flavor->id], [], 'Second item', 1);

    $firstId = $cart->items()->first()['id'];
    $cart->remove($firstId);

    expect($cart->items())->toHaveCount(1);
    expect($cart->items()->first()['custom_field_value'])->toBe('Second item');
});
