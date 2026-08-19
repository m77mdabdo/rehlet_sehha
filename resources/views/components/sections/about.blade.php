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
                            alt="{{ __('about.portrait_alt', ['name' => __('about.name')]) }}"
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
                    :title="__('about.name')"
                    :lead="__('about.title')"
                />

                <p class="mt-6 leading-relaxed text-pretty text-muted">
                    {{ __('about.philosophy') }}
                </p>

                <h3 class="mt-8 font-display text-base font-semibold text-ink">
                    {{ __('about.credentials_heading') }}
                </h3>

                <ul class="mt-4 space-y-3">
                    @foreach (__('about.credentials') as $credential)
                        <li class="flex items-start gap-3 text-sm leading-relaxed text-muted">
                            <svg class="mt-1 size-4 shrink-0 text-accent-dark" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="m4 10.5 4 4 8-9" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>{{ $credential }}</span>
                        </li>
                    @endforeach
                </ul>

                {{-- The clinical registration line. Small, factual, last. --}}
                <p class="mt-8 border-t border-line pt-6 text-sm text-muted">
                    {{ __('about.registration') }}
                </p>
            </div>
        </div>
    </x-container>
</section>
