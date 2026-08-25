@php
    /*
     * EVERY FACT ON THIS PAGE COMES FROM config/clinic.php.
     *
     * Not one qualification, body or number is written into this file, and
     * CredentialsTest fails the build if one is. These are claims about a real
     * person's professional standing, published under her name — the version
     * this replaced invented a university, a degree she does not hold and the
     * wrong syndicate.
     */
    $practitioner = config('clinic.practitioner');
    $training = config('clinic.training');

    // Reserved, not filled. Her own photograph and the redacted certificates
    // are coming; a stock stand-in on this page would be a false claim about
    // who will be treating you.
    $portrait = null;
    $certificates = [];
@endphp

<x-page-shell
    :eyebrow="__('about.eyebrow')"
    :title="__('about.page_title')"
    :lead="__('about.page_lead')"
    :meta-title="__('about.meta_title')"
    :meta-description="__('about.meta_description')"
    :footer-services="$footerServices"
    :trail="[
        ['label' => __('nav.home'), 'url' => route('home')],
        ['label' => __('nav.about'), 'url' => null],
    ]"
>
    <x-slot:cta-title>{{ __('about.cta.title') }}</x-slot:cta-title>
    <x-slot:cta-lead>{{ __('about.cta.lead') }}</x-slot:cta-lead>

    <section class="py-16 sm:py-24" aria-labelledby="practitioner-heading">
        <x-container>
            <div class="grid items-start gap-10 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-5 lg:mt-6">
                    @if ($portrait)
                        <x-photo :slug="$portrait" :alt="__('about.portrait_alt', ['name' => $practitioner['name_ar']])" sizes="(min-width: 1024px) 36vw, 100vw" />
                    @else
                        <x-photo-frame :label="__('about.portrait_pending')" class="mx-auto max-w-sm shadow-sm" />
                        <p class="mt-4 text-center text-sm text-muted lg:text-start">{{ __('about.portrait_pending_title') }}</p>
                    @endif
                </div>

                <div class="lg:col-span-7">
                    <h2 id="practitioner-heading" class="font-display text-3xl font-semibold text-ink sm:text-4xl">
                        {{ $practitioner['name_ar'] }}
                    </h2>

                    <p class="mt-2 text-lg text-accent-dark">
                        {{ app()->getLocale() === 'ar' ? $practitioner['title_ar'] : $practitioner['title_en'] }}
                    </p>

                    {{--
                        THE LICENCE, DISPLAYED PROMINENTLY AND NOT SUMMARISED.

                        A membership number a patient can check against the
                        syndicate register is a stronger trust signal than any
                        adjective a marketing page could reach for. It is the
                        most verifiable fact on this site, so it is given the
                        most visual weight.
                    --}}
                    <div class="mt-8 rounded-2xl bg-ink p-6 text-white sm:p-8">
                        <p class="text-xs font-semibold tracking-wide text-gold uppercase">{{ __('about.licence_label') }}</p>

                        <p class="mt-3 font-display text-xl font-semibold sm:text-2xl">{{ $practitioner['licence_body_ar'] }}</p>

                        <p class="mt-4 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                            {{-- dir=ltr: a membership number reorders inside an
                                 Arabic line without it, and a number a patient
                                 retypes wrongly verifies nothing. --}}
                            <bdi dir="ltr" class="font-display text-4xl font-semibold tabular-nums text-gold sm:text-5xl">{{ $practitioner['licence_number'] }}</bdi>
                            <span class="text-sm text-white/75"><bdi dir="ltr">{{ $practitioner['licence_year'] }}</bdi></span>
                        </p>

                        <p class="mt-5 border-t border-white/15 pt-5 text-sm leading-relaxed text-white/75">
                            {{ __('about.licence_note') }}
                        </p>
                    </div>

                    <dl class="mt-8 divide-y divide-line border-y border-line">
                        <div class="py-4">
                            <dt class="text-xs font-semibold tracking-wide text-accent-dark uppercase">{{ __('about.degree_label') }}</dt>
                            <dd class="mt-1.5 leading-relaxed text-ink">{{ $practitioner['degree_ar'] }}</dd>
                        </div>
                    </dl>

                    <h3 class="mt-10 text-sm font-semibold tracking-wide text-accent-dark uppercase">
                        {{ __('about.philosophy_heading') }}
                    </h3>

                    <p class="mt-4 leading-relaxed text-pretty text-muted">{{ __('about.philosophy') }}</p>
                </div>
            </div>
        </x-container>
    </section>

    {{--
        The clinical training, as a timeline.

        TWO NAMED UNIVERSITY HOSPITALS IS THE STRONGEST FACT SHE HAS, and a
        paragraph would bury it. An institution a patient can look up carries
        weight that "extensive training" never will, so each one gets its own
        row with its hours and its year exactly as the certificate states them.
    --}}
    <section class="bg-sage/50 py-16 sm:py-24" aria-labelledby="training-heading">
        <x-container>
            <div class="max-w-2xl">
                <x-section-heading
                    id="training-heading"
                    :title="__('about.training_heading')"
                    :lead="__('about.training_lead')"
                />
            </div>

            {{-- Held to a readable measure. At full width the hours pill ends up a
                 hand-span from the institution it belongs to, and the two stop
                 reading as one row. --}}
            <ol class="mt-12 max-w-3xl space-y-0">
                @foreach ($training as $index => $entry)
                    <li class="reveal relative grid gap-2 border-s-2 border-line ps-6 pb-10 last:pb-0 sm:grid-cols-12 sm:gap-6">
                        {{-- The node on the line. Decorative: the list is
                             ordered, so a screen reader already counts it. --}}
                        <span aria-hidden="true" class="absolute -start-[7px] top-1.5 size-3 rounded-full bg-accent ring-4 ring-sage/50"></span>

                        <div class="sm:col-span-8">
                            <h3 class="font-display text-lg font-semibold text-ink">{{ $entry['institution_ar'] }}</h3>
                            <p class="mt-1 text-sm text-muted">{{ $entry['programme_ar'] }}</p>
                        </div>

                        {{--
                            Fixed slots, not a wrapping flex row.

                            Hours and year are both optional — one entry has no
                            year, another has no hours — and letting them close
                            up shifted the pill sideways on every row, so five
                            certificates read as a ragged list rather than as a
                            record. Each keeps its column whether it is filled
                            or not.
                        --}}
                        <div class="flex items-start justify-start gap-x-3 text-sm text-muted sm:col-span-4 sm:justify-end">
                            <span class="w-[5.5rem] shrink-0">
                                @if ($entry['hours'])
                                    <span class="inline-block rounded-pill bg-white px-3 py-1 font-medium text-ink ring-1 ring-line">
                                        {{ __('about.training_hours', ['hours' => $entry['hours']]) }}
                                    </span>
                                @endif
                            </span>

                            <span class="w-10 shrink-0 pt-1">
                                @if ($entry['year'])
                                    <bdi dir="ltr">{{ $entry['year'] }}</bdi>
                                @endif
                            </span>
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-container>
    </section>

    {{--
        Certificate images, reserved.

        THEY CARRY HER NATIONAL ID AND ARE NOT PUBLISHED UNTIL IT IS REDACTED.
        A national ID number is personal data a patient does not need in order
        to verify a qualification, and once it is on the internet it does not
        come back. The frames hold the shape the redacted scans will take.
    --}}
    <section class="py-16 sm:py-24" aria-labelledby="certificates-heading">
        <x-container>
            <h2 id="certificates-heading" class="font-display text-2xl font-semibold text-ink sm:text-3xl">
                {{ __('about.certificates_heading') }}
            </h2>

            <p class="mt-3 max-w-2xl leading-relaxed text-muted">{{ __('about.certificates_note') }}</p>

            <div class="mt-8 grid gap-6 sm:grid-cols-3">
                @if ($certificates !== [])
                    @foreach ($certificates as $certificate)
                        <x-photo :slug="$certificate" :alt="__('about.certificates_heading')" />
                    @endforeach
                @else
                    {{-- Three at different offsets: the shape the scans will
                         take, held now so nothing moves when they arrive. --}}
                    <x-photo-frame ratio="aspect-4/5" :label="__('about.certificates_pending')" :size="110" />
                    <x-photo-frame ratio="aspect-4/5" :label="__('about.certificates_pending')" :size="110" class="sm:mt-8" />
                    <x-photo-frame ratio="aspect-4/5" :label="__('about.certificates_pending')" :size="110" class="sm:mt-16" />
                @endif
            </div>

            <p class="mt-4 text-sm text-muted">{{ __('about.certificates_pending') }}</p>
        </x-container>
    </section>

    <section class="bg-sage/50 py-16 sm:py-20" aria-labelledby="treats-heading">
        <x-container>
            <x-section-heading
                id="treats-heading"
                :title="__('about.treats_heading')"
                :lead="__('about.treats_lead')"
            />

            <ul class="mt-8 flex flex-wrap gap-3">
                @foreach ($footerServices as $service)
                    <li>
                        <a href="{{ route('services') }}" class="inline-flex rounded-pill bg-white px-4 py-2 text-sm text-ink ring-1 ring-line transition hover:ring-accent">{{ $service->name }}</a>
                    </li>
                @endforeach
            </ul>
        </x-container>
    </section>
</x-page-shell>
