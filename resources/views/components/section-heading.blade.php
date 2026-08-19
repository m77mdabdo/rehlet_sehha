@props([
    'eyebrow' => null,
    'title' => null,
    'lead' => null,
    'level' => 'h2',
    'align' => 'start',
])

@php
    // text-start / text-center only — never text-left, which would pin the
    // heading to the wrong edge of an Arabic page.
    $alignment = $align === 'center' ? 'text-center items-center' : 'text-start items-start';
@endphp

<div {{ $attributes->merge(['class' => "flex flex-col {$alignment} gap-3"]) }}>
    @if ($eyebrow)
        <p class="text-sm font-medium tracking-wide text-accent-dark uppercase">
            {{ $eyebrow }}
        </p>
    @endif

    @if ($title)
        <{{ $level }} class="font-display text-3xl font-semibold text-balance text-ink sm:text-4xl">
            {{ $title }}
        </{{ $level }}>
    @endif

    @if ($lead)
        <p class="max-w-2xl text-base leading-relaxed text-pretty text-muted sm:text-lg">
            {{ $lead }}
        </p>
    @endif

    {{ $slot }}
</div>
