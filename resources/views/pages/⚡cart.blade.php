<?php

use App\Models\Product;
use App\Models\Setting;
use App\Services\Cart\CartService;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    public Collection $items;

    public int $subtotal = 0;

    public int $minOrderAmount = 0;

    /** @var array<int, \Illuminate\Support\Collection> */
    public array $availableAddonsByProduct = [];

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

    public function toggleAddon(string $lineId, int $addonId, CartService $cart): void
    {
        $cart->toggleAddon($lineId, $addonId);
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

        $productIds = $this->items->pluck('product_id')->filter()->unique();

        $this->availableAddonsByProduct = Product::query()
            ->whereIn('id', $productIds)
            ->with(['addons' => fn ($query) => $query->active()])
            ->get()
            ->keyBy('id')
            ->map(fn (Product $product) => $product->addons)
            ->all();
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
                                <div class="flex h-full w-full items-center justify-center text-3xl">🛍️</div>
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <p class="font-display font-semibold text-ink">{{ $item['product_name'] }}</p>
                                    <p class="text-xs text-ink/60">
                                        {{ collect($item['options'])->pluck('value_label')->implode(' · ') }}
                                    </p>

                                    @php($availableAddons = $availableAddonsByProduct[$item['product_id']] ?? collect())
                                    @if ($availableAddons->isNotEmpty())
                                        <div x-data="{ open: false }" class="mt-2">
                                            @if (! empty($item['addons']))
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    @foreach ($item['addons'] as $addon)
                                                        <button
                                                            type="button"
                                                            wire:key="{{ $item['id'] }}-chip-{{ $addon['id'] }}"
                                                            wire:click="toggleAddon('{{ $item['id'] }}', {{ $addon['id'] }})"
                                                            class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700 transition hover:bg-accent-50 hover:text-accent-600"
                                                        >
                                                            {{ $addon['name'] }}
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <button
                                                type="button"
                                                x-on:click="open = !open"
                                                class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-brand-600 transition hover:text-brand-700"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200" :class="open && 'rotate-45'" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                                </svg>
                                                <span x-text="open ? 'Close' : 'Add extras'"></span>
                                            </button>

                                            <div
                                                x-show="open"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 -translate-y-1"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                x-cloak
                                                class="mt-2 grid grid-cols-1 gap-2 rounded-xl border border-brand-100 bg-cream/60 p-2.5 sm:grid-cols-2"
                                            >
                                                @foreach ($availableAddons as $addon)
                                                    @php($isSelected = collect($item['addons'])->contains('id', $addon->id))
                                                    <label
                                                        wire:key="{{ $item['id'] }}-avail-{{ $addon->id }}"
                                                        class="flex cursor-pointer items-center gap-2 rounded-lg border bg-white px-3 py-2 text-sm transition hover:border-brand-300 {{ $isSelected ? 'border-brand-400 bg-brand-50' : 'border-brand-100' }}"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            wire:click="toggleAddon('{{ $item['id'] }}', {{ $addon->id }})"
                                                            @checked($isSelected)
                                                            class="rounded border-brand-300 text-brand-600 focus:ring-brand-400"
                                                        >
                                                        <span class="flex-1 text-ink/80">{{ $addon->name }}</span>
                                                        <span class="text-xs text-ink/50">+{{ \App\Support\Money::format($addon->price) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if ($item['custom_field_value'])
                                        <p class="mt-1 text-xs italic text-brand-600">"{{ $item['custom_field_value'] }}"</p>
                                    @endif
                                </div>
                                <button
                                    wire:click="remove('{{ $item['id'] }}')"
                                    class="text-ink/40 transition hover:text-accent-600"
                                    aria-label="Remove item"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>

                            @if (($item['addons_total'] ?? 0) > 0 && $item['quantity'] > 1)
                                <p class="mt-2 text-xs text-ink/40">
                                    {{ \App\Support\Money::format($item['unit_price']) }} × {{ $item['quantity'] }}
                                    + {{ \App\Support\Money::format($item['addons_total']) }} add-ons <span class="italic">(one-time)</span>
                                </p>
                            @endif

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
