<?php

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOptionType;
use App\Models\ProductOptionValue;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use App\Services\Payment\EsewaClient;
use Illuminate\Support\Facades\Http;

function createEsewaOrder(): Order
{
    $product = Product::factory()->for(Category::factory())->create(['base_price' => 1500]);
    $type = ProductOptionType::factory()->for($product)->create(['is_required' => true]);
    $size = ProductOptionValue::factory()->for($type, 'optionType')->create(['price_modifier' => 0, 'is_default' => true]);

    $cart = new CartService;
    $cart->add($product, [$size->id], [], null, 1);

    return (new OrderService($cart))->createFromCart([
        'delivery_method' => DeliveryMethod::Pickup,
        'customer_name' => 'Sabina Rai',
        'phone' => '9812345678',
        'email' => null,
        'delivery_address' => 'BRT Live Cake, Bargachhi Chowk',
        'delivery_date' => now()->addDay()->toDateString(),
        'delivery_time_slot' => '10 AM - 12 PM',
        'notes' => null,
    ], PaymentMethod::Esewa);
}

test('choosing esewa stamps a transaction uuid on the order', function () {
    $order = createEsewaOrder();

    expect($order->esewa_transaction_uuid)->not->toBeNull();
    expect($order->payment_status)->toBe(PaymentStatus::Pending);
});

test('the redirect page renders a signed auto-submit form to esewa', function () {
    $order = createEsewaOrder();

    $response = $this->get(route('payment.esewa.redirect', $order));

    $response->assertOk();
    $response->assertSee(config('services.esewa.form_url'), false);
    $response->assertSee('name="transaction_uuid" value="'.$order->esewa_transaction_uuid.'"', false);
    $response->assertSee('name="signature"', false);
});

test('a verified success callback marks the order paid', function () {
    $order = createEsewaOrder();

    Http::fake([
        'rc.esewa.com.np/*' => Http::response([
            'product_code' => config('services.esewa.product_code'),
            'transaction_uuid' => $order->esewa_transaction_uuid,
            'total_amount' => $order->total,
            'status' => 'COMPLETE',
            'ref_id' => 'REF12345',
        ]),
    ]);

    $client = EsewaClient::make();
    $signed = [
        'total_amount' => (string) $order->total,
        'transaction_uuid' => (string) $order->esewa_transaction_uuid,
        'product_code' => config('services.esewa.product_code'),
    ];
    $data = [
        'transaction_code' => 'TXN123',
        'status' => 'COMPLETE',
        'total_amount' => $signed['total_amount'],
        'transaction_uuid' => $signed['transaction_uuid'],
        'product_code' => $signed['product_code'],
        'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        'signature' => $client->sign($signed),
    ];

    $response = $this->get(route('payment.esewa.success', $order).'?data='.base64_encode(json_encode($data)));

    $response->assertRedirect(route('order.confirmation', $order));
    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::Paid);
    expect($order->esewa_ref_id)->toBe('TXN123');
    expect($order->statusHistories->last()->note)->toBe('Payment confirmed via eSewa.');
});

test('a tampered success signature is rejected and the order stays unpaid', function () {
    $order = createEsewaOrder();

    $data = [
        'transaction_code' => 'TXN999',
        'status' => 'COMPLETE',
        'total_amount' => (string) $order->total,
        'transaction_uuid' => $order->esewa_transaction_uuid,
        'product_code' => config('services.esewa.product_code'),
        'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        'signature' => 'forged-signature==',
    ];

    $response = $this->get(route('payment.esewa.success', $order).'?data='.base64_encode(json_encode($data)));

    $response->assertRedirect(route('order.confirmation', $order));
    $response->assertSessionHas('esewaError');
    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::Pending);
});

test('a status check mismatch keeps the order unpaid even with a valid signature', function () {
    $order = createEsewaOrder();

    Http::fake([
        'rc.esewa.com.np/*' => Http::response(['status' => 'PENDING']),
    ]);

    $client = EsewaClient::make();
    $signed = [
        'total_amount' => (string) $order->total,
        'transaction_uuid' => (string) $order->esewa_transaction_uuid,
        'product_code' => config('services.esewa.product_code'),
    ];
    $data = [
        'transaction_code' => 'TXN777',
        'status' => 'COMPLETE',
        'total_amount' => $signed['total_amount'],
        'transaction_uuid' => $signed['transaction_uuid'],
        'product_code' => $signed['product_code'],
        'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        'signature' => $client->sign($signed),
    ];

    $response = $this->get(route('payment.esewa.success', $order).'?data='.base64_encode(json_encode($data)));

    $response->assertSessionHas('esewaError');
    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::Pending);
});

test('the failure callback records a status history note without marking the order paid', function () {
    $order = createEsewaOrder();

    $response = $this->get(route('payment.esewa.failure', $order));

    $response->assertRedirect(route('order.confirmation', $order));
    $response->assertSessionHas('esewaError');
    $order->refresh();
    expect($order->payment_status)->toBe(PaymentStatus::Pending);
    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->statusHistories->last()->note)->toContain('eSewa payment was not completed');
});

test('retrying issues a fresh transaction uuid and redirects back into the payment flow', function () {
    $order = createEsewaOrder();
    $originalUuid = $order->esewa_transaction_uuid;

    $response = $this->get(route('payment.esewa.retry', $order));

    $response->assertRedirect(route('payment.esewa.redirect', $order->fresh()));
    expect($order->fresh()->esewa_transaction_uuid)->not->toBe($originalUuid);
});
