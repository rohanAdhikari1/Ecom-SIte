<?php

namespace App\Services\Payment;

use App\Models\Order;

class CodGateway implements PaymentGateway
{
    public function handle(Order $order): ?string
    {
        // Nothing to do — payment is collected in person on delivery.
        // payment_status stays "pending" until the order is marked delivered.
        return null;
    }
}
