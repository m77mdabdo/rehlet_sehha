<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Order matters:
     *   - the APP_KEY fingerprint is recorded first, before any encrypted row
     *     exists, so clinic:verify-key has a baseline from the very first seed;
     *   - roles must exist before any user can be given one;
     *   - the doctor must exist before working hours, whose staff_id is NOT NULL;
     *   - working hours must exist before services, because Service's saving
     *     guard checks each duration against the shortest active slot and can
     *     only do that once a schedule is on record.
     * Everything after that is independent content.
     */
    public function run(): void
    {
        $this->call([
            AppKeyFingerprintSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
            DoctorUserSeeder::class,
            WorkingHoursSeeder::class,
            ServiceSeeder::class,
            SpecialtySeeder::class,
            ServiceSpecialtySeeder::class,
            FaqSeeder::class,
            TestimonialSeeder::class,
            VideoSeeder::class,
            PostSeeder::class,
            DemoAppointmentSeeder::class,
        ]);
    }
}
