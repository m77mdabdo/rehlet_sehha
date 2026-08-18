<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Biological sex as recorded on the patient file.
 *
 * This is clinical data, not demographic decoration. Resting metabolic rate,
 * protein and iron requirements, and the reference ranges we read lab work
 * against all differ by sex, so a nutrition plan cannot be calculated without
 * it. It stays nullable because a patient may decline to state it and we would
 * rather hold an honest null than a guess that silently skews a calculation.
 */
enum Gender: string
{
    case Male = 'male';
    case Female = 'female';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'ذكر',
            self::Female => 'أنثى',
        };
    }

    /**
     * @return array<string, string> value => Arabic label
     */
    public static function options(): array
    {
        return array_reduce(
            self::cases(),
            fn (array $carry, self $case): array => $carry + [$case->value => $case->label()],
            [],
        );
    }
}
