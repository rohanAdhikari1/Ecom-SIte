<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ \App\Models\Setting::get('shop_name', 'ShopKit') }}</title>
    <meta name="description" content="{{ \App\Models\Setting::get('shop_tagline', 'Everything you need, delivered fast.') }}">

    <link rel="icon" href="/favicon-32.png" type="image/png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if (config('services.google_maps.key'))
        <script>
            window.__googleMapsReady = new Promise((resolve) => { window.__resolveGoogleMaps = resolve; });
            window.__resolveGoogleMapsCallback = () => window.__resolveGoogleMaps();
        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&callback=__resolveGoogleMapsCallback&loading=async" async defer></script>
    @endif
</head>
<body class="flex min-h-screen flex-col bg-cream font-sans text-ink antialiased">

    <livewire:header />

    {{-- Flash messages — bottom-anchored so it never overlaps the sticky header/nav --}}
    @if (session('success') || session('error'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 4000)"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            x-cloak
            class="fixed inset-x-0 bottom-6 z-50 mx-auto flex w-fit max-w-[90vw] items-center gap-2.5 rounded-full px-5 py-3 text-sm font-medium text-white shadow-xl {{ session('success') ? 'bg-brand-600 shadow-brand-900/20' : 'bg-accent-600 shadow-accent-900/20' }}"
        >
            @if (session('success'))
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            @endif
            {{ session('success') ?? session('error') }}
        </div>
    @endif

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-storefront-footer />

    {{-- Floating WhatsApp / call button --}}
    @php($whatsapp = \App\Models\Setting::get('contact_whatsapp'))
    @if ($whatsapp)
        <a
            href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}"
            target="_blank"
            rel="noopener"
            class="fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-green-500 text-white shadow-lg shadow-green-500/30 transition hover:scale-110"
            aria-label="Chat on WhatsApp"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="h-7 w-7 fill-current">
                <path d="M16.001 3C9.373 3 4 8.373 4 15c0 2.386.697 4.61 1.898 6.48L4 29l7.72-1.858A11.94 11.94 0 0 0 16.001 27C22.628 27 28 21.627 28 15S22.628 3 16.001 3Zm0 21.818c-1.98 0-3.855-.55-5.454-1.5l-.39-.23-4.58 1.102 1.13-4.464-.255-.406A9.77 9.77 0 0 1 5.182 15c0-5.964 4.854-10.818 10.819-10.818 5.964 0 10.818 4.854 10.818 10.818 0 5.965-4.854 10.818-10.818 10.818Zm5.94-8.115c-.325-.163-1.928-.951-2.226-1.06-.298-.108-.516-.163-.733.163-.217.325-.842 1.06-1.032 1.278-.19.217-.38.244-.706.081-.325-.163-1.374-.506-2.617-1.612-.968-.863-1.622-1.93-1.812-2.255-.19-.326-.02-.502.143-.664.147-.146.325-.38.488-.57.163-.19.217-.326.325-.543.109-.217.055-.407-.027-.57-.081-.163-.733-1.766-1.004-2.418-.264-.635-.532-.55-.733-.56-.19-.008-.407-.01-.624-.01a1.2 1.2 0 0 0-.868.407c-.298.326-1.14 1.114-1.14 2.717s1.167 3.152 1.33 3.37c.163.217 2.297 3.507 5.567 4.918.778.336 1.384.536 1.857.687.78.248 1.49.213 2.052.13.626-.094 1.928-.788 2.2-1.55.271-.76.271-1.412.19-1.55-.081-.136-.298-.217-.624-.38Z"/>
            </svg>
        </a>
    @endif
</body>
</html>
