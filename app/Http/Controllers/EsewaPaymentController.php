<?php

namespace App\Http\Controllers;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\Payment\EsewaClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EsewaPaymentController extends Controller
{
    public function redirect(Order $order): View
    {
        abort_unless(
            $order->payment_method === PaymentMethod::Esewa && $order->esewa_transaction_uuid,
            404
        );

        $client = EsewaClient::make();

        return view('payment.esewa-redirect', [
            'formUrl' => $client->formUrl(),
            'payload' => $client->buildPayload($order),
        ]);
    }

    /**
     * Regenerates a fresh transaction_uuid and re-enters the redirect flow —
     * used when a customer's previous eSewa attempt failed or was cancelled.
     */
    public function retry(Order $order): RedirectResponse
    {
        abort_unless($order->payment_method === PaymentMethod::Esewa, 404);

        if ($order->payment_status !== PaymentStatus::Paid) {
            $order->update(['esewa_transaction_uuid' => (string) Str::uuid()]);
        }

        return redirect()->route('payment.esewa.redirect', $order);
    }

    public function success(Request $request, Order $order): RedirectResponse
    {
        $client = EsewaClient::make();
        $decoded = $this->decode($request->query('data'));

        $verified = $decoded
            && ($decoded['transaction_uuid'] ?? null) === $order->esewa_transaction_uuid
            && ($decoded['status'] ?? null) === 'COMPLETE'
            && $client->verifyResponseSignature($decoded);

        $refId = $decoded['transaction_code'] ?? null;

        if ($verified) {
            // Never trust the browser redirect alone — confirm server-to-server too.
            $status = $client->checkStatus((string) $order->esewa_transaction_uuid, $order->total);
            $verified = ($status['status'] ?? null) === 'COMPLETE';
            $refId ??= $status['ref_id'] ?? null;
        }

        if ($verified && $order->payment_status !== PaymentStatus::Paid) {
            $order->update([
                'payment_status' => PaymentStatus::Paid,
                'esewa_ref_id' => $refId,
            ]);

            $order->statusHistories()->create([
                'status' => $order->status->value,
                'note' => 'Payment confirmed via eSewa.',
            ]);
        }

        if (! $verified) {
            return redirect()
                ->route('order.confirmation', $order)
                ->with('esewaError', 'We could not confirm your eSewa payment yet. If you were charged, please contact us with your order number.');
        }

        return redirect()->route('order.confirmation', $order);
    }

    public function failure(Request $request, Order $order): RedirectResponse
    {
        $decoded = $this->decode($request->query('data'));

        if ($order->payment_status === PaymentStatus::Pending) {
            $order->statusHistories()->create([
                'status' => $order->status->value,
                'note' => 'eSewa payment was not completed'.(isset($decoded['status']) ? " (status: {$decoded['status']})." : '.'),
            ]);
        }

        return redirect()
            ->route('order.confirmation', $order)
            ->with('esewaError', 'Your eSewa payment was not completed. You can retry payment or contact us to arrange Cash on Delivery.');
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function decode(?string $raw): ?array
    {
        if (! $raw) {
            return null;
        }

        $decoded = json_decode(base64_decode($raw, true) ?: '', true);

        return is_array($decoded) ? $decoded : null;
    }
}
