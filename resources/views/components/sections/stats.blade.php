@php
    /*
     * THE FOUR HEADLINE FIGURES, EVERY ONE READ FROM CONFIG.
     *
     * Not one number is written into this file, and StatsTest fails if one
     * ever is. That is not tidiness: a figure on a clinic's homepage is a
     * claim a patient may act on, and a number typed into a Blade file is a
     * claim nobody can trace back to evidence.
     *
     * config/clinic.php carries what the evidence is for each — certificates,
     * training logs, the working_hours table. EVERY FIGURE HERE MUST BE
     * EVIDENCED; if you cannot say where one came from, it does not belong on
     * this page.
     *
     * THERE IS NO RATING IN THIS STRIP ANY MORE. The 4.9 that used to sit here
     * was invented. A rating is now computed from real approved reviews and
     * appears only once there are at least ten of them — see App\Support\Reviews
     * and the stories section.
     *
     * Deliberately none of them a clinical outcome: no average result, no
     * success rate. See the hero for why that is a standing rule.
     */
    $figures = [
        [
            'value' => (int) config('clinic.practitioner.years_practising'),
            'suffix' => '',
            'label' => __('home.stats.years'),
        ],
        [
            'value' => (int) config('clinic.practitioner.cases_followed'),
            'suffix' => '+',
            'label' => __('home.stats.cases'),
        ],
        [
            'value' => (int) config('clinic.practitioner.clinical_training_hours'),
            'suffix' => '+',
            'label' => __('home.stats.training_hours'),
        ],
        [
            'value' => (int) config('clinic.support_days'),
            'suffix' => '',
            'label' => __('home.stats.support_days'),
        ],
    ];
@endphp

<section class="bg-ink py-14 text-white" aria-labelledby="stats-heading">
    <x-container>
        {{-- The band is visual; the heading keeps it a labelled region for
             anyone navigating by landmark rather than by eye. --}}
        <h2 id="stats-heading" class="sr-only">{{ __('home.stats.title') }}</h2>

        <dl class="grid grid-cols-2 gap-y-10 gap-x-6 lg:grid-cols-4">
            @foreach ($figures as $figure)
                <div class="reveal text-center">
                    <dt class="sr-only">{{ $figure['label'] }}</dt>
                    <dd>
                        {{-- dir=ltr: numerals and the + sign reorder inside an
                             Arabic paragraph without it. --}}
                        <bdi dir="ltr" class="stat-figure font-display text-4xl font-semibold text-white sm:text-5xl">{{ number_format($figure['value']).$figure['suffix'] }}</bdi>

                        <span class="mt-2 block text-sm leading-relaxed text-white/70">{{ $figure['label'] }}</span>
                    </dd>
                </div>
            @endforeach
        </dl>
    </x-container>
</section>
