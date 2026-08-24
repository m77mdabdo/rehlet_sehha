<?php

declare(strict_types=1);

use App\Enums\FoodGroup;
use App\Models\PlateFood;
use Database\Seeders\PlateFoodSeeder;
use Illuminate\Support\Facades\Schema;

/**
 * THE PLATE BUILDER MUST NEVER SHOW A NUMBER. THIS TEST IS NOT TO BE RELAXED.
 *
 * No calories, no grams, no weights, no macros, no score, no target, no
 * percentage. Feedback is about PROPORTION only — what the plate is mostly made
 * of, and what it is missing.
 *
 * The reason is clinical, not stylistic. Numeric feedback teaches people to
 * measure food, and measuring food is the habit this clinic exists to undo; the
 * whole service being sold on this page is a plan built on someone's own
 * history and bloodwork rather than a number to hit. A calorie readout in the
 * homepage would contradict the offer three sections above it.
 *
 * It is also actively harmful to a specific group of visitors. For anyone with
 * a disordered relationship to eating, a number attached to a food is not
 * neutral information — it is the mechanism of the disorder, and a clinic
 * handing it out on a public page has done harm before the patient has spoken
 * to anyone. A tool on a nutrition clinic's homepage must not be a calorie
 * counter in disguise.
 *
 * This is the decay the test exists to prevent: somebody adds "a rough calorie
 * estimate, just as a guide" in eight months, in good faith, and nothing else
 * in the codebase objects. This objects.
 *
 * Checked in three places, because the string could arrive from any of them:
 * the translation files, the rendered page, and the schema itself.
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

it('has no quantity column on the plate_foods table', function () {
    /*
     * The structural half of the rule. A feedback string cannot show a calorie
     * count that does not exist, so the cheapest way to keep this feature
     * honest is for the number to have nowhere to live.
     */
    $columns = Schema::getColumnListing('plate_foods');

    $forbidden = [
        'calories', 'kcal', 'energy',
        'grams', 'weight', 'portion', 'portion_size', 'serving', 'serving_size',
        'protein_grams', 'carbs', 'carbohydrates', 'fat_grams', 'macros',
    ];

    foreach ($forbidden as $column) {
        expect($columns)->not->toContain(
            $column,
            "plate_foods has a `{$column}` column. This feature does not have quantities."
        );
    }
});

it('exposes no numeric attribute on the food model', function () {
    $food = PlateFood::query()->firstOrFail();

    foreach ($food->getAttributes() as $key => $value) {
        // id, sort_order and the timestamps are plumbing: they are never
        // rendered, and the rendered-page test above is what proves it.
        if (in_array($key, ['id', 'sort_order', 'is_active', 'created_at', 'updated_at'], true)) {
            continue;
        }

        expect(is_numeric($value))->toBeFalse(
            "PlateFood::{$key} holds a number ({$value}). Foods carry a name, a group and an emoji."
        );
    }
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
 * The hero section as rendered, so these assertions describe what a patient
 * actually sees rather than what a translation file happens to contain.
 */
function heroSection(string $locale): string
{
    $html = test()->get("/{$locale}")->assertOk()->getContent();

    preg_match('/<section[^>]*data-hero\b.*?<\/section>/su', $html, $match);

    expect($match)->not->toBeEmpty('The hero section did not render.');

    return $match[0];
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
