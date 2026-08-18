<?php

declare(strict_types=1);

namespace App\Enums;

enum AppointmentMode: string
{
    case Online = 'online';
    case Clinic = 'clinic';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'استشارة عن بُعد',
            self::Clinic => 'في العيادة',
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
