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

        @php
            /*
             * The step icons, in order. Held here rather than in the
             * translation files because a glyph is not copy — the Arabic and
             * English steps are the same four steps and must not be able to
             * drift onto different pictures.
             */
            $stepIcons = ['calendar-check', 'listening', 'plan-document', 'adjust'];

            /*
             * The FIRST step is the filled one. Booking is what this section
             * exists to lead to, and it is the only one of the four a visitor
             * can act on from here — the rest describe what happens after.
             * Derived from position, never hardcoded to a key.
             */
            $promotedStep = 0;
        @endphp

        <ol class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (__('home.how_it_works.steps') as $index => $step)
                <li class="reveal flex">
                    <x-feature-card
                        :icon="$stepIcons[$loop->index] ?? 'target'"
                        :title="$step['title']"
                        :body="$step['body']"
                        :promoted="$loop->index === $promotedStep"
                    />
                </li>
            @endforeach
        </ol>
    </x-container>
</section>
