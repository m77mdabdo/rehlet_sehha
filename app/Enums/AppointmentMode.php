<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Every mode an appointment has EVER been able to have.
 *
 * This is deliberately not the same set as the modes a patient may choose
 * today — that list lives in config('clinic.booking.modes').
 *
 * Clinic is kept here even while in-person visits are switched off. Deleting
 * the case would mean a migration and a data backfill to reintroduce it, and
 * in the meantime any historical row carrying 'clinic' would throw on cast and
 * break every page that renders it. An enum describes what the column can
 * contain; config describes what the form may offer. Conflating the two turns
 * a policy change into a schema change.
 */
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
     * Whether this mode can be chosen for a NEW booking.
     *
     * Read from config on every call rather than cached in a static: the clinic
     * turning in-person visits back on should take effect on the next request,
     * not the next deploy.
     */
    public function isBookable(): bool
    {
        return in_array($this->value, self::bookableValues(), true);
    }

    /**
     * The modes a patient may currently choose.
     *
     * @return list<self>
     */
    public static function bookable(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $case): bool => $case->isBookable(),
        ));
    }

    /**
     * @return list<string>
     */
    public static function bookableValues(): array
    {
        /** @var list<string> $modes */
        $modes = config('clinic.booking.modes', ['online']);

        return $modes;
    }

    /**
     * Every mode, for rendering. Includes modes that are no longer bookable,
     * because an appointment booked last year still has to display.
     *
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

    /**
     * Only the modes that may be offered in a form today.
     *
     * @return array<string, string> value => Arabic label
     */
    public static function bookableOptions(): array
    {
        return array_reduce(
            self::bookable(),
            fn (array $carry, self $case): array => $carry + [$case->value => $case->label()],
            [],
        );
    }
}
