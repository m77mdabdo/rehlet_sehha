@props(['reviews', 'aggregate' => null])

{{--
    REAL REVIEWS FROM REAL PATIENTS. Nothing here is written by the clinic.

    Every quote arrived through an invitation sent three days after a completed
    appointment, was written by the patient, and carries her explicit consent
    to be published — an unticked box she had to tick. The model refuses to
    approve anything without it, so no amount of admin-side enthusiasm can put
    an unconsented quote on this page.

    THE SECTION DOES NOT RENDER BELOW THREE APPROVED REVIEWS. A testimonials
    block with one quote in it advertises that almost nobody has said anything,
    which is worse than not having the section — so the caller checks before
    rendering and the homepage simply skips it.

    THE AGGREGATE RATING APPEARS ONLY ABOVE TEN. Three fives average to "5.0
    out of 5", which reads as a fact about the practice and is really a fact
    about the sample size. It is computed, never stored — the 4.9 this replaced
    was a number typed into a config file by nobody in particular.

    Quotes only.

    No before/after photographs and no weight figures — the same rule as the
    hero case card, and for the same reason. A before/after image is a claim
    about a result, made in the most literal way available, and it invites the
    reader to measure herself against a stranger's body before she has spoken
    to anyone. Initials rather than photographs also means a patient can
    consent to being quoted without consenting to being recognised in a waiting
    room.
--}}

<section id="stories" class="bg-sage/50 py-20 sm:py-24" aria-labelledby="stories-heading">
    <x-container>
        <x-section-heading
            id="stories-heading"
            :eyebrow="__('home.stories.eyebrow')"
            :title="__('home.stories.title')"
            :lead="__('home.stories.lead')"
        />

        @if ($aggregate !== null)
            {{-- Computed from the approved set at render time. Only shown
                 because there are enough reviews for an average to mean
                 something. --}}
            <p class="mt-6 flex items-center gap-2 text-sm text-muted">
                <bdi dir="ltr" class="font-display text-2xl font-semibold text-ink">{{ number_format($aggregate, 1) }}</bdi>
                <span>{{ __('home.stories.aggregate', ['count' => $reviews->count()]) }}</span>
            </p>
        @endif

        <ul class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($reviews as $testimonial)
                    <li class="reveal flex">
                        <x-card as="figure" class="flex w-full flex-col">
                            @if ($testimonial->rating)
                                {{--
                                    The gold measures 2.06:1 against the card —
                                    well under the 3:1 WCAG asks of a graphic
                                    that carries information. Rather than
                                    repaint the brand colour, the rating is
                                    written out beside the stars: the stars
                                    become decoration, and the fact they encode
                                    is available to a low-vision reader as text
                                    at 5.96:1. See ContrastTest for the record.
                                --}}
                                <p class="flex items-center gap-2">
                                    <span class="flex items-center gap-1" aria-hidden="true">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <svg
                                            @class([
                                                'size-4',
                                                'text-gold' => $star <= $testimonial->rating,
                                                'text-line' => $star > $testimonial->rating,
                                            ])
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            aria-hidden="true"
                                        >
                                            <path d="M10 1.6l2.4 5 5.5.8-4 3.9.9 5.5-4.8-2.6-4.9 2.6.9-5.5-4-3.9 5.5-.8z" />
                                        </svg>
                                    @endfor
                                    </span>

                                    <span class="text-sm text-muted">
                                        {{ __('home.stories.rating_label', ['count' => $testimonial->rating]) }}
                                    </span>
                                </p>
                            @endif

                            <blockquote class="mt-4 flex-1">
                                <p class="text-base leading-relaxed text-pretty text-ink">
                                    {{ $testimonial->comment }}
                                </p>
                            </blockquote>

                            <figcaption class="mt-6 flex items-center gap-3 border-t border-line pt-5">
                                <span
                                    class="inline-flex size-11 items-center justify-center rounded-pill bg-ink font-display text-sm font-semibold text-white"
                                    aria-hidden="true"
                                >
                                    {{ mb_substr((string) $testimonial->display_name, 0, 1) }}
                                </span>

                                <span>
                                    <span class="block text-sm font-medium text-ink">{{ $testimonial->display_name }}</span>
                                    @if ($testimonial->approved_at)
                                        {{-- The date it was published, not the
                                             visit: a review dated to an
                                             appointment would narrow down who
                                             wrote it. --}}
                                        <span class="block text-sm text-muted"><bdi dir="auto">{{ $testimonial->approved_at->translatedFormat('F Y') }}</bdi></span>
                                    @endif
                                </span>
                            </figcaption>
                        </x-card>
                    </li>
                @endforeach
            </ul>
    </x-container>
</section>
