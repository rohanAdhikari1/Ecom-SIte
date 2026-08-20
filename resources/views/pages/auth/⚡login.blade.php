<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    public bool $remember = true;

    public function login(): void
    {
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower($this->email)).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->addError('email', "Too many login attempts. Please try again in {$seconds} seconds.");

            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey, 60);

            $this->addError('email', 'These credentials do not match our records.');

            return;
        }

        RateLimiter::clear($throttleKey);

        request()->session()->regenerate();

        $this->redirect(route('account.orders'), navigate: true);
    }
};
?>

<div class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-4 py-16 sm:px-6">
    <div class="rounded-2xl border border-brand-100 bg-white p-8 shadow-sm">
        <h1 class="font-display text-2xl font-semibold text-ink">Welcome Back</h1>
        <p class="mt-1 text-sm text-ink/60">Login to track your orders.</p>

        <form wire:submit="login" class="mt-6 flex flex-col gap-4">
            <div>
                <label class="text-sm font-medium text-ink/80">Email</label>
                <input type="email" wire:model="email" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                @error('email') <p class="mt-1 text-xs text-rose-brand-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink/80">Password</label>
                <input type="password" wire:model="password" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                @error('password') <p class="mt-1 text-xs text-rose-brand-600">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-ink/70">
                <input type="checkbox" wire:model="remember" class="rounded border-brand-300 text-brand-600 focus:ring-brand-400">
                Remember me
            </label>

            <button type="submit" wire:loading.attr="disabled" class="mt-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.02] hover:bg-brand-700 disabled:opacity-60">
                Login
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-ink/60">
            Don't have an account?
            <a href="{{ route('register') }}" wire:navigate class="font-medium text-brand-600 hover:text-brand-700">Sign up</a>
        </p>
    </div>
</div>
