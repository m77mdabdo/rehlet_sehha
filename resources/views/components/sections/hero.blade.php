@props(['media' => true])

{{--
    Hero.

    THE COPY SITS ON THE VIDEO. THERE IS NO PANEL, AND THAT IS THE POINT.

    Until 8.16 the copy sat on a translucent panel that covered a little over
    half the frame at every width, with the case card straddling its edge. It
    was legible and it was safe, and it meant the clip — which we vet frame by
    frame, re-encode to a budget and pay for in bytes on Egyptian mobile data —
    showed as a strip down one side. We were buying footage the visitor barely
    saw.

    So the panel is gone and the legibility is now a job for a GRADIENT rather
    than a surface: dense where the words are, near-nothing on the far side, so
    the picture is actually present. The full argument, the stops and the
    measurements are on the scrim elements below.

    WHAT THAT COSTS, HONESTLY. On a panel the contrast question is answered once
    — 93% opacity, one composite, done. On a gradient it has to be answered per
    element, per breakpoint, per locale, against the brightest frame the clip
    can produce, and re-answered whenever the footage changes. HeroContrastTest
    holds the numbers and, for the cases where a sample is not good enough, a
    bound against a pure white pixel.

    THE CASE CARD IS NO LONGER HERE. It is its own section immediately below —
    see components/sections/case-card.blade.php, which carries the clinical
    argument about what it may and may not show. It moved because it is content,
    not decoration: a heading, a definition list and a disclaimer cannot sit on
    moving footage without either an opaque background (which is the panel we
    just removed, wearing a hat) or a fight with the picture that legibility
    loses. Moving it out also ends its overlap with the stats strip
    structurally, at every breakpoint, rather than by tuning offsets.

    ---------------------------------------------------------------------------
    THE BACKGROUND VIDEO
    ---------------------------------------------------------------------------

    WHAT THE CLIP IS ALLOWED TO SHOW. Two clips have been rejected here already
    and both were rejected on a frame nobody would have looked at. The first
    opened on a digital kitchen scale, filmed past a tailor's tape, and closed
    on gym treadmills. The second passed its thumbnail and held a glucose meter,
    an insulin diagram, a medication record and a printed list of named foods.

    Frame 0 is the poster, and the poster is the ONE image that reaches the
    visitors we deliberately spare the video — reduced motion, Save-Data, 2g and
    3g. A scale, a tape measure, a numeric readout or anything that reads as a
    doctor's surgery in this clinic's hero contradicts the whole offer, and it
    contradicts it in the image shown to the people we were trying to protect.

    If this file is ever replaced, open every frame of the new one. Not the
    thumbnail, not frame 0 alone — a clip rejected in 8.16 had an innocuous hand
    on a clipboard at frame 0 and a man watching two people on a bed at frame
    400.

    LAYER ORDER. Section background colour, then poster, then video, then the
    scrims, then the header scrim, then content. `isolate` keeps the negative
    z-indices inside this section instead of sliding behind the page.

    ONE POSTER SIZE, NOT A RESPONSIVE SET, AND THAT IS A MEASUREMENT.

    Chrome caps an image's LCP size by its INTRINSIC area, and does not cap a
    video's. A 828-wide poster is smaller than the area this hero paints on a
    phone, so the video — same pixels, same box — outranked it the moment it
    faded in, and LCP jumped from 1.27s to 2.82s for a crossfade between two
    identical frames. Serving the 1280 poster everywhere makes the two tie, and
    a tie keeps the first one, which is the poster. Measured: 2.82s to 1.39s.
    The extra bytes on a phone cost about 130ms of first paint and buy back 1.4
    seconds of the metric that actually scores this page.

    THE POSTER IS AN <img>, NOT A poster="" ATTRIBUTE. It is a real element, so
    the preload scanner finds it, it can be given fetchpriority, and it stays
    painted underneath forever — the video fades in on top of it and never
    replaces it. Every failure path therefore lands on the poster rather than on
    a black box: no JS, reduced motion, Save-Data, a 404, a codec the browser
    will not decode. The scrims sit above the poster, so they work over it too
    and those visitors get the same composition rather than a bare photograph.

    NOTHING ABOUT THE VIDEO CAN SHIFT THE LAYOUT. Both the poster and the video
    are absolutely positioned and out of flow, and the section carries a fixed
    minimum height per breakpoint, so it is sized identically whether the video
    ever arrives or not.
--}}

