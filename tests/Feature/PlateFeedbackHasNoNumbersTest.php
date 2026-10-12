<?php

declare(strict_types=1);

use App\Enums\FoodGroup;
use App\Enums\PortionUnit;
use App\Models\PlateFood;
use Database\Seeders\PlateFoodSeeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * THE PLATE BUILDER'S NUMBER RULE. NARROWED ON 2026-10-12. STILL NOT TO BE
 * RELAXED FURTHER.
 *
 * ---------------------------------------------------------------------------
 * READ THIS BEFORE CHANGING ANY ASSERTION IN THIS FILE.
 * ---------------------------------------------------------------------------
 *
 * WHAT THIS FILE USED TO SAY, AND WHY.
 *
 * It said the plate builder must never show a number — no calories, no grams,
 * no weights, no macros, no score, no target, no percentage — and that
 * feedback was about PROPORTION only. The argument was clinical, not
 * stylistic:
 *
 *   - Numeric feedback teaches people to measure food, and measuring food is
 *     the habit this clinic exists to undo. The service sold three sections up
 *     this page is a plan built on someone's own history and bloodwork rather
 *     than a number to hit; a calorie readout would contradict the offer.
 *   - For anyone with a disordered relationship to eating, a number attached
 *     to a food is not neutral information. It is the mechanism of the
 *     disorder, handed out on a public page before she has spoken to anybody.
 *
 * And it named its own failure mode exactly: "somebody adds a rough calorie
 * estimate, just as a guide, in eight months, in good faith, and nothing else
 * in the codebase objects. This objects."
 *
 * WHAT HAPPENED.
 *
 * On 2026-10-12 the practitioner asked for an energy estimate, having been
 * shown this argument. That is not the eight-months-later erosion the old note
 * predicted — it is the one person entitled to overrule it doing so
 * deliberately, and the conditions she attached are the reason the file is
 * narrowed rather than deleted.
 *
 * NONE OF THE REASONING ABOVE HAS BEEN RETRACTED. It is still the reason the
 * number is a span rather than a figure, secondary rather than a headline,
 * caveated permanently rather than on hover, and absent entirely until she has
 * signed off the value. If you are reading this because you want to make the
 * number more prominent, or add a daily total, or colour it — the answer is
 * still no, and you need her, not this file.
 *
 * WHAT IS NOW PERMITTED, AND NOWHERE ELSE.
 *
 * Digits in exactly two scoped places in the rendered section, both marked in
 * the markup so this test can find them:
 *
 *   data-plate-estimate   the energy span
 *   data-plate-quantity   how many portions of a food are on the plate
 *
 * Portions are in that list because B of the change required them and a
 * portion count is a number by nature — "two spoons" cannot be expressed
 * qualitatively. The scope is these two attributes and it is not to grow.
 *
 * WHAT REMAINS ABSOLUTE, EVERYWHERE, IN BOTH LOCALES AND BOTH SCRIPTS.
 *
 *   - No digit in any plate translation string. Still zero, in both files.
 *     The estimate's copy uses :min and :max placeholders, so this costs
 *     nothing and is worth keeping as a tripwire.
 *   - No digit in the qualitative FEEDBACK, in the files or in the payload
 *     handed to the browser. The sentences that judge the plate stay wordy.
 *   - No percentage. No progress bar, meter or dial. No target, goal, limit,
 *     remaining count or daily total. No streak, no history, no pass/fail
 *     colour. Not in the plate, not in the hero, not anywhere.
 *   - Nothing is stored. No account, no session record, no tracking.
 *
 * WHAT THE SCHEMA ENFORCES, so that the UI is not the only thing standing
 * between a patient and a bad number:
 *
 *   - There is no single-value `energy` column and must not be one. Energy is
 *     a span; PlateFood refuses to save min === max.
 *   - An energy value without a named source cannot be saved.
 *   - needs_review defaults to true, and a food awaiting sign-off shows no
 *     estimate while still counting towards the proportion feedback.
 *
 * Checked in four places, because a number could arrive from any of them: the
 * translation files, the rendered page, the payload sent to the browser, and
 * the schema.
 *
 * ---------------------------------------------------------------------------
 *
 * The same rule is enforced at the bottom of this file for the HERO CASE CARD,
 * which is a different feature but the identical mistake. That card used to end
 * on "86%" over a progress bar; a percentage attached to a patient's own
 * behaviour is a grade she can fail at, and it sat three screens above a plate
 * builder that refuses to show her a calorie. One rule, two enforcement sites,
 * kept in one file so that neither can be relaxed without reading the argument
 * for the other.
 */
