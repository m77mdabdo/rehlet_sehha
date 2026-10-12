<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The household measures a portion is counted in.
 *
 * NOT GRAMS, AND NEVER GRAMS. A patient who has to weigh her food to use this
 * tool is being taught the exact habit the clinic exists to undo, and the
 * number that came out of the scale would be false precision anyway — these
 * are kitchen spoons and baladi loaves, not laboratory measures.
 *
 * "Two spoons of tahina" is something a patient can answer from memory about a
 * meal she ate yesterday. "28 grams of tahina" is something she can only
 * answer by starting to weigh her food. The whole point of the unit list is
 * that every entry is a thing already sitting in her kitchen.
 *
 * This is also why every energy figure in the feature is a range: a spoon is
 * not a calibrated volume, so anything derived from one is a span. See
 * App\Models\PlateFood.
 */
enum PortionUnit: string
{
    case Spoon = 'spoon';
    case Cup = 'cup';
    case Loaf = 'loaf';
    case Piece = 'piece';
    case Whole = 'whole';
    case SmallPlate = 'small_plate';

    public function label(): string
    {
        return __('plate.units.'.$this->value);
    }

    /**
     * The unit with a count in front of it — "٢ ملعقة", "2 spoons".
     *
     * Pluralisation is left to the translation files via trans_choice, because
     * Arabic does not pluralise the way English does and a helper that assumed
     * either would be wrong in one of the two locales. The count itself is
     * formatted by the caller.
     */
    public function labelFor(float $count): string
    {
        return trans_choice('plate.units_counted.'.$this->value, (int) ceil($count));
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case): string => $case->value, self::cases());
    }
}
