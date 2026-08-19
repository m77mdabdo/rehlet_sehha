{{--
    Four steps, entirely from translation files — there is no database concept
    behind them and inventing one would be a table with four permanent rows
    nobody ever edits.

    An ordered list, because the order is the content: these steps happen in
    sequence, and a screen reader should say "1 of 4".
--}}

<section id="how-it-works" class="py-20 sm:py-24" aria-labelledby="how-it-works-heading">
    <x-container>
        <x-section-heading
            id="how-it-works-heading"
            :eyebrow="__('home.how_it_works.eyebrow')"
            :title="__('home.how_it_works.title')"
            :lead="__('home.how_it_works.lead')"
        />

        <ol class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (__('home.how_it_works.steps') as $index => $step)
                <li class="reveal">
                    <span
                        class="inline-flex size-11 items-center justify-center rounded-pill bg-ink font-display text-lg font-semibold text-white"
                        aria-hidden="true"
                    >
                        {{ $loop->iteration }}
                    </span>

                    <h3 class="mt-4 font-display text-lg font-semibold text-ink">
                        {{ $step['title'] }}
                    </h3>

                    <p class="mt-2 text-sm leading-relaxed text-muted">
                        {{ $step['body'] }}
                    </p>
                </li>
            @endforeach
        </ol>
    </x-container>
</section>
