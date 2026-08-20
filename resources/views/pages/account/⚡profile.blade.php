<?php

use App\Models\Address;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public string $name = '';

    public string $email = '';

    public ?string $phone = null;

    public ?string $currentPassword = null;

    public ?string $newPassword = null;

    public ?string $newPasswordConfirmation = null;

    public Collection $addresses;

    public bool $showAddressForm = false;

    public ?int $editingAddressId = null;

    public string $addressLabel = 'Home';

    public string $addressText = '';

    public ?float $addressLat = null;

    public ?float $addressLng = null;

    public float $mapDefaultLat = 26.4733808;

    public float $mapDefaultLng = 87.2730507;

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->addresses = $user->addresses;

        $this->mapDefaultLat = (float) Setting::get('shop_lat', $this->mapDefaultLat);
        $this->mapDefaultLng = (float) Setting::get('shop_lng', $this->mapDefaultLng);
    }

    public function updateProfile(): void
    {
        $user = auth()->user();

        $data = $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($data);

        session()->flash('success', 'Your profile has been updated.');
    }

    public function updatePassword(): void
    {
        $user = auth()->user();

        $this->validate([
            'currentPassword' => 'required|current_password',
            'newPassword' => 'required|min:8|same:newPasswordConfirmation',
        ], [
            'newPassword.same' => 'The new password confirmation does not match.',
        ]);

        $user->update(['password' => Hash::make($this->newPassword)]);

        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);

        session()->flash('success', 'Your password has been changed.');
    }

    public function addAddress(): void
    {
        $this->reset(['addressLabel', 'addressText', 'addressLat', 'addressLng', 'editingAddressId']);
        $this->addressLabel = 'Home';
        $this->showAddressForm = true;
    }

    public function editAddress(int $addressId): void
    {
        $address = auth()->user()->addresses()->findOrFail($addressId);

        $this->editingAddressId = $address->id;
        $this->addressLabel = $address->label;
        $this->addressText = $address->address;
        $this->addressLat = $address->lat;
        $this->addressLng = $address->lng;
        $this->showAddressForm = true;
    }

    public function cancelAddressForm(): void
    {
        $this->showAddressForm = false;
        $this->reset(['addressLabel', 'addressText', 'addressLat', 'addressLng', 'editingAddressId']);
    }

    public function saveAddress(): void
    {
        $data = $this->validate([
            'addressLabel' => 'required|string|max:50',
            'addressText' => 'required|string|max:500',
        ]);

        $user = auth()->user();

        $address = $user->addresses()->updateOrCreate(
            ['id' => $this->editingAddressId],
            [
                'label' => $data['addressLabel'],
                'address' => $data['addressText'],
                'lat' => $this->addressLat,
                'lng' => $this->addressLng,
            ],
        );

        if ($user->addresses()->count() === 1) {
            $address->makeDefault();
        }

        $this->addresses = $user->addresses()->get();
        $this->cancelAddressForm();

        session()->flash('success', 'Address saved.');
    }

    public function deleteAddress(int $addressId): void
    {
        auth()->user()->addresses()->where('id', $addressId)->delete();
        $this->addresses = auth()->user()->addresses()->get();

        session()->flash('success', 'Address removed.');
    }

    public function makeAddressDefault(int $addressId): void
    {
        $address = auth()->user()->addresses()->findOrFail($addressId);
        $address->makeDefault();

        $this->addresses = auth()->user()->addresses()->get();
    }
};
?>

