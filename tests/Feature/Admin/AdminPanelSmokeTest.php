<?php

use App\Models\Addon;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(function () {
    $this->seed(RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('admin can view the dashboard', function () {
    $this->actingAs($this->admin)
        ->get('/crm')
        ->assertOk();
});

test('guests are redirected away from the admin panel', function () {
    $this->get('/crm')->assertRedirect('/crm/login');
});

test('admin can view every resource index page', function () {
    $paths = [
        'categories', 'products', 'addons', 'delivery-areas', 'orders', 'banners', 'testimonials',
    ];

    foreach ($paths as $path) {
        $this->actingAs($this->admin)
            ->get("/crm/{$path}")
            ->assertOk();
    }
});

test('admin can view the settings page', function () {
    $this->actingAs($this->admin)
        ->get('/crm/manage-settings')
        ->assertOk();
});

test('staff cannot view the settings page', function () {
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $this->actingAs($staff)
        ->get('/crm/manage-settings')
        ->assertForbidden();
});

test('admin can view and edit a product with its relations', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();
    $sizeType = $product->optionTypes()->create(['name' => 'Size', 'sort_order' => 0, 'is_required' => true]);
    $sizeType->values()->create(['label' => '1 lb', 'price_modifier' => 0, 'sort_order' => 0, 'is_default' => true]);
    $addon = Addon::factory()->create();
    $product->addons()->attach($addon);

    $this->actingAs($this->admin)
        ->get("/crm/products/{$product->id}/edit")
        ->assertOk();
});

test('admin can view an order with its items', function () {
    $order = Order::factory()->create();
    $order->items()->create([
        'product_id' => null,
        'product_name' => 'Test Cake',
        'quantity' => 1,
        'unit_price' => 800,
        'line_total' => 800,
    ]);

    $this->actingAs($this->admin)
        ->get("/crm/orders/{$order->id}/edit")
        ->assertOk();
});

test('admin can view the order detail page with map, items, and status history', function () {
    $order = Order::factory()->create(['delivery_lat' => 26.47, 'delivery_lng' => 87.27]);
    $orderItem = $order->items()->create([
        'product_id' => null,
        'product_name' => 'Test Cake',
        'quantity' => 1,
        'unit_price' => 800,
        'line_total' => 800,
    ]);
    $orderItem->addons()->create(['addon_name' => 'Birthday Candles', 'addon_price' => 50]);

    $this->actingAs($this->admin)
        ->get("/crm/orders/{$order->id}")
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('Test Cake')
        ->assertSee('Birthday Candles');
});
