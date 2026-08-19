@props([
    'size' => 'default',
])

@php
    $width = match ($size) {
        'narrow' => 'max-w-3xl',
        'wide' => 'max-w-7xl',
        default => 'max-w-6xl',
    };
@endphp

<div {{ $attributes->merge(['class' => "mx-auto w-full {$width} px-5 sm:px-8"]) }}>
    {{ $slot }}
</div>
