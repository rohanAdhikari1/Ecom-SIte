@props(['product'])

<a
    href="{{ route('product.show', $product) }}"
    wire:navigate
    class="group flex translate-y-0 flex-col overflow-hidden rounded-2xl border border-brand-100 bg-white opacity-100 shadow-sm transition-all duration-500 starting:translate-y-4 starting:opacity-0 hover:-translate-y-1 hover:shadow-xl hover:shadow-brand-900/10"
>
    <div class="relative aspect-square overflow-hidden bg-brand-50">
        @if ($product->cover_image)
            <img
                src="{{ $product->cover_image }}"
                alt="{{ $product->name }}"
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
            >
        @else
            <div class="flex h-full w-full items-center justify-center text-5xl">🎂</div>
        @endif

        @if ($product->is_featured)
            <span class="absolute left-3 top-3 rounded-full bg-rose-brand-500 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                Bestseller
            </span>
        @endif

        @if ($product->is_eggless)
            <span class="absolute right-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-medium text-brand-700 shadow-sm">
                Eggless
            </span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-4">
        <p class="text-xs font-medium uppercase tracking-wide text-brand-500">{{ $product->category->name }}</p>
        <h3 class="mt-1 font-display text-base font-semibold text-ink group-hover:text-brand-600">
            {{ $product->name }}
        </h3>
        <div class="mt-auto flex items-center justify-between pt-3">
            <span class="text-sm font-semibold text-brand-700">
                From {{ \App\Support\Money::format($product->base_price) }}
            </span>
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                </svg>
            </span>
        </div>
    </div>
</a>
