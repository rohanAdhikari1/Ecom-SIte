<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ \App\Models\Setting::get('shop_name', 'BRT Live Cake') }} — Freshly Baked Cakes in Biratnagar</title>
    <meta name="description" content="{{ \App\Models\Setting::get('shop_tagline', 'Order fresh, customisable cakes online for delivery across Biratnagar.') }}">

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

    {{-- Flash messages --}}
    @if (session('success') || session('error'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 4000)"
            x-show="show"
            x-transition
            class="fixed inset-x-0 top-20 z-50 mx-auto w-fit max-w-[90vw] rounded-full px-5 py-2.5 text-sm font-medium shadow-lg {{ session('success') ? 'bg-brand-600 text-white' : 'bg-rose-brand-600 text-white' }}"
        >
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
