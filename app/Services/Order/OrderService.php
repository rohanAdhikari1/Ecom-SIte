<?php

namespace App\Services\Order;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\DeliveryArea;
use App\Models\Order;
use App\Services\Cart\CartService;
use App\Services\Payment\CodGateway;
use App\Services\Payment\EsewaGateway;
use App\Services\Payment\PaymentGateway;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(protected CartService $cart) {}

    /**
     * @param  array<string, mixed>  $customer
     */
    public function createFromCart(array $customer, PaymentMethod $paymentMethod): Order
    {
        $items = $this->cart->items();

        $deliveryMethod = $customer['delivery_method'] ?? DeliveryMethod::Delivery;
        $isPickup = $deliveryMethod === DeliveryMethod::Pickup;

        $deliveryArea = (! $isPickup && isset($customer['delivery_area_id']))
            ? DeliveryArea::query()->find($customer['delivery_area_id'])
            : null;

        $subtotal = (int) $items->sum('line_total');
        $deliveryFee = $isPickup ? 0 : ($deliveryArea?->fee ?? 0);

        $order = DB::transaction(function () use ($items, $customer, $deliveryMethod, $deliveryArea, $paymentMethod, $subtotal, $deliveryFee) {
            $order = Order::create([
                'user_id' => $customer['user_id'] ?? null,
                'delivery_method' => $deliveryMethod,
                'customer_name' => $customer['customer_name'],
                'phone' => $customer['phone'],
                'email' => $customer['email'] ?? null,
                'delivery_address' => $customer['delivery_address'],
                'delivery_lat' => $customer['delivery_lat'] ?? null,
                'delivery_lng' => $customer['delivery_lng'] ?? null,
                'delivery_area_id' => $deliveryArea?->id,
                'delivery_date' => $customer['delivery_date'],
                'delivery_time_slot' => $customer['delivery_time_slot'],
                'payment_method' => $paymentMethod,
                'payment_status' => PaymentStatus::Pending,
                'status' => OrderStatus::Pending,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'notes' => $customer['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $orderItem = $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'weight_label' => $item['weight_label'],
                    'weight_price' => $item['weight_price'],
                    'flavor_label' => $item['flavor_label'],
                    'flavor_price_modifier' => $item['flavor_price_modifier'],
                    'message_on_cake' => $item['message_on_cake'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                ]);

                foreach ($item['addons'] as $addon) {
                    $orderItem->addons()->create([
                        'addon_name' => $addon['name'],
                        'addon_price' => $addon['price'],
                    ]);
                }
            }

            return $order;
        });

        $this->resolveGateway($paymentMethod)->handle($order);

        $this->cart->clear();

        return $order;
    }

    protected function resolveGateway(PaymentMethod $paymentMethod): PaymentGateway
    {
        return match ($paymentMethod) {
            PaymentMethod::Cod => new CodGateway,
            PaymentMethod::Esewa => new EsewaGateway,
        };
    }
}
