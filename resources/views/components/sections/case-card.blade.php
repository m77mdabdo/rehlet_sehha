{{--
    The illustrative case card.

    IT USED TO LIVE IN THE HERO, straddling the copy panel's inner edge. Both
    the panel and that overlap are gone as of 8.16: the copy now sits directly
    on the video, and a card cannot. A heading, a definition list and a
    disclaimer either need an opaque background — which is the panel we just
    removed, wearing a hat — or they have to win a legibility fight with moving
    footage, which they lose. Content goes on a surface; only display copy goes
    on a picture.

    Moving it here also ends its overlap with the stats strip STRUCTURALLY. It
    used to be absolutely positioned against the hero and dropped below the
    hero's baseline, which meant every breakpoint needed its own offset and the
    strip below had to keep out of the way. Two sections in normal flow cannot
    overlap at any width, so there is nothing left to tune.

    ---------------------------------------------------------------------------

    IT SHOWS QUALITATIVE PROGRESS ONLY — energy, sleep consistency, labs in
    range, plan adherence.

    No weight, no BMI, no calorie counts, no body-fat percentage, here or
    anywhere else on this site. That is a deliberate clinical and brand
    decision, not an oversight, and it is not a detail to "improve" later:

      - A number on a homepage becomes the thing a patient measures herself
        against before she has ever spoken to a clinician, and the number that
        makes her stop coming when it stalls for a fortnight — which is exactly
        when a plan is usually working.
      - Weight moves for reasons that have nothing to do with adherence: water,
        cycle, illness, muscle. Publishing it as the progress metric teaches the
        wrong causal story.
      - The metrics shown here are the ones the clinic actually adjusts a plan
        on, so the card doubles as an honest statement of method.

    If a future task asks for weight anywhere, that is a conversation with the
    clinician, not a ticket.

    THERE IS NO ADHERENCE PERCENTAGE HERE ANY MORE, AND THERE MUST NOT BE ONE.

    This card used to end on "86%" over a progress bar. It went for the same
    reason the plate builder has no calorie count: a number attached to a
    patient's own behaviour is something she can fail at, and a score with a bar
    under it invites her to grade herself before she has spoken to anybody. It
    also read as an app dashboard rather than as a clinic.

    Adherence is now an ordinary row saying an ordinary thing, in the register
    the rows around it already used — "better than at the start", "within normal
    range". PlateFeedbackHasNoNumbersTest fails the build if a percentage, a
    meter or a progress bar comes back.

    The card is also explicitly labelled as an illustration. Presenting a
    fabricated patient record as a real one would be a different problem again.
--}}
<section class="bg-paper py-14 sm:py-16" aria-labelledby="case-card-heading">
    <x-container>
        {{-- Narrow and centred. The card is one object and a full-width row of
             one object reads as a mistake; this is the same measure the card
             had in the hero, given room rather than tucked into a corner. --}}
        <div class="mx-auto max-w-md">
            <x-card class="rounded-xl shadow-lg ring-black/5" :padding="false" data-hero-card data-enter="card">
                <div class="reveal p-6 sm:p-7">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-medium tracking-wide text-accent-dark uppercase">
                                {{ __('home.hero.case_card.label') }}
                            </p>
                            <h2 id="case-card-heading" class="mt-1 font-display text-lg font-semibold text-ink">
                                {{ __('home.hero.case_card.title') }}
                            </h2>
                            <p class="text-sm text-muted">{{ __('home.hero.case_card.subtitle') }}</p>
                        </div>

                        <x-logo.mark-full :size="34" class="text-ink/25" />
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
    </x-container>
</section>