beforeEach(function () {
    $this->seed(PlateFoodSeeder::class);
});

/**
 * Digits in any script the site renders.
 *
 * Latin 0-9 and Arabic-Indic ٠-٩, because "٣٠٠ سعرة" is exactly as much a
 * number as "300 kcal" and a check that only knew about ASCII would wave it
 * straight through on the Arabic page.
 */
function containsDigit(string $value): bool
{
    return preg_match('/[0-9\x{0660}-\x{0669}\x{06F0}-\x{06F9}]/u', $value) === 1;
}

/*
|------------------------------------------------------------------------------
| The translation files
|------------------------------------------------------------------------------
*/

it('has no digit in any plate feedback string, in any locale', function (string $locale) {
    $feedback = __('plate.feedback', [], $locale);

    expect($feedback)->toBeArray();
    expect($feedback)->not->toBeEmpty();

    foreach ($feedback as $key => $string) {
        expect(containsDigit($string))->toBeFalse(
            "plate.feedback.{$key} in {$locale} contains a digit:\n\n  {$string}\n\n"
            .'Feedback is about proportion, never quantity. See the file header.'
        );
    }
})->with(['ar', 'en']);

it('has no digit anywhere in the plate translation files', function (string $locale) {
    /*
     * Wider than the feedback block on purpose. A calorie figure smuggled into
     * the lead, a group label or the disclaimer reaches the patient exactly as
     * a number in the feedback would.
     */
    $strings = __('plate', [], $locale);

    $flat = [];

    array_walk_recursive($strings, function ($value, $key) use (&$flat): void {
        if (is_string($value)) {
            $flat[$key] = $value;
        }
    });

    expect($flat)->not->toBeEmpty();

    foreach ($flat as $key => $string) {
        expect(containsDigit($string))->toBeFalse(
            "plate.{$key} in {$locale} contains a digit:\n\n  {$string}"
        );
    }
})->with(['ar', 'en']);

/*
|------------------------------------------------------------------------------
| The rendered page
|------------------------------------------------------------------------------
*/

it('renders the plate section without a number in any of its own copy', function (string $locale) {
    $html = $this->get("/{$locale}")->assertOk()->getContent();

    // Isolate the section: the rest of the page legitimately has prices,
    // durations and dates on it, and this rule is about the plate.
    preg_match('/<section id="plate".*?<\/section>/su', $html, $match);

    expect($match)->not->toBeEmpty('The plate section did not render.');

    $section = $match[0];

    /*
     * Strip the machinery before reading the words. Class names carry sizes
     * (p-6, size-3), inline colours carry hex digits, and the JSON payload
     * carries them too — none of which a patient ever sees. What is left is
     * the visible text.
     */
    $text = strip_tags($section);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    expect(containsDigit($text))->toBeFalse(
        "The rendered plate section shows a digit:\n\n".trim(preg_replace('/\s+/u', ' ', $text))
    );
})->with(['ar', 'en']);

it('has no digit in the feedback payload handed to the browser', function (string $locale) {
    /*
     * The gap the rendered-page check cannot cover on its own.
     *
     * Only ONE feedback string is server-rendered — the empty state. Every
     * other sentence reaches the patient through the JSON payload on the
     * element, swapped in by the script as she taps. A digit in "mostly
     * starch" would therefore sail past a test that only reads the initial
     * HTML, and would appear on screen the moment she used the feature.
     *
     * So the payload itself is decoded and every string in it checked. This is
     * the assertion that actually covers what she will see.
     */
    $html = $this->get("/{$locale}")->assertOk()->getContent();

    preg_match('/data-plate-feedback="([^"]*)"/', $html, $match);

    expect($match)->not->toBeEmpty('The plate feedback payload did not render.');

    $payload = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);

    expect($payload)->toBeArray();
    expect($payload)->not->toBeEmpty();

    foreach ($payload as $key => $string) {
        expect(containsDigit((string) $string))->toBeFalse(
            "The feedback payload sent to the browser has a digit in «{$key}»:

  {$string}"
        );
    }
})->with(['ar', 'en']);

/*
|------------------------------------------------------------------------------
| The schema
|------------------------------------------------------------------------------
*/

