@props([
    'icon',
    'title',
    'body' => null,
    'href' => null,
    'linkLabel' => null,
    /** Filled at rest. The others fill on hover. */
    'promoted' => false,
])

{{--
    An icon feature card.

    THE FILL IS THE HOVER STATE, AND THE PROMOTED CARD IS THE HOVER STATE MADE
    PERMANENT. That is the whole idea: one card already shows what the others do
    when you reach for them, so the promotion reads as "this one first" rather
    than as a different kind of object. Two visual languages — a filled card and
    a hover effect that did something else — would be two things to learn.

    COLOUR AND TRANSFORM ONLY. No border-width change, no padding change,
    nothing in the box model, so a hovered card cannot nudge its neighbours. The
    lift is behind motion-safe; the colour change is not, because a visitor who
    asked for less motion still needs to see what she is pointing at.

    THE BORDER STAYS AT REST AND THERE IS NO SHADOW. A grid of shadowed cards
    reads as a dashboard. A hairline is enough to say "this is a thing", and
    keeping the shadow for the hover means the lift has something to lift out of.
--}}
<div
    @class([
        'group flex h-full flex-col rounded-xl border p-7 text-center transition-[background-color,border-color,color,transform,box-shadow] duration-300 ease-out sm:p-8',
        'motion-safe:hover:-translate-y-1 hover:shadow-lg',
        'border-ink bg-ink text-white' => $promoted,
        'border-line bg-white hover:border-ink hover:bg-ink' => ! $promoted,
    ])
    data-feature-card
>
    {{--
        The icon sits in a tinted disc so it has presence at this size without
        being drawn heavier than the set it belongs to. The disc inverts with
        the card; the glyph is currentColor and follows on its own.
    --}}
    <span
        @class([
            'mx-auto inline-flex size-16 items-center justify-center rounded-2xl transition-colors duration-300',
            'bg-white/10 text-teal' => $promoted,
            'bg-sage text-accent group-hover:bg-white/10 group-hover:text-teal' => ! $promoted,
        ])
        aria-hidden="true"
    >
        <x-icon :name="$icon" :size="30" />
    </span>

    <h3 @class([
        'mt-6 font-display text-lg font-semibold text-balance transition-colors duration-300',
        'text-white' => $promoted,
        'text-ink group-hover:text-white' => ! $promoted,
    ])>
        @if ($href)
            <a href="{{ $href }}" class="rounded-sm">{{ $title }}</a>
        @else
            {{ $title }}
        @endif
    </h3>

    @if ($body)
        <p @class([
            'mt-3 flex-1 text-sm leading-relaxed text-pretty transition-colors duration-300',
            'text-white/80' => $promoted,
            'text-muted group-hover:text-white/80' => ! $promoted,
        ])>{{ $body }}</p>
    @endif

    @if ($linkLabel)
        {{--
            Decorative: the heading above is already the link, and a second
            anchor to the same place gives a screen reader the destination
            twice under two different names.
        --}}
        <p @class([
            'mt-6 inline-flex items-center justify-center gap-2 text-sm font-medium transition-colors duration-300',
            'text-teal' => $promoted,
            'text-accent-dark group-hover:text-teal' => ! $promoted,
        ]) aria-hidden="true">
            {{ $linkLabel }}
            <svg class="size-4 rtl:-scale-x-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 10h11M11 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </p>
    @endif
</div>
