<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url]
    public ?string $category = null;

    #[Url]
    public string $search = '';

    #[Url]
    public string $sort = 'newest';

    public function mount(?Category $category = null): void
    {
        if ($category?->exists) {
            $this->category = $category->slug;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Product::query()->active()->with('category');

        if ($this->category) {
            $query->whereHas('category', fn (Builder $q) => $q->where('slug', $this->category));
        }

        if ($this->search !== '') {
            $query->where('name', 'like', "%{$this->search}%");
        }

        match ($this->sort) {
            'price_low' => $query->orderBy('base_price'),
            'price_high' => $query->orderByDesc('base_price'),
            default => $query->latest(),
        };

        return [
            'products' => $query->paginate(12),
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(),
        ];
    }
};
?>

<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div data-reveal class="flex flex-col gap-2">
        <p class="text-xs font-semibold tracking-widest text-brand-500 uppercase">Full Menu</p>
        <h1 class="font-display text-3xl font-semibold text-ink sm:text-4xl">Shop Our Cakes</h1>
        <p class="text-ink/60">Freshly baked, fully customisable, delivered across Biratnagar.</p>
    </div>

    <div class="mt-8 flex flex-col gap-6 lg:flex-row lg:items-start">
        {{-- Filters --}}
        <aside data-reveal class="flex flex-col gap-5 rounded-2xl border border-brand-100 bg-white p-5 shadow-sm lg:w-64 lg:shrink-0">
            <div class="relative">
                <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-ink/30" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    type="search"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Search cakes..."
                    class="w-full rounded-full border border-brand-100 bg-cream/60 py-2.5 pr-4 pl-10 text-sm focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-200"
                >
            </div>

            <div class="border-t border-brand-50 pt-5">
                <h3 class="text-sm font-semibold text-ink">Category</h3>
                <div class="mt-3 flex flex-col gap-1">
                    <button
                        wire:click="$set('category', null)"
                        class="rounded-lg px-3 py-1.5 text-left text-sm transition {{ $category === null ? 'bg-brand-100 font-medium text-brand-700' : 'text-ink/70 hover:bg-brand-50' }}"
                    >
                        All Cakes
                    </button>
                    @foreach ($categories as $cat)
                        <button
                            wire:click="$set('category', '{{ $cat->slug }}')"
                            class="rounded-lg px-3 py-1.5 text-left text-sm transition {{ $category === $cat->slug ? 'bg-brand-100 font-medium text-brand-700' : 'text-ink/70 hover:bg-brand-50' }}"
                        >
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-brand-50 pt-5">
                <h3 class="text-sm font-semibold text-ink">Sort By</h3>
                <select wire:model.live="sort" class="mt-3 w-full rounded-lg border border-brand-100 bg-white px-3 py-2 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                    <option value="newest">Newest</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                </select>
            </div>
        </aside>

        {{-- Products --}}
        <div class="flex-1">
            @if ($products->isEmpty())
                <div class="rounded-2xl border border-dashed border-brand-200 py-20 text-center text-ink/60">
                    No cakes found. Try a different search or category.
                </div>
            @else
                <div wire:loading.class="opacity-50" class="grid grid-cols-2 gap-4 transition-opacity sm:grid-cols-3 xl:grid-cols-4">
                    @foreach ($products as $product)
                        <div data-reveal data-reveal-delay="{{ min($loop->index % 4 + 1, 7) }}">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
