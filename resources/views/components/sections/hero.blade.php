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

    ---------------------------------------------------------------------------
    THE BACKGROUND VIDEO
    ---------------------------------------------------------------------------

    Full-bleed footage behind this section only, with the copy on its own solid
    panel rather than laid directly over the picture.

    That composition is the whole reason the video is watchable. White text over
    this footage needs roughly 65% navy to clear 4.5:1 — the clip averages 124
    of 255 luminance with a near-white plate sitting exactly where a headline
    goes — and at 65% the video is dark enough that it stops being worth the
    bytes. Putting the text on a panel unties the two: the overlay only has to
    make the picture pleasant, so it sits at 38%, and readability is carried by
    an opaque surface whose colour is known at build time.

    THE PANEL IS OPAQUE ON PURPOSE. A translucent one would composite against
    whatever frame happens to be behind it, which makes the contrast ratio a
    property of the video rather than of the stylesheet — untestable, and
    different every second.

    LAYER ORDER. Section background colour, then poster, then video, then
    overlay, then content. `isolate` keeps the negative z-indices inside this
    section instead of sliding behind the page.

    THE POSTER IS AN <img>, NOT A poster="" ATTRIBUTE. It is a real element, so
    the preload scanner finds it, it can be given fetchpriority, and it stays
    painted underneath forever — the video fades in on top of it and never
    replaces it. Every failure path therefore lands on the poster rather than on
    a black box: no JS, reduced motion, Save-Data, a slow connection, a 404, a
    codec the browser will not decode.

    NOTHING ABOUT THE VIDEO CAN SHIFT THE LAYOUT. Both the poster and the video
    are absolutely positioned and out of flow, so the section is sized by its
    content exactly as it was before the video existed.
--}}

@php
    // The adherence figure lives here, not in the translation files, so the
    // bar width and the printed percentage can never disagree.
    $adherence = 86;

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
            imagesrcset="{{ asset('brand/hero-poster-828.webp') }} 828w, {{ asset('brand/hero-poster-1280.webp') }} 1280w"
            imagesizes="100vw"
            type="image/webp"
        >
    @endpush
@endif

<section class="relative isolate overflow-hidden bg-ink py-16 sm:py-24" data-hero>
    @if ($media)
        {{-- Painted on first contentful paint and never taken away. --}}
        <picture>
            <source
                type="image/webp"
                srcset="{{ asset('brand/hero-poster-828.webp') }} 828w, {{ asset('brand/hero-poster-1280.webp') }} 1280w"
                sizes="100vw"
            />
            <img
                src="{{ asset('brand/hero-poster.jpg') }}"
                alt=""
                aria-hidden="true"
                fetchpriority="high"
                decoding="async"
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
                muted
                loop
                playsinline
                preload="none"
                aria-hidden="true"
                tabindex="-1"
                disablepictureinpicture
                controlslist="nodownload noplaybackrate noremoteplayback"
                data-hero-video
                data-src="{{ asset('brand/1.mp4') }}"
            ></video>
        @endif

        {{--
            The overlay. 38% ink: enough to settle the footage down behind a
            near-white panel and to keep the white case card from disappearing
            into the plate, and not so much that the picture goes to mud.

            It is NOT what makes any text readable — see the block comment
            above. Nothing on this page relies on it for contrast.
        --}}
        <div class="absolute inset-0 -z-10 bg-ink/[0.38]" aria-hidden="true" data-hero-overlay></div>
    @endif

    <x-container>
        <div class="grid items-center gap-8 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-7">
                {{--
                    The panel. Opaque paper, the same colour the page background
                    already is, so every text pair inside it is one the contrast
                    test has always covered and the copy needs no rework.

                    Paper rather than navy: under the overlay the footage sits
                    mid-dark and navy-tinted, so a near-white surface separates
                    from it cleanly, where a navy panel would sink into the
                    overlay it is supposed to stand out from.
                --}}
                <div class="rounded-3xl bg-paper p-7 shadow-xl ring-1 ring-white/40 sm:p-10">
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

            <div class="lg:col-span-5">
                <x-card class="reveal" :padding="false">
                    <div class="p-6 sm:p-7">
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

                        <dl class="mt-6 space-y-4">
                            @foreach (__('home.hero.case_card.metrics') as $metric)
                                <div class="flex items-center justify-between gap-4 border-b border-line pb-3 last:border-0">
                                    <dt class="text-sm text-muted">{{ $metric['label'] }}</dt>
                                    <dd class="text-end text-sm font-medium text-ink">{{ $metric['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>

                        <div class="mt-5">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm text-muted">{{ __('home.hero.case_card.adherence') }}</span>
                                <span class="font-display text-sm font-semibold text-accent-dark">{{ $adherence }}%</span>
                            </div>

                            {{--
                                A meter, not a decorative div: role/aria give the
                                value to a screen reader, which otherwise hears a
                                percentage with no indication of what it is out of.
                            --}}
                            <div
                                class="mt-2 h-2 w-full overflow-hidden rounded-pill bg-sage"
                                role="meter"
                                aria-valuenow="{{ $adherence }}"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-label="{{ __('home.hero.case_card.adherence') }}"
                            >
                                <div class="h-full rounded-pill bg-accent" style="width: {{ $adherence }}%"></div>
                            </div>
                        </div>

                        <p class="mt-5 text-xs leading-relaxed text-muted">
                            {{ __('home.hero.case_card.note') }}
                        </p>
                    </div>
                </x-card>
            </div>
        </div>
    </x-container>
</section>
