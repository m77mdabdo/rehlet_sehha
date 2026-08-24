@php
    $areas = __('services.areas');

    /*
     * Which clinical areas have a photograph, and which do not.
     *
     * FOUR OF THE EIGHT GET NO IMAGE, AND THAT IS THE RULE WORKING RATHER THAN
     * A GAP TO FILL. An image has to illustrate the text beside it. There is
     * nothing in the library that shows PCOS, sports nutrition, lab results or
     * a corporate programme — and a food photograph stretched over PCOS would
     * be decoration pretending to be information, which is worse than the
     * white space it replaces. The packages page proved a page works without.
     *
     * See docs/media/photography.md. When real images exist for those areas
     * they are added here and nowhere else.
     */
    $photos = [
        'medical-nutrition' => ['slug' => 'blood-pressure-reading', 'alt' => 'services.photo_alt.medical-nutrition'],
        'weight-management' => ['slug' => 'food-market-counter', 'alt' => 'services.photo_alt.weight-management'],
        'pregnancy-nutrition' => ['slug' => 'pregnancy-baby-shoes', 'alt' => 'services.photo_alt.pregnancy-nutrition'],
        'child-nutrition' => ['slug' => 'infant-formula', 'alt' => 'services.photo_alt.child-nutrition'],
    ];
@endphp

<x-page-shell
    :eyebrow="__('services.eyebrow')"
    :title="__('services.title')"
    :lead="__('services.lead')"
    :meta-title="__('services.meta_title')"
    :meta-description="__('services.meta_description')"
    :footer-services="$footerServices"
    :trail="[
        ['label' => __('nav.home'), 'url' => route('home')],
        ['label' => __('nav.services'), 'url' => null],
    ]"
>
    <x-slot:cta-title>{{ __('services.cta.title') }}</x-slot:cta-title>
    <x-slot:cta-lead>{{ __('services.cta.lead') }}</x-slot:cta-lead>

    <div class="py-20 sm:py-28">
        <x-container>
            @if ($specialties->isEmpty())
                <p class="text-muted">{{ __('home.specialties.empty') }}</p>
            @else
                <div class="space-y-20 sm:space-y-28">
                    @foreach ($specialties as $index => $specialty)
                        @php
                            $area = $areas[$specialty->slug] ?? null;
                            $photo = $photos[$specialty->slug] ?? null;
                            // Alternating side, which is what keeps eight
                            // sections from reading as eight identical rows.
                            $flipped = $index % 2 !== 0;
                        @endphp

                        @if ($area)
                            <section
                                id="{{ $specialty->slug }}"
                                class="reveal grid items-start gap-8 lg:grid-cols-12 lg:gap-14"
                                aria-labelledby="area-{{ $specialty->slug }}"
                            >
                                <div @class([
                                    'lg:col-span-7',
                                    'lg:order-2' => $flipped && $photo,
                                ])>
                                    <span class="inline-flex size-12 items-center justify-center rounded-lg bg-sage text-accent">
                                        <x-icon :name="$specialty->icon" :size="24" />
                                    </span>

                                    <h2 id="area-{{ $specialty->slug }}" class="mt-5 font-display text-2xl font-semibold text-ink sm:text-3xl">
                                        {{ $specialty->name }}
                                    </h2>

                                    <p class="mt-4 leading-relaxed text-pretty text-muted">{{ $area['body'] }}</p>

                                    <h3 class="mt-8 text-sm font-semibold tracking-wide text-accent-dark uppercase">
                                        {{ __('services.covers_heading') }}
                                    </h3>

                                    <ul class="mt-4 space-y-2.5">
                                        @foreach ($area['covers'] as $item)
                                            <li class="flex items-start gap-2.5 text-sm leading-relaxed text-muted">
                                                <svg class="mt-1 size-4 shrink-0 text-accent" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path d="m4 10.5 4 4 8-9" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <span>{{ $item }}</span>
                                            </li>
                                        @endforeach
                                    </ul>

                                    <div class="mt-7 rounded-xl bg-sage/50 p-5">
                                        <h3 class="text-sm font-semibold text-ink">{{ __('services.suits_heading') }}</h3>
                                        <p class="mt-2 text-sm leading-relaxed text-muted">{{ $area['suits'] }}</p>
                                    </div>

                                    <a
                                        href="{{ route('specialties.show', ['slug' => $specialty->slug]) }}"
                                        class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-accent-dark underline-offset-4 hover:underline"
                                    >
                                        {{ __('services.more', ['name' => $specialty->name]) }}
                                        <svg class="size-4 rtl:-scale-x-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M7 4l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </a>
                                </div>

                                @if ($photo)
                                    {{-- Offset downward so the pair reads as a
                                         composition rather than two columns
                                         starting on the same line. --}}
                                    <div @class([
                                        'lg:col-span-5 lg:mt-14',
                                        'lg:order-1' => $flipped,
                                    ])>
                                        <x-photo
                                            :slug="$photo['slug']"
                                            :alt="__($photo['alt'])"
                                            sizes="(min-width: 1024px) 38vw, 100vw"
                                            class="shadow-sm ring-1 ring-line"
                                        />
                                    </div>
                                @endif
                            </section>
                        @endif
                    @endforeach
                </div>
            @endif
        </x-container>
    </div>

    <x-statement>{{ __('services.statement') }}</x-statement>
</x-page-shell>
