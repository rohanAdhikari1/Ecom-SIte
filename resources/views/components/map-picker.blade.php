@props(['initialLat' => 26.4525, 'initialLng' => 87.2718])

@if (! config('services.google_maps.key'))
    <div class="rounded-xl border border-dashed border-brand-200 bg-brand-50/50 px-4 py-3 text-xs text-ink/60">
        Map picker isn't set up yet — add a <code class="rounded bg-white px-1 py-0.5">GOOGLE_MAPS_API_KEY</code> to your <code class="rounded bg-white px-1 py-0.5">.env</code> to let customers pin their exact delivery location. You can still type the address above.
    </div>
@else
    <div
        wire:ignore
        x-data="{
            map: null,
            marker: null,
            geocoder: null,
            locating: false,
            ready: false,
            failed: false,
            async init() {
                if (!window.__googleMapsReady) { this.failed = true; return; }
                await window.__googleMapsReady;

                const center = { lat: {{ $initialLat }}, lng: {{ $initialLng }} };
                this.map = new google.maps.Map(this.$refs.mapEl, {
                    center,
                    zoom: 14,
                    mapId: 'BRT_LIVE_CAKE_MAP',
                    streetViewControl: false,
                    fullscreenControl: false,
                    mapTypeControl: false,
                });
                this.geocoder = new google.maps.Geocoder();
                this.marker = new google.maps.Marker({ position: center, map: this.map, draggable: true });

                this.marker.addListener('dragend', () => this.updatePosition(this.marker.getPosition().toJSON()));
                this.map.addListener('click', (e) => {
                    const latlng = e.latLng.toJSON();
                    this.marker.setPosition(latlng);
                    this.updatePosition(latlng);
                });

                this.ready = true;
            },
            updatePosition(latlng) {
                $wire.set('deliveryLat', latlng.lat);
                $wire.set('deliveryLng', latlng.lng);
                this.reverseGeocode(latlng);
            },
            reverseGeocode(latlng) {
                this.geocoder.geocode({ location: latlng }, (results, status) => {
                    if (status === 'OK' && results?.[0]) {
                        $wire.set('deliveryAddress', results[0].formatted_address);
                    }
                });
            },
            locateMe() {
                if (!navigator.geolocation) return;
                this.locating = true;
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const latlng = { lat: pos.coords.latitude, lng: pos.coords.longitude };
                        this.map.setCenter(latlng);
                        this.map.setZoom(16);
                        this.marker.setPosition(latlng);
                        this.updatePosition(latlng);
                        this.locating = false;
                    },
                    () => { this.locating = false; },
                    { enableHighAccuracy: true, timeout: 10000 },
                );
            },
            recenter(latlng) {
                if (!this.ready || latlng.lat == null || latlng.lng == null) return;
                this.map.setCenter(latlng);
                this.map.setZoom(16);
                this.marker.setPosition(latlng);
            },
        }"
        x-init="init(); $wire.on('map-recenter', (event) => recenter({ lat: event.lat, lng: event.lng }))"
        class="overflow-hidden rounded-xl border border-brand-100"
    >
        <div class="flex items-center justify-between gap-2 border-b border-brand-100 bg-brand-50/50 px-3 py-2">
            <p class="text-xs text-ink/60">Drag the pin or tap the map to pin your exact delivery spot</p>
            <button
                type="button"
                x-on:click="locateMe()"
                x-show="ready"
                :disabled="locating"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-white px-3 py-1.5 text-xs font-medium text-brand-700 shadow-sm transition hover:bg-brand-100 disabled:opacity-60"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                <span x-text="locating ? 'Locating…' : 'Use my location'"></span>
            </button>
        </div>
        <div x-ref="mapEl" class="h-56 w-full bg-brand-50"></div>
        <p x-show="failed" x-cloak class="px-3 py-2 text-xs text-rose-brand-600">Map failed to load. You can still type your address above.</p>
    </div>
@endif
