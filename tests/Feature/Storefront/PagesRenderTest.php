<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductWeightOption;
use Database\Seeders\SettingSeeder;

beforeEach(function () {
    $this->seed(SettingSeeder::class);
});

test('home page renders', function () {
    $this->get('/')->assertOk();
});

test('shop page renders', function () {
    $this->get('/shop')->assertOk();
});

test('shop category page renders', function () {
    $category = Category::factory()->create();

    $this->get("/shop/{$category->slug}")->assertOk();
});

test('product page renders', function () {
    $product = Product::factory()->for(Category::factory())->create();
    ProductWeightOption::factory()->for($product)->create(['is_default' => true]);

    $this->get("/product/{$product->slug}")->assertOk();
});

test('empty cart page renders', function () {
    $this->get('/cart')->assertOk();
});

test('track order page renders', function () {
    $this->get('/track-order')->assertOk();
});

test('login and register pages render', function () {
    $this->get('/login')->assertOk();
    $this->get('/register')->assertOk();
});

test('account orders requires authentication', function () {
    $this->get('/account/orders')->assertRedirect('/login');
});
