<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Real eSewa ePay v2 integration. Stamps a fresh transaction_uuid on the order
 * and hands back the internal redirect route that auto-submits the signed
 * payment form to eSewa — see EsewaClient for the request/response contract.
 */
class EsewaGateway implements PaymentGateway
{
    public function handle(Order $order): ?string
    {
        $order->update([
            'esewa_transaction_uuid' => (string) Str::uuid(),
        ]);

        return route('payment.esewa.redirect', $order);
    }
}