<div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
    {{-- Profile header --}}
    <div class="overflow-hidden rounded-3xl bg-linear-to-br from-brand-600 via-brand-600 to-rose-brand-600 p-8 text-white shadow-lg">
        <div class="flex items-center gap-4">
            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white/15 font-display text-2xl font-semibold ring-4 ring-white/20">
                {{ auth()->user()->initials() }}
            </span>
            <div>
                <p class="font-display text-2xl font-semibold">{{ $name }}</p>
                <p class="text-sm text-white/80">{{ $email }}</p>
            </div>
        </div>
        <div class="mt-6 flex flex-wrap gap-3 text-sm">
            <a href="{{ route('account.orders') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-4 py-2 font-medium backdrop-blur transition hover:bg-white/25">
                📦 My Orders
            </a>
            <a href="{{ route('order.track') }}" wire:navigate class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-4 py-2 font-medium backdrop-blur transition hover:bg-white/25">
                🚴 Track Orders
            </a>
        </div>
    </div>

    {{-- Delivery addresses --}}
    <div class="mt-8 rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="font-display font-semibold text-ink">📍 Delivery Addresses</h2>
            @unless ($showAddressForm)
                <button wire:click="addAddress" class="text-sm font-medium text-brand-600 hover:text-brand-700">+ Add Address</button>
            @endunless
        </div>

        @if ($showAddressForm)
            <form wire:submit="saveAddress" class="mt-4 flex flex-col gap-3 rounded-xl border border-brand-100 bg-brand-50/40 p-4">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="sm:col-span-1">
                        <label class="text-sm font-medium text-ink/80">Label</label>
                        <select wire:model="addressLabel" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                            <option>Home</option>
                            <option>Office</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-ink/80">Address</label>
                        <textarea wire:model="addressText" rows="1" placeholder="Type or pin on the map below" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200"></textarea>
                        @error('addressText') <p class="mt-1 text-xs text-rose-brand-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <x-map-picker :initial-lat="$addressLat ?? $mapDefaultLat" :initial-lng="$addressLng ?? $mapDefaultLng" />

                <div class="flex gap-2">
                    <button type="submit" class="rounded-full bg-brand-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700">
                        Save Address
                    </button>
                    <button type="button" wire:click="cancelAddressForm" class="rounded-full border border-brand-200 px-5 py-2 text-sm font-medium text-ink/70 transition hover:bg-brand-50">
                        Cancel
                    </button>
                </div>
            </form>
        @endif

        <div class="mt-4 flex flex-col gap-3">
            @forelse ($addresses as $address)
                <div class="flex items-start justify-between gap-3 rounded-xl border border-brand-100 p-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-ink">{{ $address->label }}</p>
                            @if ($address->is_default)
                                <span class="rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-semibold text-brand-700 uppercase">Default</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-ink/60">{{ $address->address }}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-3 text-xs">
                        @unless ($address->is_default)
                            <button wire:click="makeAddressDefault({{ $address->id }})" class="font-medium text-brand-600 hover:text-brand-700">Set default</button>
                        @endunless
                        <button wire:click="editAddress({{ $address->id }})" class="font-medium text-ink/50 hover:text-ink">Edit</button>
                        <button wire:click="deleteAddress({{ $address->id }})" wire:confirm="Remove this address?" class="font-medium text-rose-brand-500 hover:text-rose-brand-600">Delete</button>
                    </div>
                </div>
            @empty
                @unless ($showAddressForm)
                    <p class="rounded-xl border border-dashed border-brand-200 py-6 text-center text-sm text-ink/50">
                        No saved addresses yet — add one to check out faster next time.
                    </p>
                @endunless
            @endforelse
        </div>
    </div>

    {{-- Account details --}}
    <div class="mt-6 rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
        <h2 class="font-display font-semibold text-ink">👤 Account Details</h2>
        <form wire:submit="updateProfile" class="mt-4 grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="text-sm font-medium text-ink/80">Full Name</label>
                <input type="text" wire:model="name" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                @error('name') <p class="mt-1 text-xs text-rose-brand-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink/80">Email</label>
                <input type="email" wire:model="email" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                @error('email') <p class="mt-1 text-xs text-rose-brand-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink/80">Phone Number</label>
                <input type="tel" wire:model="phone" placeholder="98XXXXXXXX" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                @error('phone') <p class="mt-1 text-xs text-rose-brand-600">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <button type="submit" wire:loading.attr="disabled" class="rounded-full bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.02] hover:bg-brand-700 disabled:opacity-60">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Change password --}}
    <div class="mt-6 rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
        <h2 class="font-display font-semibold text-ink">🔒 Change Password</h2>
        <form wire:submit="updatePassword" class="mt-4 grid gap-4 sm:grid-cols-3">
            <div>
                <label class="text-sm font-medium text-ink/80">Current Password</label>
                <input type="password" wire:model="currentPassword" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                @error('currentPassword') <p class="mt-1 text-xs text-rose-brand-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink/80">New Password</label>
                <input type="password" wire:model="newPassword" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                @error('newPassword') <p class="mt-1 text-xs text-rose-brand-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink/80">Confirm New Password</label>
                <input type="password" wire:model="newPasswordConfirmation" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
            </div>

            <div class="sm:col-span-3">
                <button type="submit" wire:loading.attr="disabled" class="rounded-full bg-ink px-6 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.02] disabled:opacity-60">
                    Update Password
                </button>
            </div>
        </form>
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
        @csrf
        <button type="submit" class="text-sm font-medium text-ink/50 hover:text-rose-brand-600">Logout</button>
    </form>
</div>