it('has no single-figure energy column, only a span', function () {
    /*
     * THE STRUCTURAL HALF, which is the half that survived intact.
     *
     * The old version of this test listed every column name a quantity could
     * hide behind and asserted none existed — on the reasoning that a feedback
     * string cannot show a calorie count that does not exist. That reasoning
     * was right, and it is why the replacement is not "anything goes now":
     * the columns that exist are enumerated, and the ones that would permit
     * false precision are still banned by name.
     *
     * `energy` singular is the important one. A single integer per food is the
     * shape a patient must never see, and a single integer in the database is
     * a single integer something will eventually render.
     */
    $columns = Schema::getColumnListing('plate_foods');

    $forbidden = [
        // A single figure, under any of the names it would plausibly take.
        'energy', 'calories', 'kcal', 'cal', 'energy_kcal',
        // Weighing food is the habit this clinic exists to undo. The unit is
        // a household measure; grams are not an option here.
        'grams', 'weight', 'weight_grams', 'serving_grams',
        // A target is forbidden outright, so it gets nowhere to live either.
        'target', 'goal', 'daily_target', 'limit',
    ];

    foreach ($forbidden as $column) {
        expect(in_array($column, $columns, true))->toBeFalse(
            "plate_foods has a `{$column}` column. Energy is a SPAN with a named source, and "
            .'there are no targets in this feature. See the file header.'
        );
    }

    // And the span itself is present in both halves, or the feature has been
    // half-reverted into exactly the shape this test forbids.
    foreach (['energy_min', 'energy_max', 'source', 'needs_review'] as $column) {
        expect(in_array($column, $columns, true))->toBeTrue(
            "plate_foods is missing `{$column}`, which is what makes the estimate honest."
        );
    }
});

it('refuses to store a single figure, an unsourced figure, or half a span', function () {
    /*
     * The guard that replaces the absent column. Asserted by trying to save
     * each bad shape, because a rule enforced only by the seeder is a rule
     * the admin panel and the next tinker session do not have to obey.
     */
    $base = [
        'name' => ['ar' => 'اختبار', 'en' => 'Probe'],
        'group' => FoodGroup::Protein,
        'emoji' => '🧪',
        'unit' => PortionUnit::Spoon,
        'default_portion' => 1,
        'source' => 'USDA FoodData Central',
    ];

    // A single figure dressed as a span.
    expect(fn () => PlateFood::create([...$base, 'energy_min' => 200, 'energy_max' => 200]))
        ->toThrow(LogicException::class);

    // Inverted, which would render as a backwards span.
    expect(fn () => PlateFood::create([...$base, 'energy_min' => 300, 'energy_max' => 100]))
        ->toThrow(LogicException::class);

    // Half a span.
    expect(fn () => PlateFood::create([...$base, 'energy_min' => 200, 'energy_max' => null]))
        ->toThrow(LogicException::class);

    // A figure nobody can trace. Same rule the article citations are held to.
    expect(fn () => PlateFood::create([...$base, 'energy_min' => 100, 'energy_max' => 200, 'source' => null]))
        ->toThrow(LogicException::class);

    // No energy at all is a legitimate state: the food still appears on the
    // plate and still counts towards the proportion feedback.
    $none = PlateFood::create([...$base, 'name' => ['ar' => 'بدون تقدير', 'en' => 'No estimate']]);
    expect($none->hasPublishableEstimate())->toBeFalse();
});

it('exposes no numeric attribute beyond the enumerated nutrition ones', function () {
    /*
     * Narrowed rather than dropped. The original asserted that NO attribute on
     * the model held a number; the permitted set is now listed explicitly, so
     * a thirtieth numeric column added later still fails this.
     */
    $food = PlateFood::query()->firstOrFail();

    $permitted = [
        // Plumbing, never rendered.
        'id', 'sort_order', 'is_active', 'created_at', 'updated_at',
        // The estimate, as approved on 2026-10-12. Each of these is here
        // because a human decided it, not because it was convenient.
        'default_portion', 'energy_min', 'energy_max', 'macros',
        'needs_review', 'reviewed_at', 'reviewed_by',
    ];

    foreach ($food->getAttributes() as $key => $value) {
        if (in_array($key, $permitted, true)) {
            continue;
        }

        expect(is_numeric($value))->toBeFalse(
            "PlateFood::{$key} holds a number ({$value}) and is not in the permitted set. "
            .'Adding a number to this model is a practitioner decision, not a refactor.'
        );
    }
});

