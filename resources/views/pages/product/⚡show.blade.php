<?php

use App\Models\Addon;
use App\Models\Product;
use App\Services\Cart\CartService;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public Product $product;

    public ?int $weightOptionId = null;

    public ?int $flavorOptionId = null;

    /** @var array<int> */
    public array $selectedAddonIds = [];

    public ?string $message = null;

    public int $quantity = 1;

    public function mount(Product $product): void
    {
        $this->product = $product->load(['weightOptions', 'flavorOptions', 'addons']);

        $this->weightOptionId = $this->product->defaultWeightOption?->id;
        $this->flavorOptionId = $this->product->flavorOptions->first()?->id;
    }

    #[Computed]
    public function unitPrice(): int
    {
        $weightPrice = $this->product->weightOptions->firstWhere('id', $this->weightOptionId)?->price ?? 0;
        $flavorModifier = $this->product->flavorOptions->firstWhere('id', $this->flavorOptionId)?->price_modifier ?? 0;
        $addonsTotal = $this->product->addons->whereIn('id', $this->selectedAddonIds)->sum('price');

        return $weightPrice + $flavorModifier + $addonsTotal;
    }

    #[Computed]
    public function totalPrice(): int
    {
        return $this->unitPrice * $this->quantity;
    }

    public function addToCart(CartService $cart): void
    {
        $this->validate([
            'weightOptionId' => 'required',
            'quantity' => 'required|integer|min:1|max:20',
            'message' => 'nullable|string|max:' . $this->product->message_max_length,
        ]);

        $weightOption = $this->product->weightOptions()->whereKey($this->weightOptionId)->firstOrFail();
        $flavorOption = $this->flavorOptionId
            ? $this->product->flavorOptions()->whereKey($this->flavorOptionId)->first()
            : null;

        $cart->add($this->product, $weightOption, $flavorOption, $this->selectedAddonIds, $this->product->allows_message ? $this->message : null, $this->quantity);

        $this->dispatch('cart-updated');

        session()->flash('success', $this->product->name . ' added to your cart.');

        $this->redirect(route('cart'), navigate: true);
    }
};
?>

