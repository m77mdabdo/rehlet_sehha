<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * The four numbers the clinic opens the panel to see.
 *
 * NO REVENUE WIDGET, deliberately. The clinic has not asked for one, and a
 * money total at the top of a medical screen changes what the screen is for:
 * it turns the morning glance from "who is coming and who needs a call" into
 * "how are we doing", and the two lead to different decisions about the same
 * patient. If the clinic later wants takings, that belongs in a report someone
 * chooses to open, not on the first thing the doctor sees.
 */
class ClinicOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'النهاردة';

    protected static ?int $sort = 1;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $zone = config('clinic.timezone');
        $today = now($zone)->startOfDay();

        $todayCount = Appointment::query()
            ->countsTowardWorkload()
            ->whereBetween('starts_at', [$today->clone()->utc(), $today->clone()->endOfDay()->utc()])
            ->count();

        $weekCount = Appointment::query()
            ->countsTowardWorkload()
            ->whereBetween('starts_at', [
                $today->clone()->startOfWeek()->utc(),
                $today->clone()->endOfWeek()->utc(),
            ])
            ->count();

        /*
         * Patients who cannot be reached electronically, in the days ahead.
         *
         * The number that turns into a task: each of these received no
         * confirmation and will get no reminder, so somebody has to telephone
         * them. Filtered in PHP because reachability is derived from the
         * patient record rather than stored — see App\Enums\ContactPreference.
         */
        $unreachable = Appointment::query()
            ->with('patient')
            ->countsTowardWorkload()
            ->whereBetween('starts_at', [
                now()->utc(),
                $today->clone()->addDays(7)->endOfDay()->utc(),
            ])
            ->get()
            ->reject(fn (Appointment $appointment): bool => $appointment->isReachableByEmail())
            ->count();

        $cancellations = Appointment::query()
            ->where('status', AppointmentStatus::Cancelled)
            ->where('cancelled_at', '>=', now()->subDays(7))
            ->count();

        return [
            Stat::make('حجوزات النهاردة', (string) $todayCount)
                ->description($today->translatedFormat('l j F'))
                ->color('primary'),

            Stat::make('حجوزات الأسبوع', (string) $weekCount)
                ->description('من السبت للجمعة')
                ->color('gray'),

            Stat::make('محتاجين مكالمة', (string) $unreachable)
                ->description('مرضى من غير إيميل، في الأسبوع الجاي')
                ->color($unreachable > 0 ? 'warning' : 'gray'),

            Stat::make('إلغاءات آخر أسبوع', (string) $cancellations)
                ->color($cancellations > 0 ? 'danger' : 'gray'),
        ];
    }
}
