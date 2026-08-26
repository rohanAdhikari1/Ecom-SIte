<?php

use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $query = '';

    #[Computed]
    public function results(): Collection
    {
        if (mb_strlen(trim($this->query)) < 2) {
            return collect();
        }

        return Product::query()
            ->active()
            ->with('category')
            ->where('name', 'like', '%'.trim($this->query).'%')
            ->take(6)
            ->get();
    }

    public function goToShop(): void
    {
        $this->redirect(route('shop', ['search' => $this->query]), navigate: true);
    }
};
?>

<div x-data="{ open: false }" x-on:keydown.escape.window="open = false" class="relative">
    <button
        x-on:click="open = !open; $nextTick(() => open && $refs.searchInput.focus())"
        class="inline-flex items-center justify-center rounded-full p-2.5 text-ink transition hover:bg-brand-50 hover:text-brand-600"
        aria-label="Search"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-on:click.outside="open = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="fixed inset-x-4 top-20 z-50 sm:absolute sm:inset-x-auto sm:top-full sm:right-0 sm:mt-2 sm:w-96"
    >
        <div class="overflow-hidden rounded-2xl border border-brand-100 bg-white shadow-2xl shadow-brand-950/10">
            <form wire:submit="goToShop" class="flex items-center gap-2 border-b border-brand-50 px-4 py-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-ink/40" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    x-ref="searchInput"
                    type="search"
                    wire:model.live.debounce.300ms="query"
                    placeholder="Search for products…"
                    class="w-full border-0 p-0 text-sm text-ink placeholder:text-ink/40 focus:outline-none focus:ring-0"
                >
            </form>

            <div class="max-h-96 overflow-y-auto">
                @if (mb_strlen(trim($query)) < 2)
                    <p class="px-4 py-6 text-center text-sm text-ink/50">Start typing to search products…</p>
                @elseif ($this->results->isEmpty())
                    <p class="px-4 py-6 text-center text-sm text-ink/50">No products found for "{{ $query }}".</p>
                @else
                    <ul class="divide-y divide-brand-50">
                        @foreach ($this->results as $product)
                            <li>
                                <a
                                    href="{{ route('product.show', $product) }}"
                                    wire:navigate
                                    x-on:click="open = false"
                                    class="flex items-center gap-3 px-4 py-3 transition hover:bg-brand-50"
                                >
                                    <span class="h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-brand-50">
                                        @if ($product->cover_image)
                                            <img src="{{ $product->cover_image }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                        @endif
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-ink">{{ $product->name }}</span>
                                        <span class="block text-xs text-ink/50">{{ $product->category->name }}</span>
                                    </span>
                                    <span class="shrink-0 text-sm font-semibold text-brand-700">{{ \App\Support\Money::format($product->base_price) }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <button
                        wire:click="goToShop"
                        class="block w-full border-t border-brand-50 px-4 py-3 text-center text-sm font-medium text-brand-600 transition hover:bg-brand-50"
                    >
                        See all results for "{{ $query }}" →
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
