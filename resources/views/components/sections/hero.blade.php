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
--}}

@php
    // The adherence figure lives here, not in the translation files, so the
    // bar width and the printed percentage can never disagree.
    $adherence = 86;
@endphp

<section class="relative overflow-hidden bg-linear-to-b from-sage to-paper py-16 sm:py-24">
    <x-container>
        <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-16">
            <div class="lg:col-span-7">
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
