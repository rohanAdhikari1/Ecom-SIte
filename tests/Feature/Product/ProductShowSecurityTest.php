<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOptionType;
use App\Models\ProductOptionValue;
use App\Services\Cart\CartService;
use Livewire\Livewire;

test('a customer cannot add a cheaper product to the cart by submitting another product\'s option value id', function () {
    $expensiveProduct = Product::factory()->for(Category::factory())->create();
    $expensiveType = ProductOptionType::factory()->for($expensiveProduct)->create(['is_required' => true]);
    ProductOptionValue::factory()->for($expensiveType, 'optionType')->create(['price_modifier' => 5500, 'is_default' => true]);

    $cheapProduct = Product::factory()->for(Category::factory())->create();
    $cheapType = ProductOptionType::factory()->for($cheapProduct)->create(['is_required' => true]);
    $cheapValue = ProductOptionValue::factory()->for($cheapType, 'optionType')->create(['price_modifier' => 0]);

    Livewire::test('pages::product.show', ['product' => $expensiveProduct])
        ->set("selectedOptionValueIds.{$expensiveType->id}", $cheapValue->id)
        ->call('addToCart')
        ->assertStatus(422);

    expect(app(CartService::class)->isEmpty())->toBeTrue();
});
