<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\WorkingHour;
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
     */
    public function run(): void
    {
        $openDays = [
            6, // Saturday
            0, // Sunday
            1, // Monday
            2, // Tuesday
            3, // Wednesday
            4, // Thursday
        ];

        foreach ($openDays as $day) {
            WorkingHour::updateOrCreate(
                ['staff_id' => null, 'day_of_week' => $day],
                [
                    'start_time' => '10:00:00',
                    'end_time' => '20:00:00',
                    'slot_minutes' => 60,
                    'is_active' => true,
                ],
            );
        }
    }
}
