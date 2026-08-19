@php
    /*
     * The four headline figures, read from config/clinic.php rather than
     * written into this file. They are facts about the practice, so they must
     * read identically in both languages — and a number inlined in markup is a
     * number nobody can find when it needs updating once a year.
     *
     * Deliberately none of them a clinical outcome: no average result, no
     * success rate. See the hero for why that is a standing rule here.
     */
    $stats = [
        ['value' => number_format((int) config('clinic.stats.cases')).'+', 'label' => __('home.stats.cases')],
        ['value' => config('clinic.stats.years').'+', 'label' => __('home.stats.years')],
        ['value' => number_format((float) config('clinic.stats.rating'), 1), 'label' => __('home.stats.rating'), 'suffix' => __('home.stats.rating_suffix')],
        ['value' => config('clinic.stats.support_days'), 'label' => __('home.stats.support_days')],
    ];
@endphp

<section class="bg-ink py-14 text-white" aria-labelledby="stats-heading">
    <x-container>
        {{-- The band is visual; the heading keeps it a labelled region for
             anyone navigating by landmark rather than by eye. --}}
        <h2 id="stats-heading" class="sr-only">{{ __('home.stats.title') }}</h2>

        <dl class="grid grid-cols-2 gap-y-10 gap-x-6 lg:grid-cols-4">
            @foreach ($stats as $stat)
                <div class="reveal text-center">
                    <dt class="sr-only">{{ $stat['label'] }}</dt>
                    <dd>
                        {{-- dir=ltr: numerals and the + sign reorder inside an
                             Arabic paragraph without it. --}}
                        <bdi dir="ltr" class="font-display text-4xl font-semibold text-white sm:text-5xl">
                            {{ $stat['value'] }}
                        </bdi>

                        @isset($stat['suffix'])
                            <span class="ms-1 text-sm text-white/60">{{ $stat['suffix'] }}</span>
                        @endisset

                        <span class="mt-2 block text-sm leading-relaxed text-white/70">{{ $stat['label'] }}</span>
                    </dd>
                </div>
            @endforeach
        </dl>
    </x-container>
</section>
