<?php

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    public Collection $banners;

    public Collection $categories;

    public Collection $featuredProducts;

    public Collection $testimonials;

    public string $heroBadgeText;

    public string $storyHeading;

    public string $storyBody;

    public string $storyStat1Value;

    public string $storyStat1Label;

    public string $storyStat2Value;

    public string $storyStat2Label;

    /** @var array<int, array{icon: string, title: string, body: string}> */
    public array $whyChooseUs;

    public function mount(): void
    {
        $this->banners = Banner::query()->active()->orderBy('sort_order')->get();
        $this->categories = Category::query()->where('is_active', true)->orderBy('sort_order')->get();
        $this->featuredProducts = Product::query()->active()->featured()->with('category')->latest()->take(8)->get();
        $this->testimonials = Testimonial::query()->active()->orderBy('sort_order')->take(6)->get();

        $this->heroBadgeText = Setting::get('hero_badge_text', "Biratnagar's Own Cake Parlor");
        $this->storyHeading = Setting::get('story_heading', 'Crafted with Love, Baked with Care');
        $this->storyBody = Setting::get('story_body', '');
        $this->storyStat1Value = Setting::get('story_stat1_value', '500+');
        $this->storyStat1Label = Setting::get('story_stat1_label', 'Cakes delivered');
        $this->storyStat2Value = Setting::get('story_stat2_value', '4.9★');
        $this->storyStat2Label = Setting::get('story_stat2_label', 'Average rating');
        $this->whyChooseUs = json_decode(Setting::get('why_choose_us', '[]'), true) ?: [];
    }
};
?>

