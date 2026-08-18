<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Order matters:
     *   roles must exist before the admin user can be given one;
     *   services and working hours must exist before demo appointments can
     *   reference a service and land on a real clinic hour.
     * Everything after that is independent content.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminUserSeeder::class,
            ServiceSeeder::class,
            WorkingHoursSeeder::class,
            FaqSeeder::class,
            TestimonialSeeder::class,
            VideoSeeder::class,
            PostSeeder::class,
            DemoAppointmentSeeder::class,
        ]);
    }
}
