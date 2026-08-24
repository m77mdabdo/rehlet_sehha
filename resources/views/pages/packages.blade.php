@php
    use App\Support\Locales;

    $matrix = __('packages.matrix');
    $rows = __('packages.comparison.rows');

    /*
     * Rows that come from the SERVICES TABLE, not from copy. Price, session
     * count and duration are facts about the offer and are rendered from the
     * model, so this table and the homepage cards can never end up quoting
     * different numbers at the same patient.
     *
     * Everything else is wording the schema does not model, and comes from the
     * slug-keyed matrix in the packages translation file.
     *
     * (Written without a glob. A star followed by a slash ends a block comment,
     * and the rest of the sentence becomes a syntax error — which is exactly
     * how this file first failed to compile.)
     */
    $copyRows = ['format', 'plan', 'between', 'labs', 'adjust', 'suits'];
@endphp

<x-page-shell
    :eyebrow="__('packages.eyebrow')"
    :title="__('packages.title')"
    :lead="__('packages.lead')"
    :meta-title="__('packages.meta_title')"
    :meta-description="__('packages.meta_description')"
    :footer-services="$footerServices"
    :trail="[
        ['label' => __('nav.home'), 'url' => route('home')],
        ['label' => __('nav.packages'), 'url' => null],
    ]"