@php
    /*
     * Save-Data is answered on the server as well as in the browser. If the
     * request says the visitor is conserving data, the source never reaches the
     * page at all — a client-side check can only skip a fetch it has already
     * been told to make, and this skips telling it.
     *
     * Egyptian mobile data is the normal case here, not the edge case.
     */
    $saveData = request()->header('Save-Data') === 'on';
    $showVideo = $media && ! $saveData;

    /*
     * WHERE THE CROP IS ANCHORED, AND WHY IT IS ONE NUMBER RATHER THAN FOUR.
     *
     * There is one <video> element and four beats, so there is one crop for all
     * of them. It has to keep every beat's subject in frame at the tightest
     * crop on the site, which is the phone: a 16:9 source in a 390x576 box
     * shows about 35% of the source width, and the wrong anchor cuts the hands
     * out of a shot whose whole subject is a pair of hands.
     *
     * Measured off the four beats, as a fraction of source width:
     *
     *     talk    hands and paper   25-55%
     *     write   hand and pen      28-75%
     *     notes   hand and pen      25-45%
     *     plan    hand and pen      30-60%
     *
     * 42% puts the visible window at 27-62% on a phone, which contains all four.
     * The vertical 45% is for the other extreme: at 1920 the box is 2.7:1 against
     * a 1.78:1 source, so a third of the frame height is cropped, and 45% is what
     * keeps the hands in `talk` — the highest of the four in frame — off the top
     * edge.
     *
     * IF THE FOOTAGE CHANGES, RE-MEASURE. This is a property of the clip.
     */
    $focus = '42% 45%';

    /*
     * THE SCRIM RUNS FROM THE SIDE THE COPY IS ON, so it has to know which side
     * that is. `to left` means the gradient TRAVELS left, which puts its opaque
     * end on the right — where Arabic copy sits.
     */
    $rtl = in_array(app()->getLocale(), ['ar'], true);
    $scrimTravel = $rtl ? 'to left' : 'to right';
@endphp

@if ($media)
    {{--
        The poster is the largest thing in the viewport, so it is the LCP
        element whether we like it or not — a full-bleed hero image always is.
        Preloading it moves the fetch ahead of the stylesheet instead of behind
        it, which is the only part of that timing we control.
    --}}
    @push('head')
        <link
            rel="preload"
            as="image"
            fetchpriority="high"
            href="{{ asset(config('hero.poster')) }}"
            imagesrcset="{{ asset(config('hero.poster_webp')) }} 1280w"
            imagesizes="100vw"
            type="image/webp"
        >
    @endpush
@endif

{{--
    -mt-18 pulls this section up under the header.

    The header is position:sticky, which means it OCCUPIES FLOW SPACE — it is
    not an overlay. Without this the hero starts below it, "transparent" header
    shows the page background rather than the footage, and the white nav ends up
    on paper where it is close to invisible.

    THE HEIGHT IS FIXED PER BREAKPOINT so nothing reflows when the webfont swaps
    or a line of copy changes length. It is min-h rather than h: content that
    somehow grew past it would push the section taller instead of spilling out
    of it, which is the failure mode you want of the two.
--}}
<section
    @class([
        'relative isolate flex items-end overflow-hidden bg-ink pt-28 pb-12',
        'min-h-[48rem] sm:min-h-[48rem] lg:min-h-[44rem]',
        'sm:pb-16 lg:items-center lg:pt-40 lg:pb-24',
        '-mt-18' => $media,
    ])
    data-hero
