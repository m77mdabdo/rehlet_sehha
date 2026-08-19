@props(['faqs'])

{{--
    Native <details>/<summary>, no JavaScript.

    This is not a shortcut, it is the better implementation. It gives correct
    keyboard handling, a real expanded state for assistive technology, and it
    lets in-page find (ctrl-F) open a closed answer — which a div-based
    accordion silently breaks, hiding content from the reader who was searching
    hardest for it. It also works with JavaScript off or still loading.

    The disclosure triangle is removed in app.css so the chevron below can be
    ours; that CSS is the entire cost of the approach.

    Deliberately NOT using the `name` attribute to make it an exclusive
    accordion: closing someone's answer because they opened a second one is a
    behaviour people find hostile when comparing two answers.
--}}

<section id="faq" class="bg-sage/50 py-20 sm:py-24" aria-labelledby="faq-heading">
    <x-container size="narrow">
        <x-section-heading
            id="faq-heading"
            :eyebrow="__('home.faq.eyebrow')"
            :title="__('home.faq.title')"
            :lead="__('home.faq.lead')"
        />

        @if ($faqs->isEmpty())
            <p class="mt-10 text-muted">{{ __('home.faq.empty') }}</p>
        @else
            <div class="mt-10 space-y-3">
                @foreach ($faqs as $faq)
                    <details class="faq-item reveal rounded-lg bg-white ring-1 ring-line">
                        <summary class="flex items-center justify-between gap-4 p-5 text-start">
                            <h3 class="font-display text-base font-semibold text-ink">
                                {{ $faq->question }}
                            </h3>

                            <svg
                                class="faq-chevron size-5 shrink-0 text-accent transition-transform duration-200"
                                viewBox="0 0 20 20"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                aria-hidden="true"
                            >
                                <path d="m5 8 5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </summary>

                        <div class="border-t border-line px-5 pt-4 pb-5">
                            <p class="text-sm leading-relaxed text-muted">{{ $faq->answer }}</p>
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </x-container>
</section>
