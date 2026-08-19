@props([
    'size' => 40,
])

{{--
    The icon-level mark: the plate ring, the capsule, and the gold pulse
    endpoint sitting outside the ring.

    The ring and capsule are drawn in currentColor so the mark inverts by
    itself — navy on the paper header, white on the navy footer — with no
    second asset and no colour prop to keep in sync. Only the gold dot is
    fixed, because it is the one element that carries the brand regardless of
    the surface behind it.

    The viewBox is shifted -17 on x so the mark reads as optically centred:
    the gold dot hangs off the right of the ring, and without the shift the
    whole thing sits visibly left of centre in a square box.

    Decorative by default — the lockup beside it already carries the name in
    real text, so announcing it again would just make a screen reader say the
    clinic's name twice.
--}}

<svg
    {{ $attributes->merge(['class' => 'shrink-0']) }}
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="-17 0 400 400"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    role="presentation"
    aria-hidden="true"
    focusable="false"
>
    <circle cx="200" cy="200" r="116" fill="none" stroke="currentColor" stroke-width="24" />
    <rect x="170" y="140" width="60" height="124" rx="30" fill="currentColor" />
    <circle cx="336" cy="202" r="26" fill="var(--color-gold)" />
</svg>
