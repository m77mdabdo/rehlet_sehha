<?php

declare(strict_types=1);

namespace App\Enums;

use App\Models\PlateFood;

/**
 * The six groups a plate is built from.
 *
 * This is the entire vocabulary of the plate builder, and it is deliberately
 * about PROPORTION rather than quantity. A plate is "mostly starch" or
 * "missing vegetables"; it is never 640 of anything.
 *
 * @see PlateFood for why no number ever enters this feature.
 */
enum FoodGroup: string
{
    case Vegetable = 'vegetable';
    case Protein = 'protein';
    case Starch = 'starch';
    case Fat = 'fat';
    case Fruit = 'fruit';
    case Dairy = 'dairy';

    public function label(): string
    {
        return __('plate.groups.'.$this->value);
    }

    /**
     * The colour each group takes on the plate.
     *
     * Colour is never the only signal: every segment is also labelled, and the
     * feedback below the plate says the same thing in words. A patient with a
     * colour vision deficiency gets the whole message without seeing a single
     * hue correctly.
     */
    public function colour(): string
    {
        return match ($this) {
            self::Vegetable => '#4C7C59',
            self::Protein => '#1A6DA6',
            self::Starch => '#C98A2E',
            self::Fat => '#8A6BB1',
            self::Fruit => '#C05D7E',
            self::Dairy => '#5A8FA8',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case): string => $case->value, self::cases());
    }
}
