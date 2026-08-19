@props(['testimonials'])

{{--
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

        @if ($testimonials->isEmpty())
            <p class="mt-10 text-muted">{{ __('home.stories.empty') }}</p>
        @else
            <ul class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $testimonial)
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
                                    {{ $testimonial->quote }}
                                </p>
                            </blockquote>

                            <figcaption class="mt-6 flex items-center gap-3 border-t border-line pt-5">
                                <span
                                    class="inline-flex size-11 items-center justify-center rounded-pill bg-ink font-display text-sm font-semibold text-white"
                                    aria-hidden="true"
                                >
                                    {{ $testimonial->initials }}
                                </span>

                                <span>
                                    <span class="block text-sm font-medium text-ink">{{ $testimonial->name }}</span>
                                    @if ($testimonial->context)
                                        <span class="block text-sm text-muted">{{ $testimonial->context }}</span>
                                    @endif
                                </span>
                            </figcaption>
                        </x-card>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-container>
</section>
