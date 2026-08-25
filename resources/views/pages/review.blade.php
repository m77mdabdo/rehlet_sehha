<x-layouts.app
    :title="__('review.meta_title')"
    :footer-services="$footerServices"
    :indexable="false"
>
    {{--
        The review form.

        indexable=false: the URL carries a bearer token, so canonical, hreflang
        and og:url are all suppressed and a robots noindex is emitted. Those
        three tags echo the current URL, and publishing this one to a search
        engine would hand anybody a way to write as this patient.
    --}}
    <section class="py-16 sm:py-24">
        <x-container size="narrow">
            @if (session('review-submitted'))
                <x-card class="text-center">
                    <h1 class="font-display text-2xl font-semibold text-ink sm:text-3xl">{{ __('review.thanks_title') }}</h1>
                    <p class="mt-4 leading-relaxed text-muted">
                        {{ $review->consented_at ? __('review.thanks_body') : __('review.thanks_private') }}
                    </p>
                </x-card>
            @elseif ($alreadySubmitted)
                <x-card>
                    <h1 class="font-display text-2xl font-semibold text-ink">{{ __('review.already_title') }}</h1>
                    <p class="mt-3 leading-relaxed text-muted">{{ __('review.already_body') }}</p>

                    <dl class="mt-6 space-y-3 border-t border-line pt-5 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted">{{ __('review.rating_label') }}</dt>
                            <dd class="font-medium text-ink"><bdi dir="ltr">{{ $review->rating }}/5</bdi></dd>
                        </div>

                        @if ($review->comment)
                            <div>
                                <dt class="text-muted">{{ __('review.comment_label') }}</dt>
                                <dd class="mt-1 leading-relaxed text-ink">{{ $review->comment }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-card>
            @else
                <div class="mb-10">
                    <p class="text-sm font-medium tracking-wide text-accent-dark uppercase">{{ __('review.eyebrow') }}</p>
                    <h1 class="mt-3 font-display text-3xl font-semibold text-balance text-ink sm:text-4xl">{{ __('review.title') }}</h1>
                    <p class="mt-4 leading-relaxed text-pretty text-muted">{{ __('review.lead') }}</p>
                </div>

                <form method="POST" action="{{ route('review.store', ['token' => $review->token]) }}">
                    @csrf

                    <x-card>
                        {{-- Rating. A radio group, not a star widget: radios
                             are keyboard-operable and announced correctly
                             without a line of JavaScript. --}}
                        <fieldset>
                            <legend class="font-medium text-ink">{{ __('review.rating_label') }}</legend>
                            <p class="mt-1 text-sm text-muted">{{ __('review.rating_hint') }}</p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                @for ($value = 1; $value <= 5; $value++)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="rating" value="{{ $value }}" class="peer sr-only" @checked(old('rating') == $value) required>
                                        <span class="inline-flex size-12 items-center justify-center rounded-lg text-lg font-semibold text-ink ring-1 ring-line transition peer-checked:bg-accent peer-checked:text-white peer-checked:ring-accent peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-accent-dark">
                                            <bdi dir="ltr">{{ $value }}</bdi>
                                        </span>
                                    </label>
                                @endfor
                            </div>

                            @error('rating')
                                <p class="mt-2 text-sm text-accent-dark">{{ __('review.rating_required') }}</p>
                            @enderror
                        </fieldset>

                        {{--
                            The clinical warning, ABOVE the box rather than
                            under it. A public review naming a diagnosis is her
                            medical information, published by us, somewhere she
                            cannot easily take it back from — so she reads this
                            before she writes, not after.
                        --}}
                        <div class="mt-8 rounded-lg bg-sage/60 p-4">
                            <p class="text-sm font-semibold text-ink">{{ __('review.clinical_warning_title') }}</p>
                            <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ __('review.clinical_warning') }}</p>
                        </div>

                        <div class="mt-6">
                            <label for="comment" class="font-medium text-ink">{{ __('review.comment_label') }}</label>
                            <p class="mt-1 text-sm text-muted">{{ __('review.comment_hint') }}</p>

                            <textarea
                                id="comment"
                                name="comment"
                                rows="5"
                                maxlength="1200"
                                class="mt-3 w-full rounded-lg border-line bg-white p-3 text-ink ring-1 ring-line focus:outline-2 focus:outline-offset-2 focus:outline-accent-dark"
                            >{{ old('comment') }}</textarea>
                        </div>

                        <div class="mt-6">
                            <label for="display_name" class="font-medium text-ink">{{ __('review.name_label') }}</label>
                            <p class="mt-1 text-sm text-muted">{{ __('review.name_hint') }}</p>

                            <input
                                id="display_name"
                                name="display_name"
                                type="text"
                                maxlength="60"
                                value="{{ old('display_name', $review->display_name) }}"
                                class="mt-3 w-full rounded-lg bg-white p-3 text-ink ring-1 ring-line focus:outline-2 focus:outline-offset-2 focus:outline-accent-dark"
                            >
                        </div>

                        {{--
                            CONSENT. Unticked, always, and never pre-checked.
                            Telling the clinic something and telling the
                            internet something are different decisions, and the
                            model refuses to publish anything without this.
                        --}}
                        <div class="mt-8 rounded-lg border border-line p-4">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input
                                    type="checkbox"
                                    name="consent"
                                    value="1"
                                    class="mt-1 size-5 shrink-0 rounded border-line text-accent focus:outline-2 focus:outline-offset-2 focus:outline-accent-dark"
                                >
                                <span>
                                    <span class="font-medium text-ink">{{ __('review.consent_label') }}</span>
                                    <span class="mt-1 block text-sm leading-relaxed text-muted">{{ __('review.consent_hint') }}</span>
                                </span>
                            </label>
                        </div>

                        <div class="mt-8">
                            <x-button type="submit" size="lg" class="w-full">{{ __('review.submit') }}</x-button>
                        </div>
                    </x-card>
                </form>
            @endif
        </x-container>
    </section>
</x-layouts.app>
