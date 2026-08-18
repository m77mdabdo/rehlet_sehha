<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationChannel: string
{
    case Mail = 'mail';
    case Whatsapp = 'whatsapp';
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::Mail => 'البريد الإلكتروني',
            self::Whatsapp => 'واتساب',
            self::Sms => 'رسالة نصية',
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