>
    @if ($media)
        {{-- Painted on first contentful paint and never taken away. --}}
        <picture>
            <source
                type="image/webp"
                srcset="{{ asset(config('hero.poster_webp')) }} 1280w"
                sizes="100vw"
            />
            <img
                src="{{ asset(config('hero.poster')) }}"
                alt=""
                aria-hidden="true"
                fetchpriority="high"
                decoding="async"
                style="object-position: {{ $focus }}"
                class="absolute inset-0 -z-20 size-full object-cover"
                data-hero-poster
            />
        </picture>

        @if ($showVideo)
            {{--
                No src. The source is handed over by hero-video.js after first
                paint, and only once it has checked reduced motion, Save-Data
                and the effective connection type — which is the only way to
                *guarantee* no fetch, since preload="none" is a hint a browser
                is free to ignore.

                muted + playsinline are what make autoplay permissible at all;
                without them iOS opens the video fullscreen. No controls, no
                download, not focusable, hidden from assistive technology: it
                is decoration and carries no information.
            --}}
            <video
                class="absolute inset-0 -z-20 size-full object-cover opacity-0 transition-opacity duration-1000 ease-out motion-reduce:transition-none"
                style="object-position: {{ $focus }}"
                muted
                loop
                playsinline
                preload="none"
                aria-hidden="true"
                tabindex="-1"
                disablepictureinpicture
                controlslist="nodownload noplaybackrate noremoteplayback"
                data-hero-video
                data-src="{{ asset(config('hero.video')) }}"
                {{-- Plain JSON, NOT Js::from(). Js::from renders JavaScript
                     source — literally `JSON.parse('...')` — which is right
                     inside a <script> and wrong in an attribute, where the
                     value is read as data. Putting it here parses to the
                     string "JSON.parse(..." and the cycle silently never
                     starts: every line stays at opacity 0 and the static one
                     is left showing, which looks exactly like the intended
                     no-JS fallback. It cost a rebuild to notice.

                     Only the three fields the script needs, so the payload is
                     not the whole scene map. --}}
                data-hero-beats-source="{{ json_encode(array_map(
                    fn (array $beat): array => [
                        'key' => $beat['key'],
                        'start' => $beat['start'],
                        'end' => $beat['end'],
                    ],
                    config('hero.beats'),
                )) }}"
            ></video>
        @endif

        {{--
            THE SCRIM. This is what replaced the panel, and it is the whole
            technique of this hero.

            A flat overlay dims the picture evenly, which means paying for
            footage nobody can see in order to make text readable over the part
            of it nobody is looking at. These gradients spend the ink where the
            copy is and almost none where it is not, so the far side of the
            frame stays bright and the clip is actually visible.

            TWO OF THEM, BECAUSE THE LAYOUT HAS TWO SHAPES.

            Below lg the copy is full-width and sits at the bottom, so a
            sideways gradient would mean nothing: this one runs bottom-to-top
            and leaves the top of the frame almost untouched. That top third is
            where the phone visitor actually sees the video.

            At lg and up the copy is a column on one side, so the gradient runs
            from that side. It is DIRECTION-AWARE rather than mirrored with a
            transform: `to left` puts the opaque end on the right, where Arabic
            copy is; English gets `to right`.

            THE STOPS ARE max(rem, %), AND BOTH HALVES EARNED THEIR PLACE.

            Percent alone failed at 1024: the copy column is a fixed 36rem, so
            it is 56% of a 1024 frame and 30% of a 1920 one, and a gradient
            dense for "the first 26%" left the lead on bare picture at the
            small end. Measured 3.63:1 against a needed 4.5.

            Rem alone then failed at 1920, for the opposite reason. The column
            is not at the window's edge — the container centres it, and that
            inset GROWS with the window: 0 at 1152 and 24rem at 1920. A dense
            zone measured from the viewport edge had already faded to 0.2 by
            the time it reached the copy. Measured 1.44:1 on the lead.

            So each stop takes whichever is larger. The rem term holds the
            narrow end where the column touches the gutter; the percent term
            tracks the container's centring at the wide end. What stays roughly
            constant either way is the fraction of the frame left clean —
            about a sixth at 1440 and 1920, less at 1024, where there is
            honestly not room for both.

            THE STOPS ARE MEASURED, NOT CHOSEN. See HeroContrastTest for the
            numbers and the bound. The short version: white body copy needs
            4.5:1 against the worst pixel that can appear behind it, the
            brightest beat of this clip measures 146 of 255, and the densities
            below are the least ink that clears it.
        --}}
        <div
            aria-hidden="true"
            data-hero-scrim-copy
            class="absolute inset-0 -z-10 lg:hidden"
            style="background: linear-gradient(to top,
                rgb(14 46 77 / 0.94) 0,
                rgb(14 46 77 / 0.93) 30rem,
                rgb(14 46 77 / 0.89) 38rem,
                rgb(14 46 77 / 0.35) 44rem,
                rgb(14 46 77 / 0.04) 48rem)"
        ></div>

        <div
            aria-hidden="true"
            data-hero-scrim-copy-lg
            class="absolute inset-0 -z-10 hidden lg:block"
            style="background: linear-gradient({{ $scrimTravel }},
                rgb(14 46 77 / 0.94) 0,
                rgb(14 46 77 / 0.93) max(26rem, 30%),
                rgb(14 46 77 / 0.88) max(38rem, 53%),
                rgb(14 46 77 / 0.50) max(44rem, 62%),
                rgb(14 46 77 / 0.16) max(50rem, 72%),
                rgb(14 46 77 / 0.00) max(56rem, 82%))"
        ></div>

        {{--
            And a floor. The credential chips sit at the bottom of the column
            and, at lg and up, reach further across the frame than anything
            above them — far enough to run out from under the directional
            gradient. This is the second line under exactly those.
        --}}
        <div
            aria-hidden="true"
            data-hero-scrim-floor
            class="absolute inset-x-0 bottom-0 -z-10 h-56"
            style="background: linear-gradient(to top, rgb(14 46 77 / 0.55), rgb(14 46 77 / 0.00))"
        ></div>

        {{--
            The header scrim. The header is transparent over this section, and
            white nav links over a bright frame would fail on their own —
            measured, not guessed. A gradient rather than a bar because the
            point of the transparent header is that it has no edge.
        --}}
        <div
            class="absolute inset-x-0 top-0 -z-10 h-40 bg-linear-to-b from-ink/85 via-ink/45 to-transparent"
            aria-hidden="true"
            data-hero-scrim
        ></div>
    @endif

    <x-container class="relative w-full">
        {{--
            THE COPY, ON THE PICTURE.

            The column is capped rather than half-width: a measure that runs the
            full side of a 1920 frame is unreadable however good the contrast is,
            and capping it is also what leaves the far side of the frame clear.

            THERE IS NO AUTO MARGIN ON IT, and that is the fix rather than the
            omission. A capped block with no margin sits at its INLINE-START
            edge on its own: the right in Arabic, the left in English. The first
            attempt used ms-auto, which sets margin-inline-start:auto and
            therefore pushes the column AWAY from the start — it put the Arabic
            copy on the left, under the hands, with the scrim on the right.

            TEXT-SHADOW AS A SECOND LINE, NOT AS THE FIRST. The gradient is what
            makes this legible; the shadow is insurance for the worst pixel in a
            frame nobody has measured yet. It is tight and dark — 1px and 2px
            offsets, small blurs — because that reads as depth. The version with
            a wide soft blur reads as a glow, which looks like a mistake and
            fattens every glyph.
        --}}
        <div class="max-w-xl lg:max-w-[36rem] [text-shadow:0_1px_2px_rgb(2_12_24_/_0.55),0_2px_10px_rgb(2_12_24_/_0.30)]">
            <p class="text-sm font-medium tracking-wide text-teal uppercase">
                {{ __('home.hero.eyebrow') }}
            </p>

            {{--
                Heavier and tighter than the same heading would be on paper.
                Type on moving footage loses apparent weight to the picture
                behind it, and a line-height tuned for a white page opens gaps
                the video shows through.
            --}}
            <h1 class="mt-3 font-display text-[1.875rem] leading-[1.08] font-bold text-balance text-white sm:text-5xl lg:text-[3.25rem]">
                {{ __('home.hero.title') }}
            </h1>

            {{-- Looser than the title, and capped short of the column so the
                 two do not read as one block of text. --}}
            <p class="mt-4 max-w-lg text-base leading-7 text-pretty text-white/85 sm:mt-5 sm:text-lg sm:leading-8">
                {{ __('home.hero.lead') }}
            </p>

            <div class="mt-6 flex flex-wrap items-center gap-3 sm:mt-8">
                <x-button :href="route('booking')" size="lg">
                    {{ __('home.hero.cta') }}
                </x-button>

                <x-button variant="outline" size="lg" href="#packages">
                    {{ __('home.hero.secondary_cta') }}
                </x-button>
            </div>

            {{--
                THE SCENE-SYNCED LINE. Caption weight, still beneath the CTAs.

                HOW THIS COSTS NOTHING IN LAYOUT SHIFT — and it is worth reading,
                because the obvious implementation does not.

                Every line, including the static one, is stacked in the SAME grid
                cell. The grid is therefore exactly as tall as its tallest child,
                the browser works that out from the real strings during normal
                layout, and nothing ever resizes when a line swaps. There is no
                reserved pixel height, no measurement in JavaScript and no magic
                number — which matters because Arabic and English wrap
                differently and a number correct for one locale would be wrong
                for the other. Each locale reserves its own height because each
                lays out its own words.

                WHAT A SCREEN READER GETS IS ONE SENTENCE. The static line is
                real markup, present before any JavaScript runs, and is the only
                one in the accessibility tree. The four cycling lines are
                decorative duplicates of a picture that is itself decorative, so
                they are aria-hidden — announcing a line change every five
                seconds would be an interruption, not information.

                AND WITH NO JAVASCRIPT, NOTHING MOVES. The cycling lines ship at
                opacity-0 and are only ever revealed by hero-beats.js, which
                starts only once the video is actually playing. Reduced motion,
                Save-Data, 2g/3g, no JS and a failed video therefore all land in
                the same place with no special case: the poster, and this one
                sentence.
            --}}
            <div
                class="mt-6 grid border-s-2 border-white/30 ps-4 text-sm leading-relaxed text-balance text-white/80 sm:mt-7 sm:text-base"
                data-hero-beats
            >
                {{-- The one sentence. Never removed, never hidden from
                     assistive technology. --}}
                <p
                    class="col-start-1 row-start-1 transition-opacity duration-500 motion-reduce:transition-none"
                    data-hero-beat-static
                >{{ __('home.hero.beats.'.config('hero.static_beat')) }}</p>

                @foreach (config('hero.beats') as $beat)
                    <p
                        class="col-start-1 row-start-1 opacity-0 transition-[opacity,transform] duration-500 ease-out motion-reduce:transition-none"
                        aria-hidden="true"
                        data-hero-beat="{{ $beat['key'] }}"
                    >{{ __('home.hero.beats.'.$beat['key']) }}</p>
                @endforeach
            </div>

            {{-- Credential chips: what the clinic is, never what it promises. --}}
            <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-3 sm:mt-8">
                @foreach (__('home.hero.chips') as $chip)
                    <li class="flex items-center gap-2 text-sm text-white/85">
                        <svg class="size-4 text-teal" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="m4 10.5 4 4 8-9" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ $chip }}
                    </li>
                @endforeach
            </ul>
        </div>
    </x-container>
</section>
