@props([
    'as' => 'div',
    'padding' => true,
])

<{{ $as }}
    {{ $attributes->merge([
        'class' => 'rounded-lg bg-white ring-1 ring-line shadow-sm '.($padding ? 'p-6 sm:p-8' : ''),
    ]) }}
>
    {{ $slot }}
</{{ $as }}>
