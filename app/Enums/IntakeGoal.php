<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * What the patient says they came for.
 *
 * A closed list rather than free text: it is the one intake field the clinic
 * filters and counts on, and it is stored UNENCRYPTED so it can be. Everything
 * genuinely clinical — medications, conditions, foods to avoid, the free note —
 * is encrypted and never queried.
 *
 * Labels live in the translation files, not here, because unlike the internal
 * enums (status, source, mode) this one is read by patients on a bilingual
 * site. An Arabic-only label() would be untranslatable by construction.
 */
enum IntakeGoal: string
{
    case WeightManagement = 'weight_management';
    case MedicalCondition = 'medical_condition';
    case SportsPerformance = 'sports_performance';
    case Pregnancy = 'pregnancy';
    case ChildNutrition = 'child_nutrition';
    case LabReview = 'lab_review';
    case GeneralHealth = 'general_health';
    case Other = 'other';

    public function label(): string
    {
        return __('booking.goals.'.$this->value);
    }

    /**
     * @return array<string, string> value => translated label
     */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $case): array => $carry + [$case->value => $case->label()],
            [],
        );
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case): string => $case->value, self::cases());
    }
}
