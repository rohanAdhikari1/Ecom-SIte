<?php

namespace App\Services\Payment;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * Handle payment initiation for a freshly created order.
     * Returns a redirect URL if the customer needs to be sent elsewhere
     * to complete payment, or null if nothing further is required.
     */
    public function handle(Order $order): ?string;
}
