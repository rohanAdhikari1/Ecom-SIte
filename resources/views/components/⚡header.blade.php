<?php

use App\Models\Setting;
use Livewire\Component;

new class extends Component
{
    public string $shopName = 'BRT Live Cake';

    public ?string $phone = null;

    public ?string $facebookUrl = null;

    public string $announcementText = '';

    public function mount(): void
    {
        $this->shopName = Setting::get('shop_name', 'BRT Live Cake');
        $this->phone = Setting::get('contact_phone');
        $this->facebookUrl = Setting::get('facebook_url');
        $this->announcementText = Setting::get('announcement_text', 'Same-day delivery available — order today');
    }
};
?>

<div class="sticky top-0 z-40">
    {{-- Utility bar --}}
    <div class="hidden bg-brand-950 text-white/80 sm:block">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-2 text-xs sm:px-6 lg:px-8">
            <p class="flex items-center gap-1.5">
                <span>🚴</span>
                {{ $announcementText }}
            </p>
            <div class="flex items-center gap-4">
                @if ($phone)
                    <a href="tel:{{ $phone }}" class="flex items-center gap-1.5 transition hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h1.5a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                        {{ $phone }}
                    </a>
                @endif
                @if ($facebookUrl)
                    <a href="{{ $facebookUrl }}" target="_blank" rel="noopener" class="transition hover:text-white" aria-label="Facebook">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-3.5 w-3.5 fill-current">
                            <path d="M22 12.06C22 6.505 17.523 2 12 2S2 6.505 2 12.06c0 5.022 3.657 9.184 8.438 9.94v-7.03H7.898v-2.91h2.54V9.845c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562v1.878h2.773l-.443 2.91h-2.33V22c4.78-.756 8.437-4.918 8.437-9.94Z"/>
                        </svg>
                    </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Main header --}}
    <header
        x-data="{ open: false, scrolled: false }"
        x-on:scroll.window="scrolled = window.scrollY > 12"
        :class="scrolled ? 'shadow-md shadow-brand-950/5 py-2.5' : 'shadow-none py-4'"
        class="border-b border-brand-100 bg-cream/90 backdrop-blur transition-all"
    >
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" wire:navigate class="flex shrink-0 items-center gap-2.5">
                <img
                    src="{{ asset('images/logo-circle.png') }}"
                    alt="{{ $shopName }} logo"
                    class="h-12 w-12 rounded-full shadow-md shadow-brand-600/20 ring-2 ring-white"
                >
                <span class="font-display text-lg leading-tight font-semibold text-brand-700 sm:text-xl">
                    {{ $shopName }}
                </span>
            </a>

            <nav class="hidden items-center gap-1 md:flex">
                @php
                    $navLinks = [
                        ['route' => 'home', 'label' => 'Home'],
                        ['route' => 'shop', 'label' => 'Shop'],
                        ['route' => 'order.track', 'label' => 'Track Order'],
                    ];
                @endphp
                @foreach ($navLinks as $link)
                    <a
                        href="{{ route($link['route']) }}"
                        wire:navigate
                        class="relative rounded-full px-4 py-2 text-sm font-medium transition {{ request()->routeIs($link['route']) ? 'text-brand-700' : 'text-ink/70 hover:bg-brand-50 hover:text-brand-600' }}"
                    >
                        {{ $link['label'] }}
                        @if (request()->routeIs($link['route']))
                            <span class="absolute inset-x-4 -bottom-0.5 h-0.5 rounded-full bg-brand-600"></span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-1.5">
                <livewire:search-bar />

                <livewire:cart-badge />

                @auth
                    <div x-data="{ accountOpen: false }" class="relative hidden sm:block">
                        <button
                            x-on:click="accountOpen = !accountOpen"
                            class="inline-flex items-center justify-center rounded-full p-2.5 text-ink transition hover:bg-brand-50 hover:text-brand-600"
                            aria-label="My account"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                        <div
                            x-show="accountOpen"
                            x-cloak
                            x-transition
                            x-on:click.outside="accountOpen = false"
                            class="absolute right-0 top-full mt-2 w-48 overflow-hidden rounded-2xl border border-brand-100 bg-white py-1.5 shadow-xl shadow-brand-950/10"
                        >
                            <a href="{{ route('account.profile') }}" wire:navigate class="block px-4 py-2 text-sm text-ink/80 hover:bg-brand-50 hover:text-brand-600">My Profile</a>
                            <a href="{{ route('account.orders') }}" wire:navigate class="block px-4 py-2 text-sm text-ink/80 hover:bg-brand-50 hover:text-brand-600">My Orders</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-rose-brand-600 hover:bg-rose-brand-50">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="hidden rounded-full p-2.5 text-ink transition hover:bg-brand-50 hover:text-brand-600 sm:inline-flex" aria-label="Login">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </a>
                @endauth

                <button
                    x-on:click="open = !open"
                    class="inline-flex items-center justify-center rounded-full p-2.5 text-ink transition hover:bg-brand-50 md:hidden"
                    aria-label="Toggle menu"
                >
                    <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <nav
            x-show="open"
            x-cloak
            x-transition
            x-on:click.outside="open = false"
            class="flex flex-col gap-1 border-t border-brand-100 bg-cream px-4 py-3 md:hidden"
        >
            <a href="{{ route('home') }}" wire:navigate class="rounded-lg px-3 py-2 text-sm font-medium text-ink/80 hover:bg-brand-50">Home</a>
            <a href="{{ route('shop') }}" wire:navigate class="rounded-lg px-3 py-2 text-sm font-medium text-ink/80 hover:bg-brand-50">Shop</a>
            <a href="{{ route('order.track') }}" wire:navigate class="rounded-lg px-3 py-2 text-sm font-medium text-ink/80 hover:bg-brand-50">Track Order</a>
            @auth
                <a href="{{ route('account.profile') }}" wire:navigate class="rounded-lg px-3 py-2 text-sm font-medium text-ink/80 hover:bg-brand-50">My Profile</a>
                <a href="{{ route('account.orders') }}" wire:navigate class="rounded-lg px-3 py-2 text-sm font-medium text-ink/80 hover:bg-brand-50">My Orders</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-lg px-3 py-2 text-left text-sm font-medium text-rose-brand-600 hover:bg-rose-brand-50">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" wire:navigate class="rounded-lg px-3 py-2 text-sm font-medium text-ink/80 hover:bg-brand-50">Login</a>
            @endauth
        </nav>
    </header>
</div>
