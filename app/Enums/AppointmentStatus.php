<?php

declare(strict_types=1);

namespace App\Enums;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'بانتظار التأكيد',
            self::Confirmed => 'مؤكد',
            self::Completed => 'مكتمل',
            self::Cancelled => 'ملغي',
            self::NoShow => 'لم يحضر',
        };
    }

    /**
     * Whether an appointment in this status still counts as live — i.e. it is
     * neither cancelled nor a recorded no-show.
     */
    public function isActive(): bool
    {
        return ! in_array($this, [self::Cancelled, self::NoShow], true);
    }

    /**
     * Whether an appointment in this status should give its time slot back to
     * the booking calendar.
     *
     * Only cancellation releases the slot. A no-show is deliberately excluded:
     * it is a record of something that already happened at that time, and
     * freeing a past slot would let a second appointment be written into an
     * hour the clinic has already accounted for. See Appointment::syncSlotKey().
     */
    public function releasesSlot(): bool
    {
        return $this === self::Cancelled;
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
