<?php

use App\Enums\DeliveryMethod;
use App\Enums\PaymentMethod;
use App\Models\Address;
use App\Models\DeliveryArea;
use App\Models\Setting;
use App\Services\Cart\CartService;
use App\Services\Order\OrderService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $customerName = '';

    public string $phone = '';

    public ?string $email = null;

    public string $deliveryMethod = 'delivery';

    public string $deliveryAddress = '';

    public ?float $deliveryLat = null;

    public ?float $deliveryLng = null;

    public ?int $deliveryAreaId = null;

    public string $deliveryDate = '';

    public string $deliveryTimeSlot = '10 AM - 12 PM';

    public string $paymentMethod = 'cod';

    public ?string $notes = null;

    public bool $createAccount = false;

    public ?string $password = null;

    public bool $esewaEnabled = false;

    public ?string $esewaNote = null;

    public string $shopAddress = '';

    public float $shopLat = 0.0;

    public float $shopLng = 0.0;

    public string $shopGoogleMapsUrl = '';

    public ?int $selectedAddressId = null;

    public function mount(): void
    {
        if (auth()->check()) {
            $user = auth()->user();
            $this->customerName = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';

            $defaultAddress = $user->addresses()->where('is_default', true)->first();

            if ($defaultAddress) {
                $this->selectSavedAddress($defaultAddress->id);
            }
        }

        $this->deliveryDate = now()->addDay()->toDateString();
        $this->esewaEnabled = (bool) Setting::get('esewa_enabled', false);
        $this->esewaNote = Setting::get('esewa_note');
        $this->shopAddress = Setting::get('shop_address', 'Store pickup address');
        $this->shopLat = (float) Setting::get('shop_lat', $this->shopLat);
        $this->shopLng = (float) Setting::get('shop_lng', $this->shopLng);
        $this->shopGoogleMapsUrl = Setting::get('shop_google_maps_url', $this->shopGoogleMapsUrl);

        if ($this->cartService()->isEmpty()) {
            $this->redirect(route('shop'), navigate: true);
        }
    }

    public function updatedDeliveryMethod(): void
    {
        if ($this->deliveryMethod === 'pickup') {
            $this->deliveryAreaId = null;
        }
    }

    #[Computed]
    public function savedAddresses(): Collection
    {
        return auth()->check() ? auth()->user()->addresses : collect();
    }

    public function selectSavedAddress(int $addressId): void
    {
        $address = Address::query()->where('user_id', auth()->id())->find($addressId);

        if (! $address) {
            return;
        }

        $this->selectedAddressId = $address->id;
        $this->deliveryAddress = $address->address;
        $this->deliveryLat = $address->lat;
        $this->deliveryLng = $address->lng;

        if ($address->lat && $address->lng) {
            $this->dispatch('map-recenter', lat: $address->lat, lng: $address->lng);
        }
    }

    #[Computed]
    public function deliveryAreas(): Collection
    {
        return DeliveryArea::query()->active()->orderBy('sort_order')->get();
    }

    #[Computed]
    public function deliveryFee(): int
    {
        if ($this->deliveryMethod === 'pickup') {
            return 0;
        }

        return $this->deliveryAreaId
            ? (DeliveryArea::query()->find($this->deliveryAreaId)?->fee ?? 0)
            : 0;
    }

    #[Computed]
    public function subtotal(): int
    {
        return $this->cartService()->subtotal();
    }

    #[Computed]
    public function total(): int
    {
        return $this->subtotal + $this->deliveryFee;
    }

    public function placeOrder(): void
    {
        $isPickup = $this->deliveryMethod === 'pickup';

        if ($isPickup) {
            $this->deliveryAddress = $this->shopAddress;
            $this->deliveryLat = $this->shopLat;
            $this->deliveryLng = $this->shopLng;
        }

        $wantsAccount = $this->createAccount && ! auth()->check();

        $data = $this->validate([
            'customerName' => 'required|string|max:255',
            'phone' => ['required', 'regex:/^(97|98)[0-9]{8}$/'],
            'email' => $wantsAccount ? ['required', 'email', 'unique:users,email'] : 'nullable|email',
            'deliveryMethod' => 'required|in:delivery,pickup',
            'deliveryAddress' => 'required|string|max:500',
            'deliveryAreaId' => $isPickup ? 'nullable' : 'required|exists:delivery_areas,id',
            'deliveryDate' => 'required|date|after_or_equal:today',
            'deliveryTimeSlot' => 'required|string',
            'paymentMethod' => 'required|in:cod,esewa',
            'notes' => 'nullable|string|max:500',
            'password' => $wantsAccount ? 'required|min:8' : 'nullable',
        ], [
            'phone.regex' => 'Enter a valid Nepali mobile number (e.g. 98XXXXXXXX).',
            'email.unique' => 'An account with this email already exists. Please log in first.',
        ]);

        if ($this->paymentMethod === 'esewa' && ! $this->esewaEnabled) {
            $this->addError('paymentMethod', 'eSewa is currently unavailable. Please choose Cash on Delivery.');

            return;
        }

        $userId = auth()->id();

        if (! $userId && $this->createAccount) {
            $user = \App\Models\User::create([
                'name' => $this->customerName,
                'email' => $this->email,
                'phone' => $data['phone'],
                'password' => bcrypt($this->password),
            ]);

            auth()->login($user);
            $userId = $user->id;
        }

        $order = app(OrderService::class)->createFromCart([
            'user_id' => $userId,
            'delivery_method' => DeliveryMethod::from($data['deliveryMethod']),
            'customer_name' => $data['customerName'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'delivery_address' => $data['deliveryAddress'],
            'delivery_lat' => $this->deliveryLat,
            'delivery_lng' => $this->deliveryLng,
            'delivery_area_id' => $data['deliveryAreaId'],
            'delivery_date' => $data['deliveryDate'],
            'delivery_time_slot' => $data['deliveryTimeSlot'],
            'notes' => $data['notes'],
        ], PaymentMethod::from($data['paymentMethod']));

        $this->dispatch('cart-updated');

        if ($order->payment_method === PaymentMethod::Esewa && $order->esewa_transaction_uuid) {
            $this->redirect(route('payment.esewa.redirect', $order), navigate: false);

            return;
        }

        $this->redirect(route('order.confirmation', $order), navigate: true);
    }

    protected function cartService(): CartService
    {
        return app(CartService::class);
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    <h1 class="font-display text-3xl font-semibold text-ink">Checkout</h1>

    <form wire:submit="placeOrder" class="mt-8 grid gap-8 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            {{-- Contact --}}
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="font-display font-semibold text-ink">Contact Details</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-ink/80">Full Name</label>
                        <input type="text" wire:model="customerName" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                        @error('customerName') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink/80">Phone Number</label>
                        <input type="tel" wire:model="phone" placeholder="98XXXXXXXX" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                        @error('phone') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-ink/80">
                            Email
                            <span class="text-ink/40">{{ $createAccount ? '(required to create an account)' : '(optional)' }}</span>
                        </label>
                        <input type="email" wire:model="email" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                        @error('email') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Delivery method --}}
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="font-display font-semibold text-ink">How would you like to get your order?</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-brand-100 p-4 transition has-checked:border-brand-500 has-checked:bg-brand-50">
                        <input type="radio" wire:model.live="deliveryMethod" value="delivery" class="text-brand-600 focus:ring-brand-400">
                        <div>
                            <p class="text-sm font-semibold text-ink">🚴 Home Delivery</p>
                            <p class="text-xs text-ink/60">We deliver straight to your door.</p>
                        </div>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-brand-100 p-4 transition has-checked:border-brand-500 has-checked:bg-brand-50">
                        <input type="radio" wire:model.live="deliveryMethod" value="pickup" class="text-brand-600 focus:ring-brand-400">
                        <div>
                            <p class="text-sm font-semibold text-ink">🏬 Self Pickup</p>
                            <p class="text-xs text-ink/60">Collect from our shop — no delivery fee.</p>
                        </div>
                    </label>
                </div>
            </div>

            @if ($deliveryMethod === 'pickup')
                {{-- Pickup details --}}
                <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                    <h2 class="font-display font-semibold text-ink">Pickup Details</h2>
                    <div class="mt-4 rounded-xl bg-brand-50 p-4">
                        <p class="text-sm font-semibold text-ink">📍 {{ $shopAddress }}</p>
                        <a
                            href="{{ $shopGoogleMapsUrl }}"
                            target="_blank"
                            rel="noopener"
                            class="mt-1 inline-block text-xs font-medium text-brand-600 hover:text-brand-700"
                        >
                            View on map →
                        </a>
                    </div>

                    <div class="mt-4">
                        <label class="text-sm font-medium text-ink/80">Pickup Date</label>
                        <input type="date" wire:model="deliveryDate" min="{{ now()->toDateString() }}" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                        @error('deliveryDate') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-ink/80">Pickup Time</label>
                        <select wire:model="deliveryTimeSlot" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                            <option>10 AM - 12 PM</option>
                            <option>12 PM - 3 PM</option>
                            <option>3 PM - 6 PM</option>
                            <option>6 PM - 8 PM</option>
                        </select>
                    </div>
                    <div class="mt-4">
                        <label class="text-sm font-medium text-ink/80">Order Notes <span class="text-ink/40">(optional)</span></label>
                        <textarea wire:model="notes" rows="2" placeholder="Any special instructions?" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200"></textarea>
                    </div>
                </div>
            @else
                {{-- Delivery details --}}
                <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                    <h2 class="font-display font-semibold text-ink">Delivery Details</h2>

                    @if ($this->savedAddresses->isNotEmpty())
                        <div class="mt-4">
                            <label class="text-sm font-medium text-ink/80">Choose a saved address</label>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($this->savedAddresses as $saved)
                                    <button
                                        type="button"
                                        wire:click="selectSavedAddress({{ $saved->id }})"
                                        class="rounded-full border px-4 py-2 text-xs font-medium transition {{ $selectedAddressId === $saved->id ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-brand-100 text-ink/60 hover:border-brand-300' }}"
                                    >
                                        📍 {{ $saved->label }}
                                    </button>
                                @endforeach
                                <button
                                    type="button"
                                    wire:click="$set('selectedAddressId', null)"
                                    class="rounded-full border px-4 py-2 text-xs font-medium transition {{ $selectedAddressId === null ? 'border-brand-500 bg-brand-50 text-brand-700' : 'border-brand-100 text-ink/60 hover:border-brand-300' }}"
                                >
                                    ✏️ New address
                                </button>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 grid gap-4">
                        <div>
                            <label class="text-sm font-medium text-ink/80">Delivery Address</label>
                            <textarea wire:model="deliveryAddress" rows="2" placeholder="House/street details, or pin your location on the map below" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200"></textarea>
                            @error('deliveryAddress') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror

                            <div class="mt-3">
                                <x-map-picker :initial-lat="$deliveryLat ?? $shopLat" :initial-lng="$deliveryLng ?? $shopLng" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="text-sm font-medium text-ink/80">Delivery Area</label>
                                <select wire:model.live="deliveryAreaId" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                                    <option value="">Select an area…</option>
                                    @foreach ($this->deliveryAreas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }} ({{ $area->fee > 0 ? \App\Support\Money::format($area->fee) : 'Free' }})</option>
                                    @endforeach
                                </select>
                                @error('deliveryAreaId') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-ink/80">Delivery Date</label>
                                <input type="date" wire:model="deliveryDate" min="{{ now()->toDateString() }}" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                                @error('deliveryDate') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-ink/80">Delivery Time Slot</label>
                            <select wire:model="deliveryTimeSlot" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                                <option>10 AM - 12 PM</option>
                                <option>12 PM - 3 PM</option>
                                <option>3 PM - 6 PM</option>
                                <option>6 PM - 8 PM</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-ink/80">Order Notes <span class="text-ink/40">(optional)</span></label>
                            <textarea wire:model="notes" rows="2" placeholder="Any special instructions?" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200"></textarea>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Payment --}}
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="font-display font-semibold text-ink">Payment Method</h2>
                <div class="mt-4 flex flex-col gap-3">
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-brand-100 p-4 transition has-checked:border-brand-500 has-checked:bg-brand-50">
                        <input type="radio" wire:model.live="paymentMethod" value="cod" class="text-brand-600 focus:ring-brand-400">
                        <div>
                            <p class="text-sm font-semibold text-ink">Cash on Delivery</p>
                            <p class="text-xs text-ink/60">Pay in cash when your order arrives.</p>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-brand-100 p-4 transition has-checked:border-brand-500 has-checked:bg-brand-50 {{ ! $esewaEnabled ? 'opacity-50' : '' }}">
                        <input type="radio" wire:model.live="paymentMethod" value="esewa" class="text-brand-600 focus:ring-brand-400" {{ ! $esewaEnabled ? 'disabled' : '' }}>
                        <div>
                            <p class="text-sm font-semibold text-ink">eSewa</p>
                            <p class="text-xs text-ink/60">
                                {{ $esewaEnabled ? ($esewaNote ?? 'Online payment via eSewa.') : 'Currently unavailable — coming soon.' }}
                            </p>
                        </div>
                    </label>
                    @error('paymentMethod') <p class="text-xs text-accent-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Account --}}
            @unless (auth()->check())
                <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model.live="createAccount" class="rounded border-brand-300 text-brand-600 focus:ring-brand-400">
                        <span class="text-sm font-medium text-ink">Create an account to track this order</span>
                    </label>
                    @if ($createAccount)
                        <div class="mt-3">
                            <label class="text-sm font-medium text-ink/80">Password</label>
                            <input type="password" wire:model="password" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                            @error('password') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
            @endunless
        </div>

        {{-- Summary --}}
        <div class="h-fit rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
            <h2 class="font-display text-lg font-semibold text-ink">Order Summary</h2>
            <div class="mt-4 space-y-2 text-sm text-ink/70">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span>{{ \App\Support\Money::format($this->subtotal) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>{{ $deliveryMethod === 'pickup' ? 'Pickup Fee' : 'Delivery Fee' }}</span>
                    <span>{{ \App\Support\Money::format($this->deliveryFee) }}</span>
                </div>
                <div class="flex justify-between border-t border-brand-100 pt-2 text-base font-semibold text-ink">
                    <span>Total</span>
                    <span>{{ \App\Support\Money::format($this->total) }}</span>
                </div>
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="mt-6 flex w-full items-center justify-center rounded-full bg-brand-600 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/30 transition hover:scale-[1.02] hover:bg-brand-700 disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="placeOrder">Place Order</span>
                <span wire:loading wire:target="placeOrder">Placing Order…</span>
            </button>
        </div>
    </form>
</div>
