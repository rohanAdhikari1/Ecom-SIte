<?php

use App\Services\Cart\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public int $count = 0;

    public function mount(CartService $cart): void
    {
        $this->count = $cart->count();
    }

    #[On('cart-updated')]
    public function refreshCount(CartService $cart): void
    {
        $this->count = $cart->count();
    }
};
?>

<a
    href="{{ route('cart') }}"
    wire:navigate
    class="relative inline-flex items-center justify-center rounded-full p-2.5 text-ink transition hover:bg-brand-50 hover:text-brand-600"
    aria-label="View cart"
>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.98-4.706 2.545-7.201.116-.51-.28-.988-.803-.988H5.106M7.5 14.25 5.106 5.272M6 18.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
    </svg>

    @if ($count > 0)
        <span
            x-data="{ show: false }"
            x-init="show = true; setTimeout(() => show = false, 400)"
            :class="show && 'scale-125'"
            class="absolute -top-0.5 -right-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-brand-500 px-1 text-[11px] font-semibold text-white transition-transform duration-300"
        >
            {{ $count }}
        </span>
    @endif
</a>
