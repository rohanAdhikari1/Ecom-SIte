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
    <div class="flex flex-col gap-2">
        <h1 class="font-display text-3xl font-semibold text-ink">Shop Our Cakes</h1>
        <p class="text-ink/60">Freshly baked, fully customisable, delivered across Biratnagar.</p>
    </div>

    <div class="mt-8 flex flex-col gap-4 lg:flex-row lg:items-start">
        {{-- Filters --}}
        <aside class="flex flex-col gap-6 lg:w-64 lg:shrink-0">
            <div>
                <input
                    type="search"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Search cakes..."
                    class="w-full rounded-full border border-brand-100 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200"
                >
            </div>

            <div>
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

            <div>
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
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
