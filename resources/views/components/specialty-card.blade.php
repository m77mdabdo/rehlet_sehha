@props([
    'specialty',
    'promoted' => false,
    /** Where the heading links. An anchor on the services page, the specialty page elsewhere. */
    'href' => null,
    'showDescription' => true,
])

@php
    $href ??= route('specialties.show', ['slug' => $specialty->slug]);
@endphp

{{--
    One clinical area, as a card.

    Shared by the homepage grid and the services page index so the promoted
    treatment is defined once. Two copies of this would drift the first time
    one of them was touched, and the whole point of the filled card is that it
    is the SAME device the packages comparison uses for its recommended column
    — the site reading as one site depends on it not being reimplemented.

    THE LIFT IS transform ONLY. No width, no margin, no padding, nothing in the
    box model, so hovering can never reflow the grid around it. Behind
    motion-safe, so a visitor who asked for less movement does not get a card
    that jumps under the pointer.

    IT GUIDES, IT DOES NOT SELL. Nothing on this grid has a price, links to
    checkout, or carries a "most popular" badge. The filled card is typographic
    hierarchy on a list of eight things that would otherwise read as eight
    identical tiles — see App\Support\PromotedSpecialty for why promotion here
    is explicitly not a recommendation.
--}}
<x-card
    :tone="$promoted ? 'ink' : 'paper'"
    class="flex h-full flex-col transition-[transform,box-shadow] duration-300 ease-out motion-safe:hover:-translate-y-1 motion-safe:hover:shadow-lg"
>
    <span @class([
        'inline-flex size-12 items-center justify-center rounded-md',
        'bg-white/10 text-teal' => $promoted,
        'bg-sage text-accent' => ! $promoted,
    ])>
        <x-icon :name="$specialty->icon" :size="24" />
    </span>

    {{-- The heading is the link, not the whole card: a block-level anchor makes
         a screen reader read the entire card as one enormous link name. --}}
    <h3 @class(['mt-4 font-display text-base font-semibold', 'text-white' => $promoted, 'text-ink' => ! $promoted])>
        <a
            href="{{ $href }}"
            @class(['rounded-sm transition-colors', 'hover:text-teal' => $promoted, 'hover:text-accent-dark' => ! $promoted])
        >
            {{ $specialty->name }}
        </a>
    </h3>

    @if ($showDescription)
        <p @class(['mt-2 flex-1 text-sm leading-relaxed', 'text-white/80' => $promoted, 'text-muted' => ! $promoted])>
            {{ $specialty->description }}
        </p>
    @endif

    <p @class([
        'mt-4 inline-flex items-center gap-2 text-sm font-medium',
        'text-teal' => $promoted,
        'text-accent-dark' => ! $promoted,
    ]) aria-hidden="true">
        {{ $slot->isNotEmpty() ? $slot : __('specialties.see_packages') }}
        <svg class="size-4 rtl:-scale-x-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 10h11M11 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </p>
</x-card>
