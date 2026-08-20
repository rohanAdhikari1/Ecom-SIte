<?php

use Illuminate\Support\Collection;
use Livewire\Component;

new class extends Component
{
    public Collection $orders;

    public function mount(): void
    {
        $this->orders = auth()->user()->orders()->with('items')->latest()->get();
    }
};
?>

<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <h1 class="font-display text-3xl font-semibold text-ink">My Orders</h1>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm font-medium text-ink/60 hover:text-rose-brand-600">Logout</button>
        </form>
    </div>

    @if ($orders->isEmpty())
        <div class="mt-10 rounded-2xl border border-dashed border-brand-200 py-20 text-center text-ink/60">
            You haven't placed any orders yet.
            <a href="{{ route('shop') }}" wire:navigate class="mt-4 block font-medium text-brand-600 hover:text-brand-700">Start Shopping →</a>
        </div>
    @else
        <div class="mt-8 flex flex-col gap-4">
            @foreach ($orders as $order)
                <a
                    href="{{ route('order.track') }}#order-{{ $order->id }}"
                    wire:navigate
                    class="block rounded-2xl border border-brand-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="font-display font-semibold text-ink">{{ $order->order_number }}</p>
                            <p class="text-xs text-ink/50">{{ $order->created_at->format('j M Y, g:i A') }} &middot; {{ $order->items->count() }} item(s)</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700">
                                {{ $order->status->getLabel() }}
                            </span>
                            <span class="font-semibold text-ink">{{ \App\Support\Money::format($order->total) }}</span>
                            <span class="text-brand-500">→</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