it('shows no estimate for a food the practitioner has not signed off', function (string $locale) {
    /*
     * The condition attached to the whole change, asserted against the
     * rendered page rather than trusted.
     *
     * On a fresh seed every food is pending, so the energy attributes must be
     * absent from the markup entirely — not merely unused by the script. An
     * unverified figure that reaches the browser is one script change away
     * from reaching a patient.
     */
    PlateFood::query()->update(['needs_review' => true]);

    $html = $this->get("/{$locale}")->assertOk()->getContent();

    expect(str_contains($html, 'data-plate-energy-min'))->toBeFalse(
        'An unreviewed energy value was sent to the browser. See the class note on PlateFood.'
    );

    // And once she signs one off, it does appear — otherwise this test would
    // also pass on a feature that was simply broken.
    $food = PlateFood::query()->firstOrFail();
    $food->forceFill(['needs_review' => false])->save();

    expect(str_contains($this->get("/{$locale}")->getContent(), 'data-plate-energy-min'))->toBeTrue(
        'A signed-off food still shows no estimate, so the sign-off does nothing.'
    );
})->with(['ar', 'en']);

/*
|------------------------------------------------------------------------------
| What stayed absolute
|------------------------------------------------------------------------------
|
| The estimate was permitted. None of this was.
*/

/** The plate section as rendered, payloads and all. */
function plateSection(string $locale): string
{
    $html = test()->get("/{$locale}")->assertOk()->getContent();

    preg_match('/<section id="plate".*?<\/section>/su', $html, $match);

    expect($match)->not->toBeEmpty('The plate section did not render.');

    return $match[0];
}

it('shows no percentage anywhere in the plate', function (string $locale) {
    /*
     * Latin % and Arabic ٪ alike. A percentage is the specific shape the whole
     * rule is about: not a quantity of food, but a proportion of herself that
     * she can be short of. The estimate being permitted changes nothing here —
     * a span of calories is information, "you are at 80%" is a grade.
     */
    $section = plateSection($locale);

    foreach (['%', "\u{066A}"] as $sign) {
        expect(str_contains($section, $sign))->toBeFalse(
            "The plate shows a percentage in {$locale}. See the file header: still absolute."
        );
    }
})->with(['ar', 'en']);

it('has no progress bar, meter or dial in the plate', function (string $locale) {
    /*
     * The proportion bar is a set of widths with no track and no maximum — it
     * shows what the plate is made OF, not how full it is. The moment it gains
     * a value and a ceiling it becomes a score, which is why these attributes
     * are banned by name rather than left to judgement.
     */
    $section = plateSection($locale);

    foreach (['role="meter"', 'role="progressbar"', '<progress', 'aria-valuenow', 'aria-valuemax'] as $needle) {
        expect(str_contains($section, $needle))->toBeFalse(
            "The plate has a {$needle}. Proportion here is shown, never scored."
        );
    }
})->with(['ar', 'en']);

it('never phrases the estimate as a target, a total or something remaining', function (string $locale) {
    /*
     * The wording half, which is the half a future change is most likely to
     * get wrong in good faith. "You have 400 left today" is the same feature
     * as a progress bar, written out in words.
     *
     * Checked across every plate string in both locales, not just the estimate
     * block, because the sentence could be put anywhere in the section.
     */
    $plate = __('plate', [], $locale);

    /*
     * ONE KEY IS EXEMPT, AND ONLY ONE: estimate.caveat.
     *
     * Its entire job is to say "this is not a target", which cannot be done
     * without using the word. The permanent caveat was the condition attached
     * to showing a number at all, so the string that carries it has to be
     * allowed to name the thing it is disavowing.
     *
     * THIS IS IN TENSION WITH A DECISION MADE EARLIER IN THIS FILE'S HISTORY
     * and the tension is deliberate, not an oversight. plate.lead refuses to
     * use the word for calories even to disavow it, on the reasoning that
     * naming it — however negatively — still puts it in front of somebody who
     * came here trying not to think about it. That reasoning still holds for
     * the lead, which is read before anything happens. It does not hold here:
     * the caveat sits directly beneath a figure she has already chosen to
     * generate, and leaving the figure uncaveated to protect her from the word
     * "target" would be the worse trade.
     *
     * The exemption is by KEY, not by content, so a target phrasing cannot be
     * smuggled into any other string — including the feedback, including the
     * estimate's own value line.
     */
    $exempt = ['caveat'];

    $forbidden = $locale === 'ar'
        ? ['هدف', 'الهدف', 'المتبقي', 'باقي لك', 'إجمالي اليوم', 'تجاوزت', 'حد أقصى', 'نسبة']
        : ['target', 'goal', 'remaining', 'left today', 'daily total', 'exceeded', 'allowance', 'limit', 'percent'];

    $walk = function (array $node, string $path) use (&$walk, $forbidden, $exempt, $locale): void {
        foreach ($node as $key => $value) {
            $here = $path === '' ? (string) $key : $path.'.'.$key;

            if (is_array($value)) {
                $walk($value, $here);

                continue;
            }

            if (! is_string($value) || in_array((string) $key, $exempt, true)) {
                continue;
            }

            foreach ($forbidden as $term) {
                expect(mb_stripos($value, $term))->toBeFalse(
                    "plate.{$here} in {$locale} uses «{$term}», which turns the estimate into "
                    ."something she can fall short of:\n\n  {$value}"
                );
            }
        }
    };

    $walk($plate, '');

    // And the exemption is not a hole: the caveat must actually be doing the
    // disavowing it is exempt for.
    $caveat = __('plate.estimate.caveat', [], $locale);
    $disavowal = $locale === 'ar' ? 'مش هدف' : 'not a target';

    expect(mb_stripos($caveat, $disavowal))->not->toBeFalse(
        "plate.estimate.caveat in {$locale} is exempt from the target-wording ban so that it can "
        ."say the estimate is not a target. It no longer says it:\n\n  {$caveat}"
    );
})->with(['ar', 'en']);

