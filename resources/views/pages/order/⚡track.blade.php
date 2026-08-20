<?php

use App\Models\Order;
use Illuminate\Support\Collection;
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
            $this->myOrders = auth()->user()->orders()->with(['statusHistories', 'items'])->latest()->get();
        }
    }

    public function search(): void
    {
        $this->validate([
            'orderNumber' => 'required|string',
            'phone' => 'required|string',
        ]);

        $this->searched = true;

        $this->order = Order::query()
            ->where('order_number', trim($this->orderNumber))
            ->where('phone', trim($this->phone))
            ->with(['statusHistories', 'items'])
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
            <div class="mt-8 flex flex-col gap-6">
                @foreach ($myOrders as $order)
                    <div id="order-{{ $order->id }}" class="scroll-mt-24">
                        <x-order-tracker :order="$order" />
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
            <input type="text" wire:model="orderNumber" placeholder="BRT-260819-XXXX" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
            @error('orderNumber') <p class="mt-1 text-xs text-rose-brand-600">{{ $message }}</p> @enderror
        </div>
        <div class="flex-1">
            <label class="text-sm font-medium text-ink/80">Phone Number</label>
            <input type="tel" wire:model="phone" placeholder="98XXXXXXXX" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
            @error('phone') <p class="mt-1 text-xs text-rose-brand-600">{{ $message }}</p> @enderror
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
            <div class="mt-8">
                <x-order-tracker :order="$order" />
            </div>
        @endif
    @endif
</div>
