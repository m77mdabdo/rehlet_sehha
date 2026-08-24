@props(['id' => null])

{{--
    A full-bleed navy band carrying one sentence, large.

    Its job is rhythm, not information. On a reference site this is where a
    photograph would sit — a break between two blocks of reading that lets the
    next one start fresh. We have no photography, so the break is made of
    colour, scale and space instead, which is cheaper, loads instantly and
    never looks like a placeholder waiting for a real image.

    Use it once or twice a page. Three times and it stops being a breath.

    THE SENTENCE MUST BE ABLE TO STAND ALONE. If it needs the paragraph above
    it to make sense, it is a pull quote and belongs next to that paragraph.

    The pattern is drawn in CSS — see app.css — rather than shipped as an
    asset. At 6% white it lifts the darkest pixel to roughly #1C374F, which
    leaves white text far above AA; HeroContrastTest's sibling in
    PackagesPageTest measures the real composited pixels rather than assuming.
--}}

<section
    @if ($id) id="{{ $id }}" @endif
    class="statement-band bg-ink py-24 text-white sm:py-32"
    {{ $attributes }}
>
    <x-container size="narrow">
        <p class="text-center font-display text-2xl leading-[1.25] font-semibold text-balance sm:text-4xl lg:text-[2.75rem]">
            {{ $slot }}
        </p>
    </x-container>
</section>
