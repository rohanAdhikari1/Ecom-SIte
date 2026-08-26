<?php

use App\Models\Category;
use App\Models\Product;
use Livewire\Livewire;

test('typing fewer than two characters shows no results', function () {
    Product::factory()->for(Category::factory())->create(['name' => 'Belgian Chocolate Truffle']);

    Livewire::test('search-bar')
        ->set('query', 'b')
        ->assertSee('Start typing to search products');
});

test('search finds matching active products by name', function () {
    Product::factory()->for(Category::factory())->create(['name' => 'Belgian Chocolate Truffle', 'is_active' => true]);
    Product::factory()->for(Category::factory())->create(['name' => 'Rainbow Sprinkle Cake', 'is_active' => true]);
    Product::factory()->for(Category::factory())->create(['name' => 'Belgian Cheesecake', 'is_active' => false]);

    Livewire::test('search-bar')
        ->set('query', 'Belgian')
        ->assertSee('Belgian Chocolate Truffle')
        ->assertDontSee('Rainbow Sprinkle Cake')
        ->assertDontSee('Belgian Cheesecake');
});

test('search shows an empty state when nothing matches', function () {
    Product::factory()->for(Category::factory())->create(['name' => 'Belgian Chocolate Truffle']);

    Livewire::test('search-bar')
        ->set('query', 'zzz-nonexistent')
        ->assertSee('No products found');
});

test('submitting the search redirects to the shop page with the query', function () {
    Livewire::test('search-bar')
        ->set('query', 'chocolate')
        ->call('goToShop')
        ->assertRedirect(route('shop', ['search' => 'chocolate']));
});
