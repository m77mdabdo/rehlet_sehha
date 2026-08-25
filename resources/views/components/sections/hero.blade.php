@props(['media' => true])

{{--
    Hero.

    THE CASE CARD SHOWS QUALITATIVE PROGRESS ONLY — energy, sleep consistency,
    labs in range, plan adherence.

    No weight, no BMI, no calorie counts, no body-fat percentage, here or
    anywhere else on this site. That is a deliberate clinical and brand
    decision, not an oversight, and it is not a detail to "improve" later:

      - A number on a homepage becomes the thing a patient measures herself
        against before she has ever spoken to a clinician, and the number that
        makes her stop coming when it stalls for a fortnight — which is exactly
        when a plan is usually working.
      - Weight moves for reasons that have nothing to do with adherence: water,
        cycle, illness, muscle. Publishing it as the progress metric teaches
        the wrong causal story.
      - The metrics shown here are the ones the clinic actually adjusts a plan
        on, so the card doubles as an honest statement of method.

    If a future task asks for weight anywhere, that is a conversation with the
    clinician, not a ticket.

    The card is also explicitly labelled as an illustration. Presenting a
    fabricated patient record as a real one would be a different problem again.

    THERE IS NO ADHERENCE PERCENTAGE HERE ANY MORE, AND THERE MUST NOT BE ONE.

    This card used to end on "86%" over a progress bar. It went for the same
    reason the plate builder has no calorie count: a number attached to a
    patient's own behaviour is something she can fail at, and a score with a bar
    under it invites her to grade herself before she has spoken to anybody. It
    also read as an app dashboard rather than as a clinic.

    Adherence is now an ordinary row saying an ordinary thing, in the register
    the rows around it already used — "better than at the start", "within
    normal range". PlateFeedbackHasNoNumbersTest fails the build if a
    percentage, a meter or a progress bar comes back.

    ---------------------------------------------------------------------------
    THE COMPOSITION
    ---------------------------------------------------------------------------

    ONE object, not two. A copy panel with the case card straddling its inner
    edge, dropped low so the two read as a single thing with depth. They used to
    be two rectangles of near-identical size, radius and elevation sitting side
    by side, which is what flattened them into a pair of cards on a photo
    instead of a hero.

    They are now deliberately unlike each other: the panel is wide, softly
    rounded, translucent and barely raised; the card is small, tightly rounded,
    opaque and clearly lifted. Difference in radius and elevation is what makes
    one read as behind and the other as in front.

    THE PANEL SITS ON THE RIGHT IN BOTH LOCALES, WHICH IS DELIBERATE.

    The strongest frame in the clip — the top-down plate that opens it — sits
    left of centre, spanning roughly a tenth to seven tenths of the frame. Its
    natural home is therefore the left of the composition, and the panel has to
    be opposite it. Mirroring the panel with the text direction would put it
    straight on top of the plate in English, and the only ways out are worse:
    mirroring the footage is forbidden (a video that flips is a video that
    lies about which hand somebody chops with), and shifting the frame far
    enough to clear the panel needs about a 40% upscale of a 1280-wide source,
    which softens the one genuinely sharp thing on the page.

    So the image composition stays put and only the text alignment flips. For
    an Arabic-first clinic whose canonical layout is the Arabic one, that is
    the right thing to hold constant.

    THE FOOTAGE IS FRAMED AROUND THE PLATE. object-position is set so the plate
    is what fills the unobstructed area rather than a corner of it, and it
    rhymes with the plate builder further down the page.

    ---------------------------------------------------------------------------
    THE BACKGROUND VIDEO
    ---------------------------------------------------------------------------

    Full-bleed footage behind this section only, with the copy on its own panel
    rather than laid directly over the picture.

    WHAT THE CLIP IS ALLOWED TO SHOW. The original 12.7s master opened on a
    digital kitchen scale weighing a bowl of vegetables, filmed over the
    shoulder of a woman with a tailor's measuring tape round her neck, and
    closed on a rank of gym treadmills. Frame 0 was the scale — which meant the
    poster was the scale, which meant the visitors we deliberately spare the
    video (reduced motion, Save-Data, 2g/3g) were the only ones GUARANTEED to
    see it. It is now trimmed to the two on-message scenes, cross-dissolved so
    the loop has no cut. Every one of the 128 surviving frames was checked.

    If this file is ever replaced, check the new one the same way. A scale, a
    tape measure or a numeric readout in the hero of this particular clinic
    contradicts the whole offer, and it contradicts it in the one image that
    reaches the people we were trying to protect.

    THE PANEL IS TRANSLUCENT, SO ITS CONTRAST IS MEASURED, NOT ASSUMED. The
    text now composites against whatever is behind it. That is survivable only
    because the trimmed clip is unusually even — luminance 120.4 to 129.1 out
    of 255 across every frame — and because backdrop-blur flattens what is left,
    so an isolated dark pixel cannot drag a glyph below threshold. The opacity
    below is the value the measurement supports, not a value that looked nice.
    HeroContrastTest pins it.

    LAYER ORDER. Section background colour, then poster, then video, then
    overlay, then the header scrim, then content. `isolate` keeps the negative
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
    will not decode.

    NOTHING ABOUT THE VIDEO CAN SHIFT THE LAYOUT. Both the poster and the video
    are absolutely positioned and out of flow, so the section is sized by its
    content exactly as it was before the video existed.
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
     * Where the SUBJECT sits in the frame. Everything about the framing is
     * derived from this one measurement rather than eyeballed per breakpoint.
     *
     * The footage changed in 8.13 and this number moved with it. The old value
     * centred a plate that sat left of centre; the new clip is a counter of
     * vegetables running along the bottom third with a dark cabinet on the
     * left, so the interest is low and centre-right.
     */
    $plateFocus = '50% 75%';
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
            href="{{ asset('brand/hero-poster.jpg') }}"
            imagesrcset="{{ asset('brand/hero-poster-1280.webp') }} 1280w"
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
    on paper where it is close to invisible. The top padding below already
    clears the header's own height, so nothing lands underneath it.

    Only when there is media to be transparent over: on a hero without it the
    header stays solid and there is nothing to slide under.
--}}
<section
    @class([
        'relative isolate overflow-hidden bg-ink pt-28 pb-16 sm:pt-32 sm:pb-24 lg:min-h-[42rem] lg:pt-40 lg:pb-32',
        '-mt-18' => $media,
    ])
    data-hero
