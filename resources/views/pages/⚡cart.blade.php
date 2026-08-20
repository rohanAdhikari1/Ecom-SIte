<?php

use App\Models\Setting;
use App\Services\Cart\CartService;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    public Collection $items;

    public int $subtotal = 0;

    public int $minOrderAmount = 0;

    public function mount(): void
    {
        $this->minOrderAmount = (int) Setting::get('min_order_amount', 0);
        $this->refreshCart();
    }

    public function updateQuantity(string $lineId, int $quantity, CartService $cart): void
    {
        $cart->updateQuantity($lineId, $quantity);
        $this->dispatch('cart-updated');
        $this->refreshCart();
    }

    public function remove(string $lineId, CartService $cart): void
    {
        $cart->remove($lineId);
        $this->dispatch('cart-updated');
        $this->refreshCart();
    }

    protected function refreshCart(): void
    {
        $cart = app(CartService::class);
        $this->items = $cart->items();
        $this->subtotal = $cart->subtotal();
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="font-display text-3xl font-semibold text-ink">Your Cart</h1>

    @if ($items->isEmpty())
        <div class="mt-10 rounded-2xl border border-dashed border-brand-200 py-20 text-center">
            <p class="text-5xl">🛒</p>
            <p class="mt-4 text-ink/60">Your cart is empty.</p>
            <a href="{{ route('shop') }}" wire:navigate class="mt-6 inline-flex rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:scale-105">
                Start Shopping
            </a>
        </div>
    @else
        <div class="mt-8 grid gap-8 lg:grid-cols-3">
            <div class="flex flex-col gap-4 lg:col-span-2">
                @foreach ($items as $item)
                    <div wire:key="{{ $item['id'] }}" class="flex gap-4 rounded-2xl border border-brand-100 bg-white p-4 shadow-sm">
                        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-xl bg-brand-50">
                            @if ($item['product_image'])
                                <img src="{{ $item['product_image'] }}" class="h-full w-full object-cover" alt="{{ $item['product_name'] }}">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-3xl">🎂</div>
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="font-display font-semibold text-ink">{{ $item['product_name'] }}</p>
                                    <p class="text-xs text-ink/60">
                                        {{ $item['weight_label'] }}
                                        @if ($item['flavor_label']) &middot; {{ $item['flavor_label'] }} @endif
                                    </p>
                                    @if (! empty($item['addons']))
                                        <p class="mt-1 text-xs text-ink/50">
                                            + {{ collect($item['addons'])->pluck('name')->implode(', ') }}
                                        </p>
                                    @endif
                                    @if ($item['message_on_cake'])
                                        <p class="mt-1 text-xs italic text-brand-600">"{{ $item['message_on_cake'] }}"</p>
                                    @endif
                                </div>
                                <button
                                    wire:click="remove('{{ $item['id'] }}')"
                                    class="text-ink/40 transition hover:text-rose-brand-600"
                                    aria-label="Remove item"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>

                            <div class="mt-auto flex items-center justify-between pt-3">
                                <div class="inline-flex items-center rounded-full border border-brand-200">
                                    <button
                                        wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})"
                                        class="flex h-8 w-8 items-center justify-center text-ink/70 hover:text-brand-600"
                                    >−</button>
                                    <span class="w-8 text-center text-sm font-medium">{{ $item['quantity'] }}</span>
                                    <button
                                        wire:click="updateQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})"
                                        class="flex h-8 w-8 items-center justify-center text-ink/70 hover:text-brand-600"
                                    >+</button>
                                </div>
                                <span class="font-semibold text-brand-700">{{ \App\Support\Money::format($item['line_total']) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Summary --}}
            <div class="h-fit rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="font-display text-lg font-semibold text-ink">Order Summary</h2>
                <div class="mt-4 flex justify-between text-sm text-ink/70">
                    <span>Subtotal</span>
                    <span>{{ \App\Support\Money::format($subtotal) }}</span>
                </div>
                <p class="mt-1 text-xs text-ink/50">Delivery fee is calculated at checkout based on your area.</p>

                @if ($minOrderAmount > 0 && $subtotal < $minOrderAmount)
                    <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-700">
                        Minimum order amount is {{ \App\Support\Money::format($minOrderAmount) }}. Add {{ \App\Support\Money::format($minOrderAmount - $subtotal) }} more to checkout.
                    </p>
                @else
                    <a
                        href="{{ route('checkout') }}"
                        wire:navigate
                        class="mt-5 flex w-full items-center justify-center rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-brand-600/30 transition hover:scale-[1.02] hover:bg-brand-700"
                    >
                        Proceed to Checkout
                    </a>
                @endif

                <a href="{{ route('shop') }}" wire:navigate class="mt-3 flex w-full items-center justify-center text-sm font-medium text-brand-600 hover:text-brand-700">
                    Continue Shopping
                </a>
            </div>
        </div>
    @endif
</div>