>
    <x-slot:cta-title>{{ __('packages.cta.title') }}</x-slot:cta-title>
    <x-slot:cta-lead>{{ __('packages.cta.lead') }}</x-slot:cta-lead>

    {{--
        The comparison.

        A real <table>, because this is tabular data and a screen reader user
        comparing four packages needs the row and column headers announced. A
        grid of divs would look identical and tell her nothing.

        It scrolls sideways rather than reflowing into four stacked lists: at
        390px a four-column comparison cannot fit, and stacking it would
        destroy the one thing a comparison is for. The scrolling itself needs
        `contain: paint` as well as overflow — see .table-scroller in app.css
        for the mobile-viewport bug that costs. The wrapper is focusable
        with a role and a label so a keyboard user can scroll it — a
        scrollable region that only a mouse can reach is a trap.
    --}}
    <section class="py-20 sm:py-28" aria-labelledby="comparison-heading">
        <x-container>
            <x-section-heading
                id="comparison-heading"
                :title="__('packages.comparison.title')"
                :lead="__('packages.comparison.lead')"
            />

            @if ($services->isEmpty())
                <p class="mt-10 text-muted">{{ __('home.packages.empty') }}</p>
            @else
                <p class="mt-6 text-sm text-muted lg:hidden">{{ __('packages.comparison.scroll_hint') }}</p>

                <div
                    class="table-scroller reveal mt-8 rounded-2xl ring-1 ring-line"
                    tabindex="0"
                    role="region"
                    aria-label="{{ __('packages.comparison.aria') }}"
                >
                    <table class="w-full min-w-[46rem] border-collapse bg-white text-start text-sm">
                        <caption class="sr-only">{{ __('packages.comparison.aria') }}</caption>

                        <thead>
                            <tr class="border-b border-line">
                                {{-- start-0 not left-0: the sticky column has to
                                     pin to the reading edge in both languages. --}}
                                <th scope="col" class="sticky start-0 z-10 bg-white p-4 text-start font-medium text-muted">
                                    {{ __('packages.comparison.feature_column') }}
                                </th>

                                @foreach ($services as $service)
                                    <th scope="col" class="min-w-[11rem] p-4 text-start align-bottom">
                                        <span class="font-display text-base font-semibold text-ink">{{ $service->name }}</span>
                                        <span class="mt-1 block text-xs leading-relaxed font-normal text-muted">{{ $service->subtitle }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            {{-- Price, sessions and duration: read from the model. --}}
                            <tr class="border-b border-line">
                                <th scope="row" class="sticky start-0 z-10 bg-sage/40 p-4 text-start font-medium text-ink">
                                    {{ $rows['price'] }}
                                </th>
                                @foreach ($services as $service)
                                    <td class="p-4 align-top">
                                        {{-- dir=ltr: the numeral and the currency
                                             reorder inside Arabic without it. --}}
                                        <bdi dir="ltr" class="font-display text-xl font-semibold text-accent">{{ number_format((float) $service->price) }}</bdi>
                                        <span class="text-xs text-muted">{{ __('common.currency') }}</span>
                                    </td>
                                @endforeach
                            </tr>

                            <tr class="border-b border-line">
                                <th scope="row" class="sticky start-0 z-10 bg-white p-4 text-start font-medium text-ink">{{ $rows['sessions'] }}</th>
                                @foreach ($services as $service)
                                    <td class="p-4 align-top text-ink"><bdi dir="ltr">{{ $service->sessions_count }}</bdi></td>
                                @endforeach
                            </tr>

                            <tr class="border-b border-line">
                                <th scope="row" class="sticky start-0 z-10 bg-sage/40 p-4 text-start font-medium text-ink">{{ $rows['duration'] }}</th>
                                @foreach ($services as $service)
                                    <td class="p-4 align-top text-ink">
                                        <bdi dir="ltr">{{ $service->duration_minutes }}</bdi> {{ __('common.minutes') }}
                                    </td>
                                @endforeach
                            </tr>

                            {{-- The rest: wording from the slug-keyed matrix. --}}
                            @foreach ($copyRows as $index => $key)
                                <tr class="border-b border-line">
                                    <th scope="row" @class([
                                        'sticky start-0 z-10 p-4 text-start font-medium text-ink',
                                        'bg-white' => $index % 2 === 0,
                                        'bg-sage/40' => $index % 2 !== 0,
                                    ])>{{ $rows[$key] }}</th>

                                    @foreach ($services as $service)
                                        <td class="p-4 align-top leading-relaxed text-muted">
                                            {{ $matrix[$service->slug][$key] ?? '—' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            <tr>
                                <th scope="row" class="sticky start-0 z-10 bg-white p-4"><span class="sr-only">{{ __('packages.comparison.cta') }}</span></th>
                                @foreach ($services as $service)
                                    <td class="p-4 align-top">
                                        <x-button :href="route('booking', ['service' => $service->slug])" variant="ghost" class="w-full">
                                            {{ __('packages.comparison.cta') }}
                                            <span class="sr-only">— {{ $service->name }}</span>
                                        </x-button>
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </x-container>
    </section>

    <x-statement>{{ __('packages.statement') }}</x-statement>

    {{--
        What happens between sessions — the substance the homepage section
        omits entirely, and the actual difference between the packages.

        ASYMMETRIC ON PURPOSE. The steps are offset from each other rather than
        sitting in an even grid: alternate items are pushed down and inward on
        large screens, so the eye travels down a zigzag instead of scanning a
        table. It is the cheapest way to get composition out of pure text.
    --}}
    <section class="py-20 sm:py-28" aria-labelledby="between-heading">
        <x-container>
            <div class="max-w-2xl">
                <x-section-heading
                    id="between-heading"
                    :eyebrow="__('packages.between.eyebrow')"
                    :title="__('packages.between.title')"
                    :lead="__('packages.between.lead')"
                />
            </div>

            <ol class="mt-14 grid gap-x-10 gap-y-12 sm:grid-cols-2">
                @foreach (__('packages.between.steps') as $index => $step)
                    <li @class([
                        'reveal relative',
                        // Every second item drops, which is what turns two
                        // columns into a composition rather than a grid.
                        'sm:mt-16' => $index % 2 !== 0,
                    ])>
                        {{--
                            aria-hidden: the list is already ordered, so a
                            screen reader counts it for free and hearing "one"
                            twice would be noise.

                            accent-dark, not a pale sage watermark. A washed-out
                            numeral cannot reach 3:1 against paper — that is
                            arithmetic, not taste — and the contrast rule
                            outranks the effect. At full strength it reads as a
                            deliberate step marker rather than a texture, which
                            is a better answer than the one I was defending.
                        --}}
                        <span aria-hidden="true" class="font-display text-5xl leading-none font-semibold text-accent-dark">
                            <bdi dir="ltr">{{ $index + 1 }}</bdi>
                        </span>

                        <h3 class="mt-5 font-display text-xl font-semibold text-ink">{{ $step['title'] }}</h3>
                        <p class="mt-3 max-w-md leading-relaxed text-pretty text-muted">{{ $step['body'] }}</p>
                    </li>
                @endforeach
            </ol>
        </x-container>
    </section>

    {{-- Payment and cancellation. Asymmetric split: the terms take the wider
         column, the note sits under them rather than beside. --}}
    <section class="bg-sage/50 py-20 sm:py-28" aria-labelledby="terms-heading">
        <x-container>
            <div class="grid gap-12 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-5">
                    <x-section-heading
                        id="terms-heading"
                        :eyebrow="__('packages.terms.eyebrow')"
                        :title="__('packages.terms.title')"
                        :lead="__('packages.terms.lead')"
                    />
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:col-span-7">
                    @foreach (['payment', 'cancellation'] as $block)
                        <x-card class="reveal">
                            <h3 class="font-display text-lg font-semibold text-ink">
                                {{ __("packages.terms.{$block}.title") }}
                            </h3>

                            <ul class="mt-4 space-y-3 text-sm leading-relaxed text-muted">
                                @foreach (__("packages.terms.{$block}.items") as $item)
                                    <li class="flex items-start gap-2.5">
                                        <svg class="mt-1 size-4 shrink-0 text-accent" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="m4 10.5 4 4 8-9" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <span>{{ $item }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </x-card>
                    @endforeach

                    <p class="text-sm leading-relaxed text-muted sm:col-span-2">{{ __('packages.terms.note') }}</p>
                </div>
            </div>
        </x-container>
    </section>

    {{-- Buying questions. Same native accordion the homepage FAQ uses — one
         component, not a second variant that drifts. --}}
    <section class="py-20 sm:py-28" aria-labelledby="buying-faq-heading">
        <x-container size="narrow">
            <x-section-heading
                id="buying-faq-heading"
                :eyebrow="__('packages.faq.eyebrow')"
                :title="__('packages.faq.title')"
                :lead="__('packages.faq.lead')"
            />

            @if ($faqs->isEmpty())
                <p class="mt-10 text-muted">{{ __('packages.faq.empty') }}</p>
            @else
                <ul class="mt-10 space-y-3">
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
            @endif
        </x-container>
    </section>
</x-page-shell>
