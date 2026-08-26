@php
    $shopName = \App\Models\Setting::get('shop_name', 'ShopKit');
    $tagline = \App\Models\Setting::get('shop_tagline');
    $phone = \App\Models\Setting::get('contact_phone');
    $whatsapp = \App\Models\Setting::get('contact_whatsapp');
    $email = \App\Models\Setting::get('contact_email');
    $address = \App\Models\Setting::get('shop_address');
    $mapsUrl = \App\Models\Setting::get('shop_google_maps_url');
    $facebook = \App\Models\Setting::get('facebook_url');
    $instagram = \App\Models\Setting::get('instagram_url');
    $esewaEnabled = (bool) \App\Models\Setting::get('esewa_enabled', false);
@endphp

<footer class="relative mt-16 overflow-hidden bg-brand-950 text-white/70">
    <svg class="absolute inset-x-0 -top-px h-10 w-full text-cream sm:h-14" viewBox="0 0 1440 60" preserveAspectRatio="none"
        fill="currentColor">
        <path d="M0,32 C240,0 480,64 720,48 C960,32 1200,0 1440,32 L1440,0 L0,0 Z"></path>
    </svg>

    <div class="pointer-events-none absolute -top-24 right-0 h-72 w-72 rounded-full bg-brand-600/20 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 h-64 w-64 rounded-full bg-accent-600/10 blur-3xl"></div>

    <div class="relative mx-auto grid max-w-7xl gap-10 px-4 pt-16 pb-12 sm:px-6 md:grid-cols-2 lg:px-8 lg:grid-cols-4">
        <div class="lg:col-span-1">
            <div class="flex items-center gap-2.5">
                <x-shop-logo :name="$shopName" class="h-11 w-11 text-base ring-2 ring-white/10" />
                <span class="font-display text-lg font-semibold text-white">{{ $shopName }}</span>
            </div>
            @if ($tagline)
                <p class="mt-4 max-w-xs text-sm text-white/60">{{ $tagline }}</p>
            @endif

            <div class="mt-6 flex items-center gap-3">
                @if ($facebook)
                    <a href="{{ $facebook }}" target="_blank" rel="noopener" aria-label="Facebook"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white/5 text-white/70 transition hover:bg-brand-600 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current">
                            <path
                                d="M22 12.06C22 6.505 17.523 2 12 2S2 6.505 2 12.06c0 5.022 3.657 9.184 8.438 9.94v-7.03H7.898v-2.91h2.54V9.845c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562v1.878h2.773l-.443 2.91h-2.33V22c4.78-.756 8.437-4.918 8.437-9.94Z" />
                        </svg>
                    </a>
                @endif
                @if ($instagram)
                    <a href="{{ $instagram }}" target="_blank" rel="noopener" aria-label="Instagram"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white/5 text-white/70 transition hover:bg-accent-600 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 fill-current">
                            <path
                                d="M12 2c-2.717 0-3.056.012-4.123.06-1.064.049-1.79.218-2.427.465a4.9 4.9 0 0 0-1.771 1.153A4.9 4.9 0 0 0 2.525 5.45c-.247.637-.416 1.363-.465 2.427C2.012 8.944 2 9.283 2 12s.012 3.056.06 4.123c.049 1.064.218 1.79.465 2.427a4.9 4.9 0 0 0 1.153 1.771 4.9 4.9 0 0 0 1.771 1.153c.637.247 1.363.416 2.427.465C8.944 21.988 9.283 22 12 22s3.056-.012 4.123-.06c1.064-.049 1.79-.218 2.427-.465a4.9 4.9 0 0 0 1.771-1.153 4.9 4.9 0 0 0 1.153-1.771c.247-.637.416-1.363.465-2.427.048-1.067.06-1.406.06-4.123s-.012-3.056-.06-4.123c-.049-1.064-.218-1.79-.465-2.427a4.9 4.9 0 0 0-1.153-1.771A4.9 4.9 0 0 0 18.55 2.525c-.637-.247-1.363-.416-2.427-.465C15.056 2.012 14.717 2 12 2Zm0 1.802c2.67 0 2.987.01 4.042.059.976.045 1.505.207 1.858.344.467.182.8.399 1.15.748.35.35.566.683.748 1.15.137.353.3.882.344 1.858.048 1.055.058 1.372.058 4.042s-.01 2.987-.058 4.042c-.045.976-.207 1.505-.344 1.858a3.1 3.1 0 0 1-.748 1.15 3.1 3.1 0 0 1-1.15.748c-.353.137-.882.3-1.858.344-1.055.048-1.372.058-4.042.058s-2.987-.01-4.042-.058c-.976-.045-1.505-.207-1.858-.344a3.1 3.1 0 0 1-1.15-.748 3.1 3.1 0 0 1-.748-1.15c-.137-.353-.3-.882-.344-1.858-.048-1.055-.058-1.372-.058-4.042s.01-2.987.058-4.042c.045-.976.207-1.505.344-1.858.182-.467.399-.8.748-1.15.35-.35.683-.566 1.15-.748.353-.137.882-.3 1.858-.344 1.055-.048 1.372-.059 4.042-.059Zm0 3.063a5.135 5.135 0 1 0 0 10.27 5.135 5.135 0 0 0 0-10.27Zm0 8.468a3.333 3.333 0 1 1 0-6.666 3.333 3.333 0 0 1 0 6.666Zm6.538-8.671a1.2 1.2 0 1 1-2.4 0 1.2 1.2 0 0 1 2.4 0Z" />
                        </svg>
                    </a>
                @endif
                @if ($whatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank" rel="noopener"
                        aria-label="WhatsApp"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-white/5 text-white/70 transition hover:bg-green-600 hover:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="h-4 w-4 fill-current">
                            <path
                                d="M16.001 3C9.373 3 4 8.373 4 15c0 2.386.697 4.61 1.898 6.48L4 29l7.72-1.858A11.94 11.94 0 0 0 16.001 27C22.628 27 28 21.627 28 15S22.628 3 16.001 3Zm0 21.818c-1.98 0-3.855-.55-5.454-1.5l-.39-.23-4.58 1.102 1.13-4.464-.255-.406A9.77 9.77 0 0 1 5.182 15c0-5.964 4.854-10.818 10.819-10.818 5.964 0 10.818 4.854 10.818 10.818 0 5.965-4.854 10.818-10.818 10.818Z" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>

        <div>
            <h3 class="font-display text-xs font-semibold tracking-widest text-white/90 uppercase">Quick Links</h3>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a href="{{ route('shop') }}" wire:navigate class="text-white/60 transition hover:text-white">Shop
                        All Products</a></li>
                <li><a href="{{ route('order.track') }}" wire:navigate
                        class="text-white/60 transition hover:text-white">Track Your Order</a></li>
                <li><a href="{{ route('cart') }}" wire:navigate class="text-white/60 transition hover:text-white">View
                        Cart</a></li>
                @auth
                    <li><a href="{{ route('account.orders') }}" wire:navigate
                            class="text-white/60 transition hover:text-white">My Orders</a></li>
                @else
                    <li><a href="{{ route('login') }}" wire:navigate
                            class="text-white/60 transition hover:text-white">Login</a></li>
                @endauth
            </ul>
        </div>

        <div>
            <h3 class="font-display text-xs font-semibold tracking-widest text-white/90 uppercase">Get in Touch</h3>
            <ul class="mt-4 space-y-3 text-sm text-white/60">
                @if ($phone)
                    <li class="flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-brand-400"
                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 6.75c0 8.284 6.716 15 15 15h1.5a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                        <a href="tel:{{ $phone }}" class="transition hover:text-white">{{ $phone }}</a>
                    </li>
                @endif
                @if ($email)
                    <li class="flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-brand-400"
                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        <a href="mailto:{{ $email }}"
                            class="transition hover:text-white">{{ $email }}</a>
                    </li>
                @endif
                @if ($address)
                    <li class="flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-brand-400"
                            fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        @if ($mapsUrl)
                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener"
                                class="transition hover:text-white">{{ $address }}</a>
                        @else
                            <span>{{ $address }}</span>
                        @endif
                    </li>
                @endif
            </ul>
        </div>

        <div>
            <h3 class="font-display text-xs font-semibold tracking-widest text-white/90 uppercase">We Accept</h3>
            <div class="mt-4 flex flex-wrap gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/5 px-3 py-2 text-xs font-medium text-white/70">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-brand-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3M3.75 6h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z" />
                    </svg>
                    Cash on Delivery
                </span>
                <span class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium {{ $esewaEnabled ? 'bg-white text-[#154734]' : 'bg-white/5 text-white/50' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4 shrink-0">
                        <circle cx="12" cy="12" r="12" fill="{{ $esewaEnabled ? '#60bb46' : 'currentColor' }}" />
                        <text x="12" y="16.8" text-anchor="middle" font-family="Georgia, 'Times New Roman', serif" font-size="14" font-style="italic" font-weight="bold" fill="{{ $esewaEnabled ? '#ffffff' : '#9ca3af' }}">e</text>
                    </svg>
                    <span class="font-semibold">eSewa</span>
                    @unless ($esewaEnabled)
                        <span class="font-normal">(coming soon)</span>
                    @endunless
                </span>
            </div>
            <p class="mt-6 text-xs text-white/40">Secure ordering and a happiness guarantee on every order.</p>
        </div>
    </div>

    <div class="relative border-t border-white/10">
        <div
            class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-white/40 sm:flex-row sm:px-6 lg:px-8">
            <p>&copy; {{ now()->year }} {{ $shopName }}. All rights reserved.</p>
            <p>Made with 🧡 By <a href="https://rohanadhikari.com.np" target="_blank" rel="noopener noreferrer">Rohan
                    Adhikari</a></p>
        </div>
    </div>
</footer>
