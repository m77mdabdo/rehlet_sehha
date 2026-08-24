@php
    use App\Support\FeaturedPackage;

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

    /*
     * The two rows that carry a real yes/no. In those a tick or a dash reads
     * faster than a sentence — but the sentence still renders in full beside
     * it, because shortening the answer would be changing what the table says.
     *
     * The other four are not yes/no questions. "Where it happens" is a place
     * and "who it suits" is an audience; putting a tick on either would be
     * decoration pretending to be information.
     */
    $stateRows = ['between', 'adjust'];

    $absentMarkers = __('packages.comparison.absent_markers');

    $isAbsent = function (string $value) use ($absentMarkers): bool {
        foreach ($absentMarkers as $marker) {
            if (str_starts_with($value, $marker)) {
                return true;
            }
        }

        return false;
    };

    /*
     * The recommended column, from the one place that decides it. The homepage
     * highlights the same package; a patient who saw one recommendation there
     * and a different one here has been told the clinic does not know its own
     * mind.
     */
    $featuredIndex = FeaturedPackage::indexIn($services);
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

        TWO PRESENTATIONS OF ONE SET OF FACTS. Below lg it is four stacked
        package cards; from lg up it is a real table. Both read the same
        services and the same matrix — nothing is written twice — and exactly
        one of them is in the document at a time, via display:none rather than
        a visual-only hide, so a screen reader is never offered both.

        THE TABLE IS A TABLE because this is tabular data: someone comparing
        four packages on a row needs the row and column headers announced, and
        a grid of divs would look identical and tell her nothing.

        IT DOES NOT SCROLL SIDEWAYS AT ALL ANY MORE. Scrolling a comparison
        horizontally defeats the one thing a comparison is for, and it was also
        what widened the whole mobile layout viewport. The table has no
        min-width and only exists from lg up, so it always fits; below lg it is
        display:none and the bug has nothing to act on. See .table-frame in
        app.css — the containment is kept as a backstop, and the switch from
        overflow auto to clip is what lets the sticky header work.

        THE COLUMNS ARE NOT EQUAL, AND THAT IS THE POINT. Four packages of
        identical weight leave a patient comparing thirty-two cells and
        choosing none. One column is raised as the default answer; the others
        carry the same information at lower contrast. Nothing is hidden and no
        price is disguised — the recommendation is the cheaper of the two
        middle options, and it survives being noticed.
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
                {{-- ---------------------------------------------------------
                     Below lg: stacked cards, one per package.
                     --------------------------------------------------------- --}}
                <ul class="mt-10 space-y-6 lg:hidden">
                    @foreach ($services as $index => $service)
                        @php $isFeatured = $index === $featuredIndex; @endphp

                        <li class="reveal">
                            <div @class([
                                'overflow-hidden rounded-2xl bg-white ring-1',
                                'ring-line' => ! $isFeatured,
                                'ring-2 ring-ink shadow-lg' => $isFeatured,
                            ])>
                                {{-- The header block. Navy for the recommended
                                     one, paper for the rest. --}}
                                <div @class([
                                    'p-6',
                                    'bg-ink text-white' => $isFeatured,
                                    'bg-paper' => ! $isFeatured,
                                ])>
                                    @if ($isFeatured)
                                        {{-- Gold on navy measures 6.73:1, which
                                             is the only place in this design
                                             where the brand gold clears AA as
                                             text. On white it is 2.06:1. --}}
                                        <p class="mb-3 inline-flex rounded-pill bg-gold px-3 py-1 text-xs font-semibold text-ink">
                                            {{ __('packages.comparison.recommended') }}
                                        </p>
                                    @endif

                                    <h3 @class([
                                        'font-display text-xl font-semibold',
                                        'text-white' => $isFeatured,
                                        'text-ink' => ! $isFeatured,
                                    ])>{{ $service->name }}</h3>

                                    <p @class([
                                        'mt-1 text-sm leading-relaxed',
                                        'text-white/75' => $isFeatured,
                                        'text-muted' => ! $isFeatured,
                                    ])>{{ $service->subtitle }}</p>

                                    {{-- Price at display scale, not as a row. --}}
                                    <p class="mt-5 flex items-baseline gap-2">
                                        <bdi dir="ltr" @class([
                                            'font-display text-4xl font-semibold',
                                            'text-gold' => $isFeatured,
                                            'text-accent' => ! $isFeatured,
                                        ])>{{ number_format((float) $service->price) }}</bdi>
                                        <span @class([
                                            'text-sm',
                                            'text-white/75' => $isFeatured,
                                            'text-muted' => ! $isFeatured,
                                        ])>{{ __('common.currency') }}</span>
                                    </p>

                                    {{-- The CTA sits in the header so a patient
                                         who has decided does not scroll past
                                         five more rows to act. --}}
                                    <div class="mt-5">
                                        <x-button
                                            :href="route('booking', ['service' => $service->slug])"
                                            :variant="$isFeatured ? 'light' : 'primary'"
                                            class="w-full"
                                        >
                                            {{ __('packages.comparison.cta') }}
                                            <span class="sr-only">— {{ $service->name }}</span>
                                        </x-button>
                                    </div>
                                </div>

                                {{-- The attributes, as label/value pairs. --}}
                                <dl class="divide-y divide-line">
                                    <div class="flex items-baseline justify-between gap-6 px-6 py-4">
                                        <dt class="text-sm text-muted">{{ $rows['sessions'] }}</dt>
                                        <dd class="text-end text-sm font-medium text-ink"><bdi dir="ltr">{{ $service->sessions_count }}</bdi></dd>
                                    </div>

                                    <div class="flex items-baseline justify-between gap-6 px-6 py-4">
                                        <dt class="text-sm text-muted">{{ $rows['duration'] }}</dt>
                                        <dd class="text-end text-sm font-medium text-ink">
                                            <bdi dir="ltr">{{ $service->duration_minutes }}</bdi> {{ __('common.minutes') }}
                                        </dd>
                                    </div>

                                    @foreach ($copyRows as $key)
                                        @php
                                            $value = $matrix[$service->slug][$key] ?? '—';
                                            $stateful = in_array($key, $stateRows, true);
                                            $absent = $stateful && $isAbsent($value);
                                        @endphp

                                        <div class="px-6 py-4">
                                            <dt class="text-sm text-muted">{{ $rows[$key] }}</dt>
                                            <dd class="mt-1 flex items-start gap-2 text-sm leading-relaxed text-ink">
                                                @if ($stateful)
                                                    <x-comparison-state :absent="$absent" />
                                                @endif
                                                <span>{{ $value }}</span>
                                            </dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        </li>
                    @endforeach
                </ul>

                {{-- ---------------------------------------------------------
                     lg and up: the table.
                     --------------------------------------------------------- --}}
                {{-- No tabindex and no region role: this no longer scrolls, so
                     making it a tab stop would be a focus trap that goes
                     nowhere. The table's own caption names it for assistive
                     technology. --}}
                <div class="table-frame reveal mt-10 hidden rounded-2xl ring-1 ring-line lg:block">
                    {{--
                        border-separate, NOT border-collapse.
                        Chrome does not honour position:sticky on a cell inside
                        a collapsed-border table — the header simply scrolls
                        away, silently, which is exactly what it did on the
                        first build of this. Separate borders with zero spacing
                        looks identical and lets the header and the spine stick.

                        Row separators are drawn as a top border on the cells
                        rather than a full box on each one, which is what turns
                        a grid of boxes back into a table.
                    --}}
                    <table class="w-full border-separate border-spacing-0 bg-white text-start text-sm">
                        <caption class="sr-only">{{ __('packages.comparison.aria') }}</caption>

                        <thead>
                            <tr>
                                {{--
                                    The spine. Sticky on BOTH axes so it is the
                                    intersection cell, and z-30 so it stays above
                                    the header row and the label column, which
                                    are each sticky on one axis.

                                    start-0, never left-0: it pins to the reading
                                    edge, which is the right in Arabic.
                                --}}
                                <th scope="col" class="sticky top-18 start-0 z-30 w-44 bg-sage p-5 text-start align-bottom font-medium text-ink">
                                    {{ __('packages.comparison.feature_column') }}
                                </th>

                                @foreach ($services as $index => $service)
                                    @php $isFeatured = $index === $featuredIndex; @endphp

                                    <th scope="col" @class([
                                        'sticky top-18 z-20 p-5 text-start align-bottom',
                                        'bg-ink text-white' => $isFeatured,
                                        'bg-paper' => ! $isFeatured,
                                    ])>
                                        @if ($isFeatured)
                                            <p class="mb-3 inline-flex rounded-pill bg-gold px-3 py-1 text-xs font-semibold text-ink">
                                                {{ __('packages.comparison.recommended') }}
                                            </p>
                                        @endif

                                        <span @class([
                                            'block font-display text-lg font-semibold',
                                            'text-white' => $isFeatured,
                                            'text-ink' => ! $isFeatured,
                                        ])>{{ $service->name }}</span>

                                        <span @class([
                                            'mt-1 block text-xs leading-relaxed font-normal',
                                            'text-white/75' => $isFeatured,
                                            'text-muted' => ! $isFeatured,
                                        ])>{{ $service->subtitle }}</span>

                                        {{-- Price, out of the row rhythm and at
                                             display scale — the largest number
                                             anywhere on this page. --}}
                                        <span class="mt-4 flex items-baseline gap-1.5">
                                            <bdi dir="ltr" @class([
                                                'font-display text-3xl font-semibold',
                                                'text-gold' => $isFeatured,
                                                'text-accent' => ! $isFeatured,
                                            ])>{{ number_format((float) $service->price) }}</bdi>
                                            <span @class([
                                                'text-xs font-normal',
                                                'text-white/75' => $isFeatured,
                                                'text-muted' => ! $isFeatured,
                                            ])>{{ __('common.currency') }}</span>
                                        </span>

                                        <span class="mt-4 block font-normal">
                                            <x-button
                                                :href="route('booking', ['service' => $service->slug])"
                                                :variant="$isFeatured ? 'light' : 'ghost'"
                                                class="w-full"
                                            >
                                                {{ __('packages.comparison.cta') }}
                                                <span class="sr-only">— {{ $service->name }}</span>
                                            </x-button>
                                        </span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @php $bodyRows = ['sessions', 'duration', ...$copyRows]; @endphp

                            @foreach ($bodyRows as $rowIndex => $key)
                                {{-- Zebra in sage at 40%, which measures 5.44:1
                                     against the muted body text. Grey would be
                                     a colour this palette does not have. --}}
                                <tr @class(['bg-sage/40' => $rowIndex % 2 !== 0])>
                                    <th scope="row" @class([
                                        'sticky start-0 z-10 border-t border-line p-5 text-start align-top font-medium text-ink',
                                        'bg-sage' => $rowIndex % 2 === 0,
                                        'bg-[#D3E0EC]' => $rowIndex % 2 !== 0,
                                    ])>{{ $rows[$key] }}</th>

                                    @foreach ($services as $index => $service)
                                        @php
                                            $isFeatured = $index === $featuredIndex;

                                            $value = match ($key) {
                                                'sessions' => (string) $service->sessions_count,
                                                'duration' => null,
                                                default => $matrix[$service->slug][$key] ?? '—',
                                            };

                                            $stateful = in_array($key, $stateRows, true);
                                            $absent = $stateful && $value !== null && $isAbsent($value);
                                        @endphp

                                        {{-- The recommended column carries its own
                                             side edges and a faint tint through
                                             every row, so the navy header reads
                                             as the top of one column rather than
                                             a block floating above unrelated
                                             cells. border-x, not border-s/e:
                                             the column has two sides and both
                                             get one in either direction. --}}
                                        <td @class([
                                            'border-t border-line p-5 align-top leading-relaxed',
                                            'border-x-2 border-ink/15 bg-sage/25 font-medium text-ink' => $isFeatured,
                                            'border-b-2 border-b-ink/15' => $isFeatured && $rowIndex === count($bodyRows) - 1,
                                            'text-muted' => ! $isFeatured,
                                        ])>
                                            @if ($key === 'duration')
                                                <bdi dir="ltr">{{ $service->duration_minutes }}</bdi> {{ __('common.minutes') }}
                                            @elseif ($key === 'sessions')
                                                <bdi dir="ltr">{{ $value }}</bdi>
                                            @else
                                                <span class="flex items-start gap-2">
                                                    @if ($stateful)
                                                        <x-comparison-state :absent="$absent" />
                                                    @endif
                                                    <span>{{ $value }}</span>
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
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
