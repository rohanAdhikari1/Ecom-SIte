@props(['order'])

@php
    $statuses = [
        \App\Enums\OrderStatus::Pending,
        \App\Enums\OrderStatus::Confirmed,
        \App\Enums\OrderStatus::Preparing,
        \App\Enums\OrderStatus::OutForDelivery,
        \App\Enums\OrderStatus::Delivered,
    ];
@endphp

@if ($order->status === \App\Enums\OrderStatus::Cancelled)
    <div class="rounded-2xl border border-accent-200 bg-accent-50 p-6 text-center">
        <p class="font-semibold text-accent-700">This order was cancelled.</p>
        <p class="mt-1 text-sm text-accent-600">Contact us if you believe this is a mistake.</p>
    </div>
@else
    <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <span class="font-display font-semibold text-ink">{{ $order->order_number }}</span>
            <span class="text-sm text-ink/60">{{ \App\Support\Money::format($order->total) }}</span>
        </div>

        <div class="mt-8">
            <div class="relative flex justify-between">
                <div class="absolute left-0 right-0 top-4 h-0.5 bg-brand-100"></div>
                @php
                    $currentIndex = array_search($order->status, $statuses, true);
                @endphp
                @foreach ($statuses as $index => $status)
                    <div class="relative z-10 flex flex-col items-center gap-2" style="width: {{ 100 / count($statuses) }}%">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold {{ $index <= $currentIndex ? 'bg-brand-600 text-white' : 'bg-white text-ink/40 border border-brand-200' }}">
                            @if ($index < $currentIndex)
                                ✓
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                        <span class="text-center text-[11px] font-medium {{ $index <= $currentIndex ? 'text-brand-700' : 'text-ink/40' }}">
                            {{ $status->getLabel() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-10 border-t border-brand-100 pt-4">
            <h3 class="text-sm font-semibold text-ink">Status History</h3>
            <ul class="mt-3 flex flex-col gap-3">
                @foreach ($order->statusHistories->sortByDesc('created_at') as $history)
                    <li class="flex items-start gap-3 text-sm">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>
                        <div>
                            <p class="font-medium text-ink">{{ $history->status->getLabel() }}</p>
                            <p class="text-xs text-ink/50">{{ $history->created_at->format('j M Y, g:i A') }}</p>
                            @if ($history->note)
                                <p class="mt-0.5 text-xs text-ink/60">{{ $history->note }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        @if ($order->items->isNotEmpty())
            <div class="mt-8 border-t border-brand-100 pt-4">
                <h3 class="text-sm font-semibold text-ink">Order Items</h3>
                <div class="mt-3 flex flex-col gap-3">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between text-sm">
                            <div>
                                <p class="font-medium text-ink">{{ $item->quantity }} × {{ $item->product_name }}</p>
                                <p class="text-xs text-ink/60">
                                    {{ $item->options->pluck('option_value_label')->implode(' · ') }}
                                </p>
                            </div>
                            <span class="font-medium text-ink">{{ \App\Support\Money::format($item->line_total) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endif
