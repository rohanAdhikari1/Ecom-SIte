<?php

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

test('order status pipeline advances in the expected order', function () {
    expect(OrderStatus::Pending->next())->toBe(OrderStatus::Confirmed);
    expect(OrderStatus::Confirmed->next())->toBe(OrderStatus::Preparing);
    expect(OrderStatus::Preparing->next())->toBe(OrderStatus::OutForDelivery);
    expect(OrderStatus::OutForDelivery->next())->toBe(OrderStatus::Delivered);
    expect(OrderStatus::Delivered->next())->toBeNull();
    expect(OrderStatus::Cancelled->next())->toBeNull();
});

test('admin can advance an order to the next status with one click', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    Livewire::actingAs($this->admin)
        ->test(ListOrders::class)
        ->callTableAction('advance', $order);

    expect($order->fresh()->status)->toBe(OrderStatus::Confirmed);
    expect($order->fresh()->statusHistories)->toHaveCount(2);
});

test('the advance action is hidden once an order is delivered', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Delivered]);

    Livewire::actingAs($this->admin)
        ->test(ListOrders::class, ['activeTab' => 'all'])
        ->assertTableActionHidden('advance', $order);
});
