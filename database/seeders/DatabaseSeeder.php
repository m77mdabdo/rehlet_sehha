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
     *     only do that once a schedule is on record;
     *   - categories and tags must exist before PostSeeder, or every article
     *     is written with a null category_id and PostSeeder will not revisit
     *     it afterwards: it refuses to re-seed an article once it is published.
     * Everything after that is independent content.
     *
     * EVERY SEEDER IN THIS DIRECTORY MUST BE LISTED HERE. Three were not —
     * CategorySeeder, TagSeeder and PlateFoodSeeder — and nothing anywhere
     * said so. The consequence only appeared on production, a month after the
     * deploy: `plate_foods` was empty, so the homepage's plate builder
     * rendered with no food chips at all, and the fourteen articles were left
     * uncategorised and untagged. A seeder that exists but is never called
     * looks exactly like a seeder that works.
     *
     * DatabaseSeederCoversEverySeederTest now fails if a new seeder is added
     * and not wired in here, so the next one cannot go unnoticed.
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

            // Before PostSeeder — see the note above.
            CategorySeeder::class,
            TagSeeder::class,

            PostSeeder::class,

            // The homepage plate builder. Without this the section renders
            // its frame and no food.
            PlateFoodSeeder::class,

            DemoAppointmentSeeder::class,
        ]);
    }
}
