@props(['foods'])

{{--
    Build your plate.

    Tap Egyptian foods, watch the plate fill, and read what the plate is mostly
    made of.

    THE QUALITATIVE FEEDBACK IS THE PRIMARY OUTPUT. The proportion assessment —
    "this plate is mostly starch, it needs vegetables and a protein" — is what
    the clinic actually teaches, and it stays visually dominant. The energy
    estimate added on 2026-10-12 is secondary information placed below it, in
    smaller, muted type. If a future change makes the number the headline, it
    has broken the thing this section is for.

    Numeric feedback teaches people to measure food, and measuring food is the
    habit this clinic exists to undo; the service being sold three sections up
    the page is a plan built on your own history and bloodwork rather than a
    number to hit. For anyone with a disordered relationship to eating, a
    number attached to a food is not neutral information but the mechanism of
    the disorder. None of that stopped being true — the practitioner weighed it
    and decided an honest span, kept secondary and kept free of any target, is
    more useful to her patients than silence.

    So what is still absolutely forbidden here, and is enforced by
    PlateFeedbackHasNoNumbersTest: a target, a goal, a daily total, a remaining
    count, a percentage, a progress bar, a streak, any history, and any colour
    that reads as pass or fail. Nothing is stored — no account, no session
    record, nothing written anywhere.

    DIGITS ARE PERMITTED IN EXACTLY TWO PLACES, both marked in the markup so a
    test can find them: `data-plate-estimate` (the energy span) and
    `data-plate-quantity` (how many portions). Everywhere else in this section,
    a digit still fails the build. Note that neither is server-rendered — the
    initial state has no portions and no estimate, so this file emits no
    visible digit at all.

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
                {{-- Household measures, singular and plural. The count is put
                     in front of these by the script, inside the scoped
                     quantity element. --}}
                data-plate-units="{{ json_encode([
                    'one' => __('plate.units'),
                    'many' => __('plate.units_plural'),
                ], JSON_UNESCAPED_UNICODE) }}"
                data-plate-portion-labels="{{ json_encode(__('plate.portions'), JSON_UNESCAPED_UNICODE) }}"
                {{-- The estimate's own copy. Carried separately from the
                     feedback payload so the test that forbids digits in
                     FEEDBACK can stay absolute while this one is scoped. --}}
                data-plate-estimate-strings="{{ json_encode(__('plate.estimate'), JSON_UNESCAPED_UNICODE) }}"
                data-plate-custom-strings="{{ json_encode(__('plate.custom'), JSON_UNESCAPED_UNICODE) }}"
                data-plate-groups="{{ json_encode(
                    collect(FoodGroup::cases())->map(fn (FoodGroup $g): array => ['value' => $g->value, 'label' => $g->label()])->all(),
                    JSON_UNESCAPED_UNICODE
                ) }}"
            >
                {{-- The foods, grouped. --}}
                {{-- min-w-0: a grid item defaults to min-width:auto and will not
                     shrink below its content. A long food name a patient types
                     into the custom-food panel pushed the whole grid wider than
                     the viewport at 320. --}}
                <div class="order-2 min-w-0 lg:order-1">
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
                                            data-plate-unit="{{ $food->unit->value }}"
                                            data-plate-portion="{{ $food->default_portion }}"
                                            {{--
                                                The energy span is emitted ONLY for a food the
                                                practitioner has signed off. A row awaiting review
                                                still appears here and still counts towards the
                                                proportion feedback — it simply carries no figure,
                                                so an unverified number cannot reach the browser
                                                even if the script were changed to look for one.
                                            --}}
                                            @if ($food->hasPublishableEstimate())
                                                data-plate-energy-min="{{ $food->energy_min }}"
                                                data-plate-energy-max="{{ $food->energy_max }}"
                                            @endif
                                            aria-pressed="false"
                                            class="tap-target gap-2 rounded-pill bg-sage/60 px-3 text-sm text-ink ring-1 ring-line transition hover:ring-accent focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-dark aria-pressed:bg-accent aria-pressed:text-white aria-pressed:ring-accent"
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
                <div class="order-1 min-w-0 lg:order-2">
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
                        {{--
                            THE PRIMARY OUTPUT. Larger type, its own filled
                            panel, first in reading order after the plate. The
                            estimate below is deliberately smaller and muted:
                            the proportion assessment is what the clinic
                            teaches, and the number is a footnote to it.
                        --}}
                        <p
                            class="mt-5 rounded-lg bg-sage/60 p-4 text-base leading-relaxed font-medium text-ink"
                            data-plate-message
                            role="status"
                            aria-live="polite"
                        >{{ $feedback['empty'] }}</p>

                        {{--
                            THE ENERGY ESTIMATE — secondary, and scoped.

                            Everything inside this element may contain digits;
                            nothing outside it may. See the file header and
                            PlateFeedbackHasNoNumbersTest.

                            It is hidden until there is something to estimate,
                            and it renders NO figure when every food on the
                            plate is still awaiting the practitioner's sign-off
                            — which is the state on a fresh install.

                            There is deliberately no bar, no dial, no colour
                            scale and no comparison. A span of text, and a
                            caveat that does not go away.
                        --}}
                        <div
                            class="mt-4 border-t border-line pt-4"
                            data-plate-estimate
                            hidden
                        >
                            <p class="text-xs font-medium tracking-wide text-muted">{{ __('plate.estimate.label') }}</p>

                            <p
                                class="mt-1 text-sm font-semibold text-ink"
                                data-plate-estimate-value
                                role="status"
                                aria-live="polite"
                            ></p>

                            {{-- Shown only when part of the plate has no
                                 reviewed figure, so the span is never passed
                                 off as covering the whole meal. --}}
                            <p class="mt-1 text-xs leading-relaxed text-muted" data-plate-estimate-note hidden></p>

                            {{-- PERMANENT. Not a tooltip, not dismissible, no
                                 disclosure triangle. This line is the
                                 condition the practitioner attached to showing
                                 a number at all, and a patient who sees the
                                 number sees this too. --}}
                            <p class="mt-2 text-xs leading-relaxed text-muted">{{ __('plate.estimate.caveat') }}</p>
                        </div>

                        <button
                            type="button"
                            data-plate-reset
                            class="tap-target mt-4 w-full justify-center rounded-pill px-4 text-sm font-medium text-accent-dark ring-1 ring-line transition hover:bg-sage/60 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-dark"
                        >
                            {{ __('plate.reset') }}
                        </button>

                        <p class="mt-4 text-xs leading-relaxed text-muted">{{ __('plate.disclaimer') }}</p>
                    </x-card>

                    {{--
                        A FOOD SHE ADDS HERSELF.

                        NOT STORED ANYWHERE. No request is made, no session is
                        written, nothing reaches the database. It lives in a
                        JavaScript Map for as long as the tab is open and is
                        gone when she closes it — which is why the form is
                        plain markup with no action and no method, and why the
                        copy says so out loud rather than leaving her to guess.

                        Her own figure is labelled as hers everywhere it
                        appears, so it can never be read as a reviewed, sourced
                        value sitting next to ones that are.
                    --}}
                    <x-card class="mt-4 p-6">
                        <details data-plate-custom>
                            <summary class="tap-target-row cursor-pointer list-none text-sm font-medium text-accent-dark focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-dark">
                                {{ __('plate.custom.add') }}
                            </summary>

                            <div class="mt-4 space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-ink" for="plate-custom-name">{{ __('plate.custom.name') }}</label>
                                    <input
                                        id="plate-custom-name"
                                        type="text"
                                        data-plate-custom-name
                                        maxlength="40"
                                        autocomplete="off"
                                        class="mt-1 w-full rounded-lg border-line bg-white px-3 py-2 text-sm text-ink ring-1 ring-line focus:outline-2 focus:outline-offset-0 focus:outline-accent-dark"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-ink" for="plate-custom-group">{{ __('plate.custom.group') }}</label>
                                    <select
                                        id="plate-custom-group"
                                        data-plate-custom-group
                                        class="mt-1 w-full rounded-lg bg-white px-3 py-2 text-sm text-ink ring-1 ring-line focus:outline-2 focus:outline-offset-0 focus:outline-accent-dark"
                                    >
                                        @foreach (FoodGroup::cases() as $group)
                                            <option value="{{ $group->value }}">{{ $group->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{--
                                    Either she points at a food we already have
                                    a reviewed figure for, or she gives her own
                                    number. Nothing is inferred for her: there
                                    is no "we guessed from the name".
                                --}}
                                <fieldset>
                                    <legend class="block text-xs font-medium text-ink">{{ __('plate.custom.energy_mode') }}</legend>

                                    <label class="tap-target-row mt-1 flex items-center gap-2 text-sm text-ink">
                                        <input type="radio" name="plate-custom-mode" value="like" checked data-plate-custom-mode class="size-4">
                                        <span>{{ __('plate.custom.like') }}</span>
                                    </label>

                                    <select
                                        data-plate-custom-like
                                        aria-label="{{ __('plate.custom.like') }}"
                                        class="mt-1 w-full rounded-lg bg-white px-3 py-2 text-sm text-ink ring-1 ring-line focus:outline-2 focus:outline-offset-0 focus:outline-accent-dark"
                                    >
                                        @foreach ($foods->filter->hasPublishableEstimate() as $food)
                                            <option
                                                value="{{ $food->id }}"
                                                data-energy-min="{{ $food->energy_min }}"
                                                data-energy-max="{{ $food->energy_max }}"
                                            >{{ $food->emoji }} {{ $food->name }}</option>
                                        @endforeach
                                    </select>

                                    <label class="tap-target-row mt-2 flex items-center gap-2 text-sm text-ink">
                                        <input type="radio" name="plate-custom-mode" value="own" data-plate-custom-mode class="size-4">
                                        <span>{{ __('plate.custom.own') }}</span>
                                    </label>

                                    <input
                                        type="number"
                                        inputmode="numeric"
                                        min="0"
                                        max="2000"
                                        step="10"
                                        data-plate-custom-energy
                                        aria-label="{{ __('plate.custom.own_value') }}"
                                        class="mt-1 w-full rounded-lg bg-white px-3 py-2 text-sm text-ink ring-1 ring-line focus:outline-2 focus:outline-offset-0 focus:outline-accent-dark"
                                        hidden
                                    >

                                    <p class="mt-1 text-xs leading-relaxed text-muted">{{ __('plate.custom.own_hint') }}</p>
                                </fieldset>

                                <button
                                    type="button"
                                    data-plate-custom-save
                                    class="tap-target w-full justify-center rounded-pill bg-ink px-4 text-sm font-medium text-white transition hover:bg-ink/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-dark"
                                >
                                    {{ __('plate.custom.save') }}
                                </button>

                                {{-- Said plainly, because "we don't store it"
                                     is the kind of claim a patient is entitled
                                     to read rather than infer. --}}
                                <p class="text-xs leading-relaxed text-muted">{{ __('plate.custom.not_saved') }}</p>
                            </div>
                        </details>
                    </x-card>
                </div>
            </div>
        @endif
    </x-container>
</section>
