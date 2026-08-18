<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;

class WorkingHoursSeeder extends Seeder
{
    /**
     * The clinic works Saturday through Thursday, 10:00–20:00 Cairo time, in
     * 60-minute slots. Friday is the weekend and simply gets no row: the slot
     * generator reads absence of a row as "closed", so there is no need for an
     * is_active = false placeholder.
     *
     * day_of_week follows Carbon's convention, 0 = Sunday .. 6 = Saturday,
     * which makes Friday 5 — the one value missing from the list below.
     *
     * Hours are attached to each practitioner individually. staff_id is NOT
     * NULL by design: a clinic-wide default row could not say whether a second
     * doctor inherited those hours, overrode them, or was closed.
     */
    public function run(): void
    {
        // Doctors only, not User::bookable(). Administrators can act on the
        // calendar but do not themselves see patients, so giving them a
        // schedule would advertise bookable hours nobody intends to work.
        $practitioners = User::query()
            ->whereHas('roles', fn (Builder $roles) => $roles->where('name', 'doctor'))
            ->get();

        if ($practitioners->isEmpty()) {
            $this->command?->warn('WorkingHoursSeeder skipped: no doctor users. Run DoctorUserSeeder first.');

            return;
        }

        $openDays = [
            6, // Saturday
            0, // Sunday
            1, // Monday
            2, // Tuesday
            3, // Wednesday
            4, // Thursday
        ];

        foreach ($practitioners as $practitioner) {
            foreach ($openDays as $day) {
                WorkingHour::updateOrCreate(
                    [
                        'staff_id' => $practitioner->id,
                        'day_of_week' => $day,
                        'start_time' => '10:00:00',
                    ],
                    [
                        'end_time' => '20:00:00',
                        'slot_minutes' => 60,
                        'is_active' => true,
                    ],
                );
            }
        }

        $this->command?->info(sprintf(
            'Seeded working hours for %d doctor(s).',
            $practitioners->count(),
        ));
    }
}
