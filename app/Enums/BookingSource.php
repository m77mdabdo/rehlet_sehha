<?php

declare(strict_types=1);

namespace App\Enums;

enum BookingSource: string
{
    case Website = 'website';
    case Phone = 'phone';
    case WalkIn = 'walk_in';

    public function label(): string
    {
        return match ($this) {
            self::Website => 'الموقع الإلكتروني',
            self::Phone => 'الهاتف',
            self::WalkIn => 'زيارة مباشرة',
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
