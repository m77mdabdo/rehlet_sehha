@props(['foods'])

{{--
    Build your plate.

    Tap Egyptian foods, watch the plate fill, and read what the plate is mostly
    made of.

    THE CONSTRAINT IS THE FEATURE: no calories, no grams, no weights, no macros,
    no score, no target, no number of any kind. Every piece of feedback is about
    PROPORTION — "this plate is mostly starch, it needs vegetables and a
    protein" — and never about quantity.

    Numeric feedback teaches people to measure food, and measuring food is the
    habit this clinic exists to undo; its whole positioning is a plan built on
    your own history and bloodwork rather than a number to hit. A calorie
    readout here would contradict the service being sold three sections up the
    page. It is also actively harmful to anyone with a disordered relationship
    to eating, for whom a number attached to a food is not neutral information
    but the mechanism of the disorder. A tool on a nutrition clinic's homepage
    must not be a calorie counter in disguise.

    PlateFeedbackHasNoNumbersTest fails the build if a digit reaches a feedback
    string, in the translation files or in the rendered page. That test is not
    to be relaxed.

    The proportion bar is drawn from RATIOS, never printed as figures: a segment
    is wider because that group takes more of the plate, and the width is never
    written down as a percentage beside it.
--}}

@php
    use App\Enums\FoodGroup;
    use App\Support\Locales;

    $grouped = $foods->groupBy(fn ($food): string => $food->group->value);

    // Group order is the enum's, so the legend, the bar and the food lists all
    // agree without any of them sorting for itself.
    $groups = collect(FoodGroup::cases())
        ->filter(fn (FoodGroup $group): bool => $grouped->has($group->value));

    $feedback = __('plate.feedback');
@endphp

<section id="plate" class="bg-white py-20 sm:py-24" aria-labelledby="plate-heading">
    <x-container>
        <x-section-heading
            id="plate-heading"
            :eyebrow="__('plate.eyebrow')"
            :title="__('plate.title')"
            :lead="__('plate.lead')"
        />

        @if ($foods->isEmpty())
            <p class="mt-10 text-muted">{{ __('plate.plate_empty') }}</p>
        @else
            <div
                class="mt-12 grid gap-8 lg:grid-cols-[1fr_20rem]"
                data-plate
                data-plate-feedback="{{ json_encode($feedback, JSON_UNESCAPED_UNICODE) }}"
                data-plate-colours="{{ json_encode(
                    collect(FoodGroup::cases())->mapWithKeys(fn (FoodGroup $g): array => [$g->value => $g->colour()])->all()
                ) }}"
                data-plate-labels="{{ json_encode(
                    collect(FoodGroup::cases())->mapWithKeys(fn (FoodGroup $g): array => [$g->value => $g->label()])->all(),
                    JSON_UNESCAPED_UNICODE
                ) }}"
            >
                {{-- The foods, grouped. --}}
                <div class="order-2 lg:order-1">
                    @foreach ($groups as $group)
                        <div class="mb-6">
                            <h3 class="flex items-center gap-2 text-sm font-semibold text-ink">
                                <span
                                    class="inline-block size-3 rounded-sm"
                                    style="background-color: {{ $group->colour() }}"
                                    aria-hidden="true"
                                ></span>
                                {{ $group->label() }}
                            </h3>

                            <ul class="mt-3 flex flex-wrap gap-2">
                                @foreach ($grouped[$group->value] as $food)
                                    <li>
                                        {{--
                                            aria-pressed carries the state, so a
                                            screen reader hears "Foul medames,
                                            toggle button, pressed" rather than
                                            needing the visual highlight.
                                        --}}
                                        <button
                                            type="button"
                                            data-plate-food="{{ $food->id }}"
                                            data-plate-group="{{ $food->group->value }}"
                                            data-plate-name="{{ $food->name }}"
                                            aria-pressed="false"
                                            class="inline-flex items-center gap-2 rounded-pill bg-sage/60 px-3 py-2 text-sm text-ink ring-1 ring-line transition hover:ring-accent focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-dark aria-pressed:bg-accent aria-pressed:text-white aria-pressed:ring-accent"
                                        >
                                            <span aria-hidden="true">{{ $food->emoji }}</span>
                                            <span>{{ $food->name }}</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>

                {{-- The plate itself. --}}
                <div class="order-1 lg:order-2">
                    <x-card class="p-6">
                        <h3 class="text-sm font-semibold text-ink">{{ __('plate.plate_label') }}</h3>

                        {{--
                            The proportion bar. Segments are sized by ratio and
                            NEVER labelled with a figure — see the block comment
                            at the top of this file.
                        --}}
                        <div
                            class="mt-4 flex h-6 w-full overflow-hidden rounded-pill bg-sage"
                            data-plate-bar
                            data-empty-label="{{ __('plate.plate_empty') }}"
                            role="img"
                            aria-label="{{ __('plate.plate_empty') }}"
                        ></div>

                        <ul class="mt-4 space-y-1 text-sm text-muted" data-plate-chosen></ul>

                        {{-- The feedback. One string, from the translation
                             file, chosen by proportion. --}}
                        <p
                            class="mt-5 rounded-lg bg-sage/60 p-4 text-sm leading-relaxed text-ink"
                            data-plate-message
                            role="status"
                            aria-live="polite"
                        >{{ $feedback['empty'] }}</p>

                        <button
                            type="button"
                            data-plate-reset
                            class="mt-4 w-full rounded-pill px-4 py-2 text-sm font-medium text-accent-dark ring-1 ring-line transition hover:bg-sage/60 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-dark"
                        >
                            {{ __('plate.reset') }}
                        </button>

                        <p class="mt-4 text-xs leading-relaxed text-muted">{{ __('plate.disclaimer') }}</p>
                    </x-card>
                </div>
            </div>
        @endif
    </x-container>
</section>
