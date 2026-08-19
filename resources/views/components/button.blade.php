@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'size' => 'default',
])

@php
    // Every colour here resolves to a design token — no component invents one.
    $variants = [
        'primary' => 'bg-accent text-white shadow-sm hover:bg-accent-dark',
        'ghost' => 'bg-transparent text-ink ring-1 ring-line hover:bg-sage',
        'light' => 'bg-white text-ink shadow-sm hover:bg-sage',
    ];

    $sizes = [
        'default' => 'px-6 py-3 text-sm',
        'lg' => 'px-8 py-4 text-base',
        'sm' => 'px-4 py-2 text-sm',
    ];

    $classes = implode(' ', [
        'inline-flex items-center justify-center gap-2 rounded-pill font-medium',
        'transition-colors duration-200',
        // No focus:outline-none anywhere: the base stylesheet owns the focus
        // ring so it can never be removed from one component by accident.
        $sizes[$size] ?? $sizes['default'],
        $variants[$variant] ?? $variants['primary'],
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