it('permits digits only inside the two scoped quantity elements', function (string $locale) {
    /*
     * The scope, asserted rather than described.
     *
     * Nothing digit-bearing is server-rendered today — the initial state has
     * no portions and no estimate — so this currently proves the section is
     * clean. It is written as a strip-then-check so it keeps meaning if any
     * part of the estimate is ever rendered server-side.
     */
    $section = plateSection($locale);

    // Remove the two permitted regions entirely, then read what is left.
    $rest = preg_replace(
        [
            '/<div[^>]*data-plate-estimate\b.*?<\/div>/su',
            '/<[^>]*data-plate-quantity\b[^>]*>.*?<\/[a-z]+>/su',
        ],
        '',
        $section,
    );

    $text = html_entity_decode(strip_tags((string) $rest), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    expect(containsDigit($text))->toBeFalse(
        "A digit appears in the plate outside the scoped estimate and quantity elements:\n\n"
        .trim((string) preg_replace('/\s+/u', ' ', $text))
    );
})->with(['ar', 'en']);

it('lets both plate columns shrink below their content', function (string $locale) {
    /*
     * min-w-0 on each grid item, asserted because the failure it prevents is
     * invisible in a static test and only appears once a patient types.
     *
     * A grid item defaults to min-width:auto, so it refuses to shrink below
     * its content's min-content width. The custom-food panel lets her enter a
     * name of up to forty characters; at 320 a long one pushed the whole grid
     * 87px wider than the viewport and took the food list with it. The name
     * itself was already truncating — it was the COLUMN that would not give.
     *
     * ResponsiveSweepTest cannot catch this: it reads HTML and this is a
     * layout property that only exists once something is rendered at a width.
     */
    $section = plateSection($locale);

    preg_match_all('/<div class="order-[12][^"]*"/', $section, $columns);

    expect($columns[0])->toHaveCount(2, 'The plate no longer has its two ordered columns.');

    foreach ($columns[0] as $column) {
        expect(str_contains($column, 'min-w-0'))->toBeTrue(
            "A plate grid column is missing min-w-0, so it cannot shrink below its content:\n  {$column}"
        );
    }
})->with(['ar', 'en']);

it('stores nothing and sends nothing anywhere', function () {
    /*
     * "Nothing is stored" is a promise the copy makes to the patient, so it is
     * asserted against the script rather than trusted. No account, no history,
     * no tracking across visits — and the food she adds herself does not even
     * reach the server.
     */
    /*
     * Comments stripped first. The file documents this very rule — "no
     * localStorage, no sessionStorage, no cookie" — and an earlier version of
     * this assertion failed on that sentence, which is funny once and a
     * false alarm forever after. What matters is the code.
     */
    $js = (string) preg_replace(['#/\*.*?\*/#s', '#//[^\n]*#'], '', File::get(resource_path('js/plate.js')));

    foreach (['fetch(', 'XMLHttpRequest', 'sendBeacon', 'localStorage', 'sessionStorage', 'document.cookie', 'indexedDB'] as $needle) {
        expect(str_contains($js, $needle))->toBeFalse(
            "plate.js uses {$needle}. This feature keeps nothing and reports nothing."
        );
    }

    // The custom-food markup is a form in appearance only: no action, no
    // method, nothing to submit.
    $section = plateSection('ar');

    expect(preg_match('/<form\b/i', $section))->toBe(0, 'The plate section has a <form>. Nothing here is submitted.');
});

/*
|------------------------------------------------------------------------------
| The feature still works
|------------------------------------------------------------------------------
*/

it('offers real egyptian food across every group', function () {
    /*
     * The other half of being useful. A plate builder that offered quinoa and
     * kale to somebody who eats foul and baladi bread teaches her that her food
     * is not what the chart means — the opposite of the point.
     */
    $foods = PlateFood::query()->active()->get();

    expect($foods)->not->toBeEmpty();

    foreach (FoodGroup::cases() as $group) {
        expect($foods->where('group', $group)->count())
            ->toBeGreaterThan(0, "No foods in the {$group->value} group.");
    }

    foreach (['فول مدمس', 'عيش بلدي', 'ملوخية', 'طعمية', 'رز أبيض', 'جبنة قريش', 'طرشي'] as $expected) {
        expect($foods->contains(fn (PlateFood $food): bool => $food->getTranslation('name', 'ar') === $expected))
            ->toBeTrue("Expected «{$expected}» on the plate.");
    }
});

/*
|------------------------------------------------------------------------------
| The hero case card
|------------------------------------------------------------------------------
|
| Same rule, different feature. See the note in the file header.
*/

/**
 * The hero AND the case card, as rendered.
 *
 * They were one section until 8.16 and this file only had to look at the hero.
 * The card is now its own section immediately below, and the rule follows the
 * CARD rather than the position — a percentage is no more acceptable eighty
 * pixels further down the page. Both are returned together so no future move
 * can carry the card out from under these assertions again.
 */
function heroSection(string $locale): string
{
    $html = test()->get("/{$locale}")->assertOk()->getContent();

    preg_match('/<section[^>]*data-hero\b.*?<\/section>/su', $html, $hero);
    preg_match('/<section[^>]*case-card-heading.*?<\/section>/su', $html, $card);

    expect($hero)->not->toBeEmpty('The hero section did not render.');
    expect($card)->not->toBeEmpty('The case card section did not render.');

    return $hero[0].$card[0];
}

it('shows no percentage anywhere in the hero', function (string $locale) {
    /*
     * Latin % and Arabic ٪ (U+066A) alike. A percentage is the specific shape
     * this rule is about: not a quantity of food, but a proportion of herself.
     */
    $text = html_entity_decode(strip_tags(heroSection($locale)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    foreach (['%', "\u{066A}"] as $sign) {
        expect(str_contains($text, $sign))->toBeFalse(
            "The hero shows a percentage in {$locale}:\n\n"
            .trim(preg_replace('/\s+/u', ' ', $text))
        );
    }
})->with(['ar', 'en']);

it('has no meter or progress bar in the hero', function (string $locale) {
    /*
     * The bar mattered as much as the number did. A filled track next to a
     * figure is the visual grammar of a score out of a target — it is what made
     * the card read as an app dashboard rather than as a clinic — and removing
     * the digits while leaving the bar would keep the meaning and lose only the
     * evidence.
     */
    $hero = heroSection($locale);

    foreach (['role="meter"', 'role="progressbar"', '<progress', 'aria-valuenow'] as $needle) {
        expect(str_contains($hero, $needle))->toBeFalse(
            "The hero case card has a {$needle} again. Progress here is described, not scored."
        );
    }
})->with(['ar', 'en']);

it('states plan adherence in words rather than as a figure', function (string $locale) {
    /*
     * The row that carried the 86%. It is now an ordinary row saying an
     * ordinary thing, in the same register as the rows around it.
     *
     * WHY THIS ROW AND NOT EVERY ROW. "Week 8" and "6 nights out of 7" still
     * contain digits and are deliberately left alone: a week number is a date,
     * and nights slept is a count of things that happened. Neither is a mark
     * out of a target. "86% adherence" was a grade — the difference is whether
     * a patient can be short of it.
     */
    $value = __('home.hero.case_card.metrics.adherence.value', [], $locale);

    expect($value)->toBeString();
    expect($value)->not->toBeEmpty();

    expect(containsDigit($value))->toBeFalse(
        "Plan adherence is a figure again in {$locale}: «{$value}». "
        .'It is the one row on this card a patient can read as a mark out of a target.'
    );

    // And it actually reaches the page.
    expect(heroSection($locale))->toContain($value);
})->with(['ar', 'en']);
