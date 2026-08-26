<?php

use App\Models\Order;
use Livewire\Component;

new class extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        $this->order = $order->load(['items.addons', 'items.options', 'deliveryArea']);
    }
};
?>

<div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
    <div
        x-data="{ show: false }"
        x-init="setTimeout(() => show = true, 100)"
        x-show="show"
        x-transition:enter="transition ease-out duration-700"
        x-transition:enter-start="opacity-0 scale-75"
        x-transition:enter-end="opacity-100 scale-100"
        class="text-center"
    >
        <div class="relative mx-auto flex h-24 w-24 items-center justify-center">
            <span class="absolute inset-0 animate-ping rounded-full bg-brand-400/30"></span>
            <span class="relative flex h-24 w-24 items-center justify-center rounded-full bg-linear-to-br from-brand-500 to-brand-700 shadow-lg shadow-brand-600/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-11 w-11 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </span>
        </div>
        <h1 class="mt-6 font-display text-3xl font-semibold text-ink">Thank you, {{ $order->customer_name }}!</h1>
        <p class="mt-2 text-ink/60">Your order has been placed successfully.</p>

        <div class="mt-6 inline-flex flex-col items-center gap-1 rounded-2xl bg-brand-50 px-6 py-4">
            <span class="text-xs uppercase tracking-wide text-brand-600">Order Number</span>
            <span class="font-display text-xl font-semibold text-brand-700">{{ $order->order_number }}</span>
        </div>
    </div>

    @if (session('esewaError'))
        <div class="mt-8 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <div class="flex-1 text-sm">
                <p class="font-medium text-amber-900">{{ session('esewaError') }}</p>
                @if ($order->payment_method === \App\Enums\PaymentMethod::Esewa && $order->payment_status !== \App\Enums\PaymentStatus::Paid)
                    <a
                        href="{{ route('payment.esewa.retry', $order) }}"
                        class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-amber-800 hover:text-amber-900"
                    >
                        Retry eSewa payment →
                    </a>
                @endif
            </div>
        </div>
    @endif

    <div class="mt-10 rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
        <h2 class="font-display font-semibold text-ink">Order Details</h2>

        <div class="mt-4 flex flex-col gap-3">
            @foreach ($order->items as $item)
                <div class="flex justify-between border-b border-brand-50 pb-3 text-sm last:border-0 last:pb-0">
                    <div>
                        <p class="font-medium text-ink">{{ $item->quantity }} × {{ $item->product_name }}</p>
                        <p class="text-xs text-ink/60">
                            {{ $item->options->pluck('option_value_label')->implode(' · ') }}
                        </p>
                        @if ($item->addons->isNotEmpty())
                            <p class="text-xs text-ink/50">+ {{ $item->addons->pluck('addon_name')->implode(', ') }}</p>
                        @endif
                        @if ($item->custom_field_value)
                            <p class="text-xs italic text-brand-600">"{{ $item->custom_field_value }}"</p>
                        @endif
                    </div>
                    <span class="font-medium text-ink">{{ \App\Support\Money::format($item->line_total) }}</span>
                </div>
            @endforeach
        </div>

        <div class="mt-4 space-y-1.5 border-t border-brand-100 pt-4 text-sm">
            <div class="flex justify-between text-ink/70">
                <span>Subtotal</span>
                <span>{{ \App\Support\Money::format($order->subtotal) }}</span>
            </div>
            <div class="flex justify-between text-ink/70">
                <span>Delivery Fee</span>
                <span>{{ \App\Support\Money::format($order->delivery_fee) }}</span>
            </div>
            <div class="flex justify-between text-base font-semibold text-ink">
                <span>Total</span>
                <span>{{ \App\Support\Money::format($order->total) }}</span>
            </div>
        </div>

        <div class="mt-5 grid gap-4 border-t border-brand-100 pt-4 text-sm sm:grid-cols-2">
            <div class="flex gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink/40">Delivery Address</p>
                    <p class="mt-1 text-ink/80">{{ $order->delivery_address }}</p>
                    @if ($order->deliveryArea)
                        <p class="text-ink/60">{{ $order->deliveryArea->name }}</p>
                    @endif
                    @if ($order->delivery_lat && $order->delivery_lng)
                        <a
                            href="https://www.google.com/maps/search/?api=1&query={{ $order->delivery_lat }},{{ $order->delivery_lng }}"
                            target="_blank"
                            rel="noopener"
                            class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-brand-600 hover:text-brand-700"
                        >
                            View pinned location on map →
                        </a>
                    @endif
                </div>
            </div>
            <div class="flex gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink/40">Delivery Time</p>
                    <p class="mt-1 text-ink/80">{{ $order->delivery_date->format('D, j M Y') }}</p>
                    <p class="text-ink/60">{{ $order->delivery_time_slot }}</p>
                </div>
            </div>
            <div class="flex gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-brand-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3M3.75 6h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z" />
                </svg>
                <div>
                    <p class="text-xs uppercase tracking-wide text-ink/40">Payment Method</p>
                    <p class="mt-1 text-ink/80">
                        {{ $order->payment_method->getLabel() }}
                        @if ($order->payment_method === \App\Enums\PaymentMethod::Esewa)
                            <span class="ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $order->payment_status === \App\Enums\PaymentStatus::Paid ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $order->payment_status->getLabel() }}
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
        <a href="{{ route('order.track') }}" wire:navigate class="inline-flex items-center justify-center rounded-full border border-brand-200 px-6 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
            Track This Order
        </a>
        <a href="{{ route('shop') }}" wire:navigate class="inline-flex items-center justify-center rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:scale-105">
            Continue Shopping
        </a>
    </div>
</div>
