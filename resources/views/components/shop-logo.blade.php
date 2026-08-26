@props(['name' => 'Shop', 'class' => 'h-11 w-11 text-base'])

<span
    {{ $attributes->merge(['class' => "$class inline-flex shrink-0 items-center justify-center rounded-full bg-linear-to-br from-brand-500 to-brand-700 font-display font-semibold text-white shadow-md shadow-brand-600/20"]) }}
    aria-hidden="true"
>
    {{ \Illuminate\Support\Str::of($name)->trim()->substr(0, 1)->upper() ?: 'S' }}
</span>