<div>
    {{-- Hero --}}
    <section
        x-data="{
            slide: 0,
            total: {{ $banners->count() }},
            next() { this.slide = (this.slide + 1) % this.total },
            timer: null,
        }"
        x-init="if (total > 1) { timer = setInterval(() => next(), 6000) }"
        class="relative isolate min-h-[86vh] overflow-hidden bg-ink sm:min-h-[92vh]"
    >
        @forelse ($banners as $index => $banner)
            <div
                x-show="slide === {{ $index }}"
                x-transition:enter="transition ease-out duration-1000"
                x-transition:enter-start="opacity-0 scale-105"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute inset-0"
                @if ($index > 0) style="display:none" @endif
            >
                @if ($banner->image)
                    <img
                        src="{{ $banner->image }}"
                        alt="{{ $banner->title }}"
                        class="h-full w-full object-cover"
                    >
                @else
                    <div class="h-full w-full bg-linear-to-br from-brand-700 via-brand-600 to-rose-brand-600"></div>
                @endif
                <div class="absolute inset-0 bg-linear-to-t from-ink/85 via-ink/40 to-ink/10"></div>
                <div class="absolute inset-0 bg-linear-to-r from-ink/70 via-ink/10 to-transparent"></div>
            </div>
        @empty
            <div class="absolute inset-0 bg-linear-to-br from-brand-700 via-brand-600 to-rose-brand-600"></div>
        @endforelse

        <div class="relative z-10 mx-auto flex min-h-[86vh] max-w-7xl flex-col justify-end px-4 pb-16 sm:min-h-[92vh] sm:px-6 sm:pb-24 lg:px-8">
            @forelse ($banners as $index => $banner)
                <div
                    x-show="slide === {{ $index }}"
                    x-transition:enter="transition ease-out duration-700 delay-200"
                    x-transition:enter-start="opacity-0 translate-y-6"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="max-w-2xl"
                    @if ($index > 0) style="display:none" @endif
                >
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold tracking-wide text-white/90 uppercase backdrop-blur">
                        {{ $heroBadgeText }}
                    </span>
                    <h1 class="mt-5 font-display text-4xl leading-[1.05] font-bold text-white text-shadow-lg sm:text-6xl lg:text-7xl">
                        {{ $banner->title }}
                    </h1>
                    @if ($banner->subtitle)
                        <p class="mt-5 max-w-lg text-lg text-white/85">{{ $banner->subtitle }}</p>
                    @endif
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        @if ($banner->cta_label)
                            <a
                                href="{{ $banner->cta_url ?? route('shop') }}"
                                wire:navigate
                                class="inline-flex items-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-semibold text-brand-700 shadow-xl transition hover:scale-105 hover:shadow-2xl"
                            >
                                {{ $banner->cta_label }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" />
                                </svg>
                            </a>
                        @endif
                        <div class="flex items-center gap-1.5 text-sm font-medium text-white/85">
                            <span class="flex -space-x-2">
                                <span class="h-7 w-7 rounded-full border-2 border-white/40 bg-rose-brand-300"></span>
                                <span class="h-7 w-7 rounded-full border-2 border-white/40 bg-brand-300"></span>
                                <span class="h-7 w-7 rounded-full border-2 border-white/40 bg-amber-300"></span>
                            </span>
                            Loved by {{ $storyStat1Value }} happy customers
                        </div>
                    </div>
                </div>
            @empty
                <div class="max-w-2xl">
                    <h1 class="font-display text-4xl leading-[1.05] font-bold text-white sm:text-6xl">Freshly Baked, Delivered with Love</h1>
                    <p class="mt-5 text-lg text-white/85">Biratnagar's favourite cake shop — order online in minutes.</p>
                    <a href="{{ route('shop') }}" wire:navigate class="mt-8 inline-flex rounded-full bg-white px-7 py-3.5 text-sm font-semibold text-brand-700 shadow-xl transition hover:scale-105">
                        Order Now
                    </a>
                </div>
            @endforelse
        </div>

        @if ($banners->count() > 1)
            <div class="absolute inset-x-0 bottom-6 z-10 flex justify-center gap-2 sm:bottom-8">
                @foreach ($banners as $index => $banner)
                    <button
                        x-on:click="slide = {{ $index }}"
                        :class="slide === {{ $index }} ? 'w-8 bg-white' : 'w-2 bg-white/40'"
                        class="h-2 rounded-full transition-all duration-300"
                        aria-label="Go to slide {{ $index + 1 }}"
                    ></button>
                @endforeach
            </div>
        @endif

        <svg class="absolute inset-x-0 bottom-0 z-10 h-10 w-full text-cream sm:h-16" viewBox="0 0 1440 60" preserveAspectRatio="none" fill="currentColor">
            <path d="M0,32 C240,64 480,0 720,16 C960,32 1200,64 1440,32 L1440,60 L0,60 Z"></path>
        </svg>
    </section>

    {{-- Categories --}}
    @if ($categories->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-xs font-semibold tracking-widest text-brand-500 uppercase">Our Collections</p>
                    <h2 class="mt-2 font-display text-3xl font-semibold text-ink">Shop by Category</h2>
                </div>
            </div>
            <div class="mt-8 flex gap-5 overflow-x-auto pb-4">
                @foreach ($categories as $category)
                    <a
                        href="{{ route('shop.category', $category) }}"
                        wire:navigate
                        class="group flex shrink-0 flex-col items-center gap-3 text-center"
                    >
                        <span class="relative flex h-24 w-24 items-center justify-center overflow-hidden rounded-full bg-brand-50 shadow-sm ring-4 ring-white transition duration-300 group-hover:-translate-y-1 group-hover:shadow-lg sm:h-28 sm:w-28">
                            @if ($category->image)
                                <img src="{{ $category->image }}" alt="{{ $category->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                            @else
                                <span class="text-3xl">🍰</span>
                            @endif
                        </span>
                        <span class="max-w-28 text-sm font-medium text-ink/80 group-hover:text-brand-600">{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Featured products --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-xs font-semibold tracking-widest text-brand-500 uppercase">Customer Favourites</p>
                    <h2 class="mt-2 font-display text-3xl font-semibold text-ink">Bestsellers</h2>
                </div>
                <a href="{{ route('shop') }}" wire:navigate class="text-sm font-medium text-brand-600 hover:text-brand-700">View all →</a>
            </div>
            <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Crafted with love — editorial split --}}
    <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div class="relative">
                <div class="aspect-4/5 overflow-hidden rounded-4xl shadow-xl">
                    <img
                        src="{{ asset('images/story-baker.jpg') }}"
                        alt="Baker decorating a fresh cake"
                        loading="lazy"
                        class="h-full w-full object-cover"
                    >
                </div>
                <div class="absolute -bottom-6 -right-4 flex items-center gap-3 rounded-2xl bg-white px-5 py-4 shadow-xl sm:-right-8">
                    <span class="flex h-11 w-11 items-center justify-center rounded-full bg-brand-100 text-xl">🎂</span>
                    <div>
                        <p class="font-display text-sm font-semibold text-ink">100% Handcrafted</p>
                        <p class="text-xs text-ink/60">Baked fresh, every order</p>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold tracking-widest text-brand-500 uppercase">Our Story</p>
                <h2 class="mt-2 font-display text-3xl font-semibold text-ink sm:text-4xl">{{ $storyHeading }}</h2>
                <p class="mt-5 text-ink/70">
                    {{ $storyBody }}
                </p>
                <div class="mt-8 grid grid-cols-2 gap-6">
                    <div>
                        <p class="font-display text-3xl font-bold text-brand-600">{{ $storyStat1Value }}</p>
                        <p class="mt-1 text-sm text-ink/60">{{ $storyStat1Label }}</p>
                    </div>
                    <div>
                        <p class="font-display text-3xl font-bold text-brand-600">{{ $storyStat2Value }}</p>
                        <p class="mt-1 text-sm text-ink/60">{{ $storyStat2Label }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Why choose us --}}
    <section class="bg-brand-50/60 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-xs font-semibold tracking-widest text-brand-500 uppercase">Our Promise</p>
                <h2 class="mt-2 font-display text-3xl font-semibold text-ink">Why Choose Us</h2>
            </div>
            <div class="mt-10 grid gap-8 sm:grid-cols-3">
                @foreach ($whyChooseUs as $item)
                    <div
                        class="translate-y-0 rounded-2xl bg-white p-8 text-center opacity-100 shadow-sm transition-all duration-500 starting:translate-y-4 starting:opacity-0"
                    >
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand-100 text-3xl">
                            {{ $item['icon'] }}
                        </div>
                        <h3 class="mt-5 font-display text-lg font-semibold text-ink">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm text-ink/70">{{ $item['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    @if ($testimonials->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-xs font-semibold tracking-widest text-brand-500 uppercase">Kind Words</p>
                <h2 class="mt-2 font-display text-3xl font-semibold text-ink">What Our Customers Say</h2>
            </div>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $testimonial)
                    <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                        <div class="flex gap-0.5 text-amber-400">
                            @for ($i = 0; $i < $testimonial->rating; $i++)
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.958a1 1 0 0 0 .95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.447a1 1 0 0 0-.363 1.118l1.287 3.958c.3.921-.755 1.688-1.538 1.118l-3.367-2.447a1 1 0 0 0-1.176 0l-3.367 2.447c-.783.57-1.838-.197-1.538-1.118l1.287-3.958a1 1 0 0 0-.363-1.118L2.063 9.385c-.783-.57-.38-1.81.588-1.81h4.163a1 1 0 0 0 .95-.69l1.285-3.958Z"/></svg>
                            @endfor
                        </div>
                        <p class="mt-3 text-sm text-ink/80">&ldquo;{{ $testimonial->message }}&rdquo;</p>
                        <div class="mt-4 flex items-center gap-3">
                            @if ($testimonial->photo_url)
                                <img src="{{ $testimonial->photo_url }}" alt="{{ $testimonial->name }}" class="h-9 w-9 rounded-full object-cover ring-2 ring-white shadow-sm">
                            @else
                                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-100 text-xs font-semibold text-brand-700">
                                    {{ Illuminate\Support\Str::of($testimonial->name)->explode(' ')->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('') }}
                                </span>
                            @endif
                            <p class="text-sm font-semibold text-ink">{{ $testimonial->name }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
