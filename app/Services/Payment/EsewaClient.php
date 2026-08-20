<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class EsewaClient
{
    public function __construct(
        protected string $productCode,
        protected string $secretKey,
        protected string $formUrl,
        protected string $statusUrl,
    ) {}

    public static function make(): self
    {
        return new self(
            config('services.esewa.product_code'),
            config('services.esewa.secret_key'),
            config('services.esewa.form_url'),
            config('services.esewa.status_url'),
        );
    }

    public function formUrl(): string
    {
        return $this->formUrl;
    }

    /**
     * @return array<string, string>
     */
    public function buildPayload(Order $order): array
    {
        $signed = [
            'total_amount' => (string) $order->total,
            'transaction_uuid' => (string) $order->esewa_transaction_uuid,
            'product_code' => $this->productCode,
        ];

        return [
            'amount' => (string) $order->subtotal,
            'tax_amount' => '0',
            'product_service_charge' => '0',
            'product_delivery_charge' => (string) $order->delivery_fee,
            'total_amount' => $signed['total_amount'],
            'transaction_uuid' => $signed['transaction_uuid'],
            'product_code' => $signed['product_code'],
            'success_url' => route('payment.esewa.success', $order),
            'failure_url' => route('payment.esewa.failure', $order),
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
            'signature' => $this->sign($signed),
        ];
    }

    /**
     * @param  array<string, string>  $fields  in the exact order they should be signed
     */
    public function sign(array $fields): string
    {
        $message = collect($fields)
            ->map(fn($value, $key) => "{$key}={$value}")
            ->implode(',');

        return base64_encode(hash_hmac('sha256', $message, $this->secretKey, true));
    }

    /**
     * Verify a callback's signature using the field list/order it declares itself,
     * with a constant-time comparison.
     *
     * @param  array<string, mixed>  $data
     */
    public function verifyResponseSignature(array $data): bool
    {
        if (empty($data['signed_field_names']) || empty($data['signature'])) {
            return false;
        }

        $fields = [];

        foreach (explode(',', (string) $data['signed_field_names']) as $field) {
            if (! array_key_exists($field, $data)) {
                return false;
            }

            $fields[$field] = $data[$field];
        }

        return hash_equals($this->sign($fields), (string) $data['signature']);
    }

    /**
     * Server-to-server confirmation — never trust the browser redirect alone.
     *
     * @return array<string, mixed>
     */
    public function checkStatus(string $transactionUuid, int $totalAmount): array
    {
        $response = Http::timeout(10)->get($this->statusUrl, [
            'product_code' => $this->productCode,
            'total_amount' => $totalAmount,
            'transaction_uuid' => $transactionUuid,
        ]);

        return $response->successful() ? (array) $response->json() : [];
    }
}
