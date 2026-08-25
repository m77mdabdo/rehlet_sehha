{{--
    The practitioner.

    THE COPY IN THIS SECTION IS A PLACEHOLDER. Every string that needs a real
    human answer is marked TODO_COPY in lang/{ar,en}/about.php, and
    PlaceholderCopyTest fails the build if any of them is still there when
    APP_ENV is production. The structure is real; the words are not.

    Written this way rather than left out entirely because the shape of the
    section is a decision — what a clinic should say about its practitioner,
    and in what order — and that decision is worth reviewing now. Inventing
    credentials, a registration number or a biography for a real doctor is a
    different matter: those are claims about a person's qualifications, and
    getting them wrong is not a copy problem.

    The registration line is last and quiet on purpose. It is the detail that
    matters most to a regulator and least to a nervous patient, and burying it
    would be as wrong as leading with it.
--}}

<section id="about" class="py-20 sm:py-24" aria-labelledby="about-heading">
    <x-container>
        <div class="grid gap-12 lg:grid-cols-12 lg:items-center lg:gap-16">
            <div class="lg:col-span-5">
                {{--
                    Portrait slot. No photograph has been supplied, so this
                    renders the mark on sage — deliberately not a broken image
                    and deliberately not a stock photo of a stranger in a lab
                    coat, which on a clinic site reads as a claim about who
                    will be treating you.
                --}}
                <div class="reveal relative mx-auto flex aspect-4/5 w-full max-w-sm items-center justify-center overflow-hidden rounded-lg bg-sage ring-1 ring-line">
                    @if ($portrait ?? false)
                        <img
                            src="{{ $portrait }}"
                            alt="{{ __('about.portrait_alt', ['name' => config('clinic.practitioner.name_ar')]) }}"
                            class="size-full object-cover"
                            width="480"
                            height="600"
                            loading="lazy"
                        >
                    @else
                        <x-logo.mark-full :size="120" class="text-ink/20" />
                        <span class="sr-only">{{ __('about.portrait_pending') }}</span>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-7">
                <x-section-heading
                    id="about-heading"
                    :eyebrow="__('about.eyebrow')"
                    :title="config('clinic.practitioner.name_ar')"
                    :lead="app()->getLocale() === 'ar' ? config('clinic.practitioner.title_ar') : config('clinic.practitioner.title_en')"
                />

                <p class="mt-6 leading-relaxed text-pretty text-muted">
                    {{ __('about.philosophy') }}
                </p>

                {{--
                    THE CREDENTIALS COME FROM config/clinic.php, not from copy.

                    These are claims about a real person's professional
                    standing published under her name, and one source means the
                    homepage and the about page cannot state them differently.
                    CredentialsAndReviewsTest fails if a page names a
                    university or a syndicate config does not hold — the
                    version this replaced named the wrong one of each.

                    The summary here is the degree and the licence; the full
                    training record is on the about page.
                --}}
                <h3 class="mt-8 font-display text-base font-semibold text-ink">
                    {{ __('about.credentials_heading') }}
                </h3>

                <ul class="mt-4 space-y-3">
                    @foreach ([config('clinic.practitioner.degree_ar'), __('about.licence_value', [
                        'body' => config('clinic.practitioner.licence_body_ar'),
                        'number' => config('clinic.practitioner.licence_number'),
                        'year' => config('clinic.practitioner.licence_year'),
                    ])] as $credential)
                        <li class="flex items-start gap-3 text-sm leading-relaxed text-muted">
                            <svg class="mt-1 size-4 shrink-0 text-accent-dark" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="m4 10.5 4 4 8-9" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>{{ $credential }}</span>
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('about') }}" class="mt-8 inline-flex items-center gap-1.5 border-t border-line pt-6 text-sm font-medium text-accent-dark underline-offset-4 hover:underline">
                    {{ __('about.training_heading') }}
                    <svg class="size-4 rtl:-scale-x-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M7 4l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            </div>
        </div>
    </x-container>
</section>
