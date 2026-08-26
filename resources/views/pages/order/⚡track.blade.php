<?php

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

new class extends Component
{
    public string $orderNumber = '';

    public string $phone = '';

    public ?Order $order = null;

    public bool $searched = false;

    public ?Collection $myOrders = null;

    public function mount(): void
    {
        if (auth()->check()) {
            $this->myOrders = auth()->user()->orders()->with(['statusHistories', 'items.options'])->latest()->get();
        }
    }

    /**
     * Polled every few seconds so status changes (e.g. an admin marking an
     * order out for delivery) show up live without the customer refreshing
     * the page — plain HTTP polling, no websocket/broadcasting setup needed.
     */
    public function refreshTracking(): void
    {
        if (auth()->check()) {
            $this->myOrders = auth()->user()->orders()->with(['statusHistories', 'items.options'])->latest()->get();
        }

        if ($this->order) {
            $this->order = $this->order->fresh(['statusHistories', 'items.options']);
        }
    }

    public function search(): void
    {
        $this->validate([
            'orderNumber' => 'required|string',
            'phone' => 'required|string',
        ]);

        $throttleKey = 'order-track|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->addError('orderNumber', "Too many lookup attempts. Please try again in {$seconds} seconds.");

            return;
        }

        RateLimiter::hit($throttleKey, 60);

        $this->searched = true;

        $this->order = Order::query()
            ->where('order_number', trim($this->orderNumber))
            ->where('phone', trim($this->phone))
            ->with(['statusHistories', 'items.options'])
            ->first();
    }
};
?>

<div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="text-center">
        <h1 class="font-display text-3xl font-semibold text-ink">Track Your Order</h1>
        <p class="mt-2 text-ink/60">
            @auth
                Here's the latest on your orders.
            @else
                Enter your order number and phone number to see its status.
            @endauth
        </p>
    </div>

    @auth
        @if ($myOrders->isNotEmpty())
            <div wire:poll.15s.visible="refreshTracking" class="mt-8 flex flex-col gap-6">
                @foreach ($myOrders as $userOrder)
                    <div id="order-{{ $userOrder->id }}" class="scroll-mt-24">
                        <x-order-tracker :order="$userOrder" />
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-8 rounded-2xl border border-dashed border-brand-200 py-10 text-center text-ink/60">
                You haven't placed any orders yet.
                <a href="{{ route('shop') }}" wire:navigate class="mt-2 block font-medium text-brand-600 hover:text-brand-700">Start Shopping →</a>
            </div>
        @endif

        <div class="mt-10 border-t border-brand-100 pt-8 text-center">
            <p class="text-sm text-ink/50">Tracking an order placed with a different account or as a guest?</p>
        </div>
    @endauth

    <form wire:submit="search" class="mt-4 flex flex-col gap-4 rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:flex-row sm:items-end">
        <div class="flex-1">
            <label class="text-sm font-medium text-ink/80">Order Number</label>
            <input type="text" wire:model="orderNumber" placeholder="ORD-260819-XXXX" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
            @error('orderNumber') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
        </div>
        <div class="flex-1">
            <label class="text-sm font-medium text-ink/80">Phone Number</label>
            <input type="tel" wire:model="phone" placeholder="98XXXXXXXX" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
            @error('phone') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
        </div>
        <button type="submit" wire:loading.attr="disabled" class="rounded-full bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:scale-105 disabled:opacity-60">
            Track
        </button>
    </form>

    @if ($searched)
        @if (! $order)
            <div class="mt-8 rounded-2xl border border-dashed border-brand-200 py-10 text-center text-ink/60">
                No order found with that order number and phone. Please check and try again.
            </div>
        @else
            <div wire:poll.15s.visible="refreshTracking" class="mt-8">
                <x-order-tracker :order="$order" />
            </div>
        @endif
    @endif
</div>
