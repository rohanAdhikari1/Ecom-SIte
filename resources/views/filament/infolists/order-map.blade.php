@php
    $order = $getRecord();
@endphp

@if (! $order->delivery_lat || ! $order->delivery_lng)
    <div class="rounded-xl border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:text-gray-400">
        No pinned location for this order.
    </div>
@elseif (! config('services.google_maps.key'))
    <div class="rounded-xl border border-dashed border-gray-300 px-4 py-3 text-xs text-gray-500 dark:border-gray-600 dark:text-gray-400">
        Add a <code>GOOGLE_MAPS_API_KEY</code> to <code>.env</code> to preview the pinned location here.
        <a href="https://www.google.com/maps/search/?api=1&query={{ $order->delivery_lat }},{{ $order->delivery_lng }}" target="_blank" rel="noopener" class="text-primary-600 underline">Open in Google Maps →</a>
    </div>
@else
    <div
        wire:ignore
        x-data="{
            init() {
                if (!window.__googleMapsReady) return;
                window.__googleMapsReady.then(() => {
                    const position = { lat: {{ $order->delivery_lat }}, lng: {{ $order->delivery_lng }} };
                    const map = new google.maps.Map(this.$refs.mapEl, {
                        center: position,
                        zoom: 16,
                        mapId: 'ORDER_MAP',
                        streetViewControl: false,
                        fullscreenControl: false,
                        mapTypeControl: false,
                        gestureHandling: 'cooperative',
                    });
                    new google.maps.Marker({ position, map });
                });
            },
        }"
        x-init="init()"
        class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700"
    >
        <div x-ref="mapEl" class="h-64 w-full bg-gray-100 dark:bg-gray-800"></div>
    </div>
    <a
        href="https://www.google.com/maps/search/?api=1&query={{ $order->delivery_lat }},{{ $order->delivery_lng }}"
        target="_blank"
        rel="noopener"
        class="mt-2 inline-block text-xs font-medium text-primary-600 hover:underline"
    >
        Open in Google Maps →
    </a>
@endif