<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    {{-- Full breadcrumb trail on every screen size — horizontally scrollable
         with a hidden scrollbar and a fade mask so it never overflows or looks broken on mobile. --}}
    <nav aria-label="Breadcrumb" class="relative">
        <div class="no-scrollbar flex items-center gap-1.5 overflow-x-auto text-sm whitespace-nowrap text-ink/50">
            <a href="{{ route('shop') }}" wire:navigate class="shrink-0 rounded-full px-2.5 py-1 transition hover:bg-brand-50 hover:text-brand-600">
                Shop
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-ink/25" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
            <a href="{{ route('shop.category', $product->category) }}" wire:navigate
                class="shrink-0 rounded-full px-2.5 py-1 transition hover:bg-brand-50 hover:text-brand-600">
                {{ $product->category->name }}
            </a>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-ink/25" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
            <span class="shrink-0 rounded-full bg-brand-50 px-2.5 py-1 font-medium text-brand-700">{{ $product->name }}</span>
        </div>
        {{-- Fade mask hinting there's more to scroll, on narrow screens --}}
        <div class="pointer-events-none absolute inset-y-0 right-0 w-8 bg-linear-to-l from-cream to-transparent sm:hidden"></div>
    </nav>

    <div
        x-data="{ showSticky: false }"
        x-init="
            let observer = new IntersectionObserver(([entry]) => { showSticky = !entry.isIntersecting }, { rootMargin: '-72px 0px 0px 0px', threshold: 0 });
            observer.observe(document.getElementById('product-title'));
        "
    >
        {{-- Sticky mini bar — appears once the main image/details scroll out of view --}}
        <div
            x-show="showSticky"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-3"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-3"
            x-cloak
            class="sticky top-20 z-30"
        >
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 rounded-2xl border border-brand-100 bg-white/95 px-4 py-3 shadow-lg shadow-brand-900/10 backdrop-blur">
                <div class="flex min-w-0 items-center gap-3">
                    @if ($product->cover_image)
                        <img src="{{ $product->cover_image }}" alt="" class="h-10 w-10 shrink-0 rounded-lg object-cover">
                    @endif
                    <p class="truncate font-display text-sm font-semibold text-ink sm:text-base">{{ $product->name }}</p>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                    <span class="hidden font-semibold text-brand-700 sm:inline">{{ \App\Support\Money::format($this->totalPrice) }}</span>
                    <button
                        type="submit"
                        form="product-configurator-form"
                        wire:loading.attr="disabled"
                        wire:target="addToCart"
                        class="rounded-full bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow transition hover:scale-105 hover:bg-brand-700 disabled:opacity-60"
                    >
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>

    <div class="mt-6 grid gap-10 lg:grid-cols-2">
        {{-- Gallery --}}
        <div data-reveal x-data="{ active: 0, images: @js($product->gallery_images ?: [null]) }" class="flex flex-col gap-3">
            <div class="group aspect-square overflow-hidden rounded-3xl bg-brand-50 shadow-sm">
                <template x-if="images[active]">
                    <img :src="images[active]" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" :alt="@js($product->name)">
                </template>
                <template x-if="!images[active]">
                    <div class="flex h-full w-full items-center justify-center text-8xl">🎂</div>
                </template>
            </div>

            <div class="flex gap-2" x-show="images.length > 1">
                <template x-for="(image, index) in images" :key="index">
                    <button x-on:click="active = index"
                        :class="active === index ? 'border-brand-500 ring-2 ring-brand-200' : 'border-transparent hover:border-brand-200'"
                        class="h-16 w-16 shrink-0 overflow-hidden rounded-xl border-2 bg-brand-50 transition">
                        <img :src="image" class="h-full w-full object-cover">
                    </button>
                </template>
            </div>
        </div>

        {{-- Details / configurator --}}
        <div data-reveal data-reveal-delay="2">
            <p class="text-xs font-semibold tracking-wide text-brand-500 uppercase">{{ $product->category->name }}</p>
            <h1 id="product-title" class="mt-1 font-display text-3xl font-semibold text-ink sm:text-4xl">{{ $product->name }}</h1>

            <div class="mt-2 flex items-center gap-2">
                @if ($product->is_eggless)
                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700">Eggless</span>
                @endif
            </div>

            @if ($product->description)
                <p class="mt-4 text-ink/70">{{ $product->description }}</p>
            @endif

            <div class="mt-6 flex items-baseline gap-2 border-t border-b border-brand-100 py-4">
                <span class="text-3xl font-bold text-brand-700">{{ \App\Support\Money::format($this->unitPrice) }}</span>
            </div>

            <form id="product-configurator-form" wire:submit="addToCart" class="mt-6 flex flex-col gap-6">
                {{-- Weight --}}
                @if ($product->weightOptions->isNotEmpty())
                    <div>
                        <h3 class="text-sm font-semibold text-ink">Weight / Size</h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($product->weightOptions as $option)
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="weightOptionId" value="{{ $option->id }}"
                                        class="peer sr-only">
                                    <span
                                        class="block rounded-full border border-brand-200 px-4 py-2 text-sm transition peer-checked:border-brand-600 peer-checked:bg-brand-600 peer-checked:text-white hover:border-brand-400">
                                        {{ $option->label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('weightOptionId')
                            <p class="mt-1 text-xs text-rose-brand-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Flavor --}}
                @if ($product->flavorOptions->isNotEmpty())
                    <div>
                        <h3 class="text-sm font-semibold text-ink">Flavor</h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($product->flavorOptions as $option)
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model.live="flavorOptionId" value="{{ $option->id }}"
                                        class="peer sr-only">
                                    <span
                                        class="block rounded-full border border-brand-200 px-4 py-2 text-sm transition peer-checked:border-brand-600 peer-checked:bg-brand-600 peer-checked:text-white hover:border-brand-400">
                                        {{ $option->name }}
                                        @if ($option->price_modifier > 0)
                                            <span
                                                class="opacity-70">(+{{ \App\Support\Money::format($option->price_modifier) }})</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Add-ons --}}
                @if ($product->addons->isNotEmpty())
                    <div>
                        <h3 class="text-sm font-semibold text-ink">Add-ons</h3>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            @foreach ($product->addons as $addon)
                                <label
                                    class="flex cursor-pointer items-center gap-2 rounded-xl border border-brand-100 px-3 py-2 text-sm transition hover:border-brand-300">
                                    <input type="checkbox" wire:model.live="selectedAddonIds"
                                        value="{{ $addon->id }}"
                                        class="rounded border-brand-300 text-brand-600 focus:ring-brand-400">
                                    <span class="flex-1">{{ $addon->name }}</span>
                                    <span
                                        class="text-xs text-ink/50">+{{ \App\Support\Money::format($addon->price) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Message on cake --}}
                @if ($product->allows_message)
                    <div>
                        <h3 class="text-sm font-semibold text-ink">
                            Message on Cake <span class="font-normal text-ink/50">(optional)</span>
                        </h3>
                        <textarea wire:model="message" maxlength="{{ $product->message_max_length }}" rows="2"
                            placeholder="e.g. Happy Birthday!"
                            class="mt-2 w-full rounded-xl border border-brand-100 px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200"></textarea>
                        @error('message')
                            <p class="mt-1 text-xs text-rose-brand-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Quantity --}}
                <div>
                    <h3 class="text-sm font-semibold text-ink">Quantity</h3>
                    <div class="mt-2 inline-flex items-center rounded-full border border-brand-200">
                        <button type="button" wire:click="$set('quantity', {{ max(1, $quantity - 1) }})"
                            class="flex h-10 w-10 items-center justify-center text-lg text-ink/70 hover:text-brand-600">−</button>
                        <span class="w-10 text-center font-medium">{{ $quantity }}</span>
                        <button type="button" wire:click="$set('quantity', {{ $quantity + 1 }})"
                            class="flex h-10 w-10 items-center justify-center text-lg text-ink/70 hover:text-brand-600">+</button>
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="flex items-center justify-center gap-2 rounded-full bg-brand-600 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-brand-600/30 transition hover:scale-[1.02] hover:bg-brand-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="addToCart">
                        Add to Cart — {{ \App\Support\Money::format($this->totalPrice) }}
                    </span>
                    <span wire:loading wire:target="addToCart">Adding…</span>
                </button>
            </form>
        </div>
    </div>
    </div>
</div>
