<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DoctorUserSeeder extends Seeder
{
    /**
     * The clinic's practitioner.
     *
     * She is real staff rather than demo data: working_hours.staff_id is now
     * NOT NULL, so a schedule cannot exist without a practitioner to own it,
     * and the schedule is production configuration. Same password handling as
     * the administrator — read from the environment, otherwise generated and
     * printed once, never a shared default baked into the repository.
     */
    public function run(): void
    {
        $email = (string) env('DOCTOR_EMAIL', 'doctor@rehletsehha.test');
        $name = (string) env('DOCTOR_NAME', 'د. رنا سالم');

        $configured = env('DOCTOR_PASSWORD');
        $password = is_string($configured) && $configured !== ''
            ? $configured
            : Str::password(20);

        $doctor = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password],
        );

        $doctor->syncRoles(['doctor']);

        if (! $doctor->wasRecentlyCreated) {
            $this->command?->line("Doctor user {$email} already exists; password left unchanged.");

            return;
        }

        $this->command?->newLine();
        $this->command?->info('Doctor user created:');
        $this->command?->line("  email:    {$email}");

        if (is_string($configured) && $configured !== '') {
            $this->command?->line('  password: (taken from DOCTOR_PASSWORD)');
        } else {
            $this->command?->line("  password: {$password}");
            $this->command?->warn('  Generated password — shown once. Store it now.');
        }

        $this->command?->newLine();
    }
}
