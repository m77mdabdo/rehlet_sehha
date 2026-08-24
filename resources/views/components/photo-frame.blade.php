@props([
    'ratio' => 'aspect-4/5',
    'label',
    'size' => 120,
])

{{--
    Space reserved for a photograph that does not exist yet.

    NEVER A STOCK STAND-IN. On a page about who will be treating you, a
    photograph of somebody else's clinician is not a placeholder — it is a
    false claim, and one a patient has no way to check. The same reasoning that
    keeps identifiable faces away from condition content applies twice as hard
    to a face presented as the practitioner's.

    And never a broken frame either. This is a deliberate, finished-looking
    empty state — the brand mark on sage, the treatment the homepage already
    uses — so the page reads as complete rather than as something that failed
    to load.

    THE SHAPE IS THE POINT. It holds the exact aspect and position the real
    photograph will occupy, so when the clinic's own pictures arrive they drop
    into this box and nothing around them moves.
--}}

<div {{ $attributes->merge(['class' => "relative flex {$ratio} w-full items-center justify-center overflow-hidden rounded-2xl bg-sage ring-1 ring-line"]) }}>
    <x-logo.mark-full :size="$size" class="text-ink/20" />
    <span class="sr-only">{{ $label }}</span>
</div>
