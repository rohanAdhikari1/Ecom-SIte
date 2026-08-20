<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductWeightOption;
use App\Services\Cart\CartService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('a customer cannot add a cheaper product to the cart by submitting another product\'s weight option id', function () {
    $expensiveProduct = Product::factory()->for(Category::factory())->create();
    ProductWeightOption::factory()->for($expensiveProduct)->create(['price' => 6500, 'is_default' => true]);

    $cheapProduct = Product::factory()->for(Category::factory())->create();
    $cheapWeight = ProductWeightOption::factory()->for($cheapProduct)->create(['price' => 100]);

    expect(fn () => Livewire::test('pages::product.show', ['product' => $expensiveProduct])
        ->set('weightOptionId', $cheapWeight->id)
        ->call('addToCart'))
        ->toThrow(ModelNotFoundException::class);

    expect(app(CartService::class)->isEmpty())->toBeTrue();
});
