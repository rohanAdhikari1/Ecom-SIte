<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    #[Validate('required|min:8')]
    public string $password = '';

    #[Validate('required|same:password')]
    public string $passwordConfirmation = '';

    public function register(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        Auth::login($user);

        request()->session()->regenerate();

        $this->redirect(route('account.orders'), navigate: true);
    }
};
?>

<div class="mx-auto flex min-h-[70vh] max-w-md flex-col justify-center px-4 py-16 sm:px-6">
    <div class="rounded-2xl border border-brand-100 bg-white p-8 shadow-sm">
        <h1 class="font-display text-2xl font-semibold text-ink">Create an Account</h1>
        <p class="mt-1 text-sm text-ink/60">Track your orders and re-order your favourites faster.</p>

        <form wire:submit="register" class="mt-6 flex flex-col gap-4">
            <div>
                <label class="text-sm font-medium text-ink/80">Full Name</label>
                <input type="text" wire:model="name" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                @error('name') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink/80">Email</label>
                <input type="email" wire:model="email" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                @error('email') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink/80">Password</label>
                <input type="password" wire:model="password" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                @error('password') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-medium text-ink/80">Confirm Password</label>
                <input type="password" wire:model="passwordConfirmation" class="mt-1 w-full rounded-xl border border-brand-100 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none focus:ring-2 focus:ring-brand-200">
                @error('passwordConfirmation') <p class="mt-1 text-xs text-accent-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled" class="mt-2 rounded-full bg-brand-600 px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:scale-[1.02] hover:bg-brand-700 disabled:opacity-60">
                Create Account
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-ink/60">
            Already have an account?
            <a href="{{ route('login') }}" wire:navigate class="font-medium text-brand-600 hover:text-brand-700">Login</a>
        </p>
    </div>
</div>
