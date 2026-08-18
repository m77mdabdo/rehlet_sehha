<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Enums\BookingSource;
use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoAppointmentSeeder extends Seeder
{
    /**
     * Fifteen appointments spread either side of today, with an intake form on
     * each, so the admin calendar and the patient history views have something
     * realistic to render.
     *
     * Local environment only. Demo patients carry invented phone numbers and
     * invented clinical answers; seeding them into a live database would put
     * fictional health records next to real ones.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('DemoAppointmentSeeder skipped: local environment only.');

            return;
        }

        $services = Service::query()->orderBy('sort_order')->get();

        if ($services->isEmpty()) {
            $this->command?->warn('DemoAppointmentSeeder skipped: run ServiceSeeder first.');

            return;
        }

        $doctor = User::query()->firstOrCreate(
            ['email' => 'doctor@rehletsehha.test'],
            ['name' => 'د. رنا سالم', 'password' => 'demo-password-not-for-production'],
        );

        if (! $doctor->hasRole('doctor')) {
            $doctor->assignRole('doctor');
        }

        $patients = Patient::factory()->count(10)->create();

        // Slots are laid out on the clinic's 60-minute grid, each one distinct,
        // because an active appointment's slot_key is unique per staff + start.
        $plan = [
            // [days offset, hour (Cairo), status]
            [-38, 11, AppointmentStatus::Completed],
            [-31, 12, AppointmentStatus::Completed],
            [-24, 17, AppointmentStatus::Completed],
            [-21, 10, AppointmentStatus::NoShow],
            [-17, 13, AppointmentStatus::Completed],
            [-12, 18, AppointmentStatus::Cancelled],
            [-9, 16, AppointmentStatus::Completed],
            [-4, 19, AppointmentStatus::Completed],
            [1, 10, AppointmentStatus::Confirmed],
            [2, 12, AppointmentStatus::Confirmed],
            [3, 15, AppointmentStatus::Pending],
            [5, 11, AppointmentStatus::Confirmed],
            [8, 17, AppointmentStatus::Pending],
            [12, 13, AppointmentStatus::Cancelled],
            [16, 18, AppointmentStatus::Pending],
        ];

        $clinicTimezone = (string) config('clinic.timezone');

        foreach ($plan as $index => [$dayOffset, $hour, $status]) {
            $service = $services[$index % $services->count()];
            $patient = $patients[$index % $patients->count()];

            // Built as a Cairo wall-clock time — that is how the clinic thinks
            // about its day — then converted to UTC for storage.
            $startsAt = Carbon::now($clinicTimezone)
                ->addDays($dayOffset)
                ->setTime($hour, 0)
                ->utc();

            $appointment = Appointment::create([
                'reference' => Appointment::generateReference(),
                'cancel_token' => Appointment::generateCancelToken(),
                'patient_id' => $patient->id,
                'service_id' => $service->id,
                'staff_id' => $doctor->id,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->clone()->addMinutes($service->duration_minutes),
                'mode' => $index % 3 === 0 ? AppointmentMode::Online : AppointmentMode::Clinic,
                'status' => $status,
                'price' => $service->price,
                'currency' => $service->currency,
                'source' => match ($index % 3) {
                    0 => BookingSource::Website,
                    1 => BookingSource::Phone,
                    default => BookingSource::WalkIn,
                },
                'confirmed_at' => in_array($status, [AppointmentStatus::Confirmed, AppointmentStatus::Completed], true)
                    ? $startsAt->clone()->subDays(2)
                    : null,
                'cancelled_at' => $status === AppointmentStatus::Cancelled
                    ? $startsAt->clone()->subDays(1)
                    : null,
                'cancellation_reason' => $status === AppointmentStatus::Cancelled
                    ? 'ظرف طارئ للمريض'
                    : null,
                'staff_notes' => $status === AppointmentStatus::Completed
                    ? 'التزام جيد بالخطة، نزول نصف كيلو منذ الجلسة السابقة.'
                    : null,
            ]);

            IntakeForm::factory()->create([
                'appointment_id' => $appointment->id,
                'consent_at' => $startsAt->clone()->subDays(1),
            ]);
        }

        $this->command?->info('Seeded 15 demo appointments with intake forms.');
    }
}
