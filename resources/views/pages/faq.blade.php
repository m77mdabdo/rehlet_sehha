<x-page-shell
    :eyebrow="__('faq.eyebrow')"
    :title="__('faq.title')"
    :lead="__('faq.lead')"
    :meta-title="__('faq.meta_title')"
    :meta-description="__('faq.meta_description')"
    :footer-services="$footerServices"
    :trail="[
        ['label' => __('nav.home'), 'url' => route('home')],
        ['label' => __('nav.faq'), 'url' => null],
    ]"
>
    <x-slot:cta-title>{{ __('faq.cta.title') }}</x-slot:cta-title>
    <x-slot:cta-lead>{{ __('faq.cta.lead') }}</x-slot:cta-lead>

    {{--
        No photograph on this page, and that is the rule rather than an
        oversight. An image has to illustrate the text beside it; there is
        nothing in the library that illustrates a question about a refund, and
        a food photograph dropped in for visual relief would be decoration
        pretending to be information.

        The rhythm comes from the grouping and the whitespace instead.
    --}}
    <section class="py-16 sm:py-24" aria-labelledby="questions-heading">
        <x-container size="narrow">
            <h2 id="questions-heading" class="sr-only">{{ __('faq.title') }}</h2>

            @if ($groups->isEmpty())
                <p class="text-muted">{{ __('faq.empty') }}</p>
            @else
                <div class="space-y-16">
                    @foreach ($groups as $category => $faqs)
                        <section aria-labelledby="faq-{{ $category }}">
                            <h3 id="faq-{{ $category }}" class="font-display text-2xl font-semibold text-ink">
                                {{ __("faq.categories.{$category}.title") }}
                            </h3>

                            <p class="mt-2 leading-relaxed text-muted">{{ __("faq.categories.{$category}.lead") }}</p>

                            {{-- The same native accordion the homepage uses.
                                 One component, not a second variant that
                                 drifts away from it. --}}
                            <ul class="mt-8 space-y-3">
                                @foreach ($faqs as $faq)
                                    <li>
                                        <details class="faq-item reveal rounded-lg bg-white ring-1 ring-line">
                                            <summary class="flex cursor-pointer items-center justify-between gap-4 p-5 font-medium text-ink">
                                                {{ $faq->question }}
                                                <svg class="size-5 shrink-0 text-accent transition-transform" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path d="M5 8l5 5 5-5" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </summary>

                                            <div class="px-5 pb-5 text-sm leading-relaxed text-muted">{{ $faq->answer }}</div>
                                        </details>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                </div>

                <div class="mt-16 rounded-2xl bg-sage/50 p-6 sm:p-8">
                    <h3 class="font-display text-lg font-semibold text-ink">{{ __('faq.still_asking.title') }}</h3>
                    <p class="mt-2 leading-relaxed text-muted">{{ __('faq.still_asking.body') }}</p>

                    <div class="mt-5">
                        <x-contact-details class="text-ink" :show-address="false" />
                    </div>
                </div>
            @endif
        </x-container>
    </section>
</x-page-shell>