>
    @if ($media)
        {{-- Painted on first contentful paint and never taken away. --}}
        <picture>
            <source
                type="image/webp"
                srcset="{{ asset('brand/hero-poster-1280.webp') }} 1280w"
                sizes="100vw"
            />
            <img
                src="{{ asset('brand/hero-poster.jpg') }}"
                alt=""
                aria-hidden="true"
                fetchpriority="high"
                decoding="async"
                style="object-position: {{ $plateFocus }}"
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
                style="object-position: {{ $plateFocus }}"
                muted
                loop
                playsinline
                preload="none"
                aria-hidden="true"
                tabindex="-1"
                disablepictureinpicture
                controlslist="nodownload noplaybackrate noremoteplayback"
                data-hero-video
                data-src="{{ asset('brand/hero.mp4') }}"
            ></video>
        @endif

        {{--
            The overlay. 38% ink: enough to settle the footage down behind a
            translucent panel and to keep the white case card from disappearing
            into the plate, and not so much that the picture goes to mud.
        --}}
        <div class="absolute inset-0 -z-10 bg-ink/[0.38]" aria-hidden="true" data-hero-overlay></div>

        {{--
            The header scrim. The header is transparent over this section, and
            white nav links over a near-white plate would fail on their own —
            measured, not guessed. A gradient rather than a bar because the
            point of the transparent header is that it has no edge.
        --}}
        <div
            class="absolute inset-x-0 top-0 -z-10 h-40 bg-linear-to-b from-ink/85 via-ink/45 to-transparent"
            aria-hidden="true"
            data-hero-scrim
        ></div>
    @endif

    <x-container class="relative">
        {{--
            The composition. One relative box; the panel sits in it and the
            card is positioned against the same coordinates, so the overlap is
            a property of the layout rather than of two margins that have to be
            kept in step.
        --}}
        <div class="relative lg:pb-[15rem]">
            {{--
                The panel. ml-auto is PHYSICAL on purpose — see the composition
                note above. The text inside stays logical, so Arabic aligns
                right and English aligns left within the same shape.
            --}}
            <div class="lg:ml-auto lg:w-[52%]">
                <div data-hero-panel data-enter="panel" class="rounded-[1.75rem] bg-paper/[0.93] p-6 shadow-md ring-1 ring-white/50 backdrop-blur-xl sm:rounded-[2rem] sm:p-9 lg:pb-44">
                    <x-section-heading
                        level="h1"
                        :eyebrow="__('home.hero.eyebrow')"
                        :title="__('home.hero.title')"
                        :lead="__('home.hero.lead')"
                    >
                        <div class="mt-7 flex flex-wrap items-center gap-3">
                            <x-button :href="route('booking')" size="lg">
                                {{ __('home.hero.cta') }}
                            </x-button>

                            <x-button variant="ghost" size="lg" href="#packages">
                                {{ __('home.hero.secondary_cta') }}
                            </x-button>
                        </div>

                        {{-- Credential chips: what the clinic is, never what it promises. --}}
                        <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-3">
                            @foreach (__('home.hero.chips') as $chip)
                                <li class="flex items-center gap-2 text-sm text-muted">
                                    <svg class="size-4 text-accent" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="m4 10.5 4 4 8-9" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    {{ $chip }}
                                </li>
                            @endforeach
                        </ul>
                    </x-section-heading>
                </div>
            </div>

            {{--
                The case card, straddling the panel's inner edge and dropped
                below its baseline. Tighter radius and a heavier shadow than the
                panel, and opaque where the panel is not: that difference is
                what gives the pair depth instead of making them a matched set.

                Positioned with PHYSICAL left, to match the panel's physical
                ml-auto. Logical positioning would mirror the card while the
                panel stayed put, giving the two locales different overlaps.

                The panel carries extra bottom padding on large screens so this
                corner is empty before the card lands on it. Overlapping live
                copy would clip a credential chip, which is content, not
                decoration.

                Stacked underneath on small screens, where there is no room for
                an overlap and pretending otherwise would just crush both.
            --}}
            <div class="mt-6 lg:absolute lg:left-[24%] lg:top-[calc(100%-15rem-7rem)] lg:mt-0 lg:w-[21rem]">
                <x-card class="rounded-xl shadow-2xl ring-black/5" :padding="false" data-hero-card data-enter="card">
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-medium tracking-wide text-accent-dark uppercase">
                                    {{ __('home.hero.case_card.label') }}
                                </p>
                                <h2 class="mt-1 font-display text-lg font-semibold text-ink">
                                    {{ __('home.hero.case_card.title') }}
                                </h2>
                                <p class="text-sm text-muted">{{ __('home.hero.case_card.subtitle') }}</p>
                            </div>

                            <x-logo.mark :size="34" class="text-ink/25" />
                        </div>

                        <dl class="mt-5 space-y-3">
                            @foreach (__('home.hero.case_card.metrics') as $metric)
                                <div class="flex items-center justify-between gap-4 border-b border-line pb-2.5 last:border-0">
                                    <dt class="text-sm text-muted">{{ $metric['label'] }}</dt>
                                    <dd class="text-end text-sm font-medium text-ink">{{ $metric['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        <p class="mt-4 text-xs leading-relaxed text-muted">
                            {{ __('home.hero.case_card.note') }}
                        </p>
                    </div>
                </x-card>
            </div>
        </div>
    </x-container>
</section>
