<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\AppointmentStatus;
use App\Enums\IntakeGoal;
use App\Livewire\Concerns\KeepsLocale;
use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Services\Availability\AvailabilityEngine;
use App\Services\Availability\Slot;
use App\Services\Booking\BookingService;
use App\Services\Booking\SlotTakenException;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Cancel or move an appointment, without an account.
 *
 * Authentication is the token in the URL: 64 random characters from
 * Str::random, unguessable, and tied to exactly one appointment. A clinic
 * whose patients had to create a password to cancel would have patients who
 * simply do not turn up instead.
 *
 * The token is therefore a bearer credential and is treated as one. It never
 * appears in anything we hand to a third party: no analytics, no og:url, no
 * outbound link. Its only home is the message we send the patient.
 */
class AppointmentManager extends Component
{
    use KeepsLocale;

    /**
     * Locked so the client cannot swap it for another appointment's token
     * after the component has mounted.
     */
    #[Locked]
    public string $token = '';

    #[Locked]
    public ?string $selectedDate = null;

    #[Locked]
    public ?string $slotKey = null;

    public bool $showReschedule = false;

    /*
    |--------------------------------------------------------------------------
    | Data subject rights
    |--------------------------------------------------------------------------
    |
    | Access, correction and erasure, on the page the patient already has a
    | link to. Egyptian law 151/2020 grants all three and the privacy page
    | promises them; before this they were "telephone the clinic", which is a
    | written promise without a mechanism.
    |
    */

    public bool $showIntake = false;

    public bool $editingIntake = false;

    public bool $confirmingErasure = false;

    public string $goal = '';

    public string $medications = '';

    public string $conditions = '';

    public string $avoidFoods = '';

    public string $note = '';

    public bool $slotWasTaken = false;

    public ?string $flash = null;

    public function mount(string $token): void
    {
        $this->token = $token;

        // 404 rather than a message: an invalid token should be
        // indistinguishable from a URL that never existed.
        abort_if($this->appointment() === null, 404);
    }

    public function appointment(): ?Appointment
    {
        return Appointment::query()
            ->with(['service', 'patient', 'staff'])
            ->where('cancel_token', $this->token)
            ->first();
    }

    /**
     * Whether the appointment may still be changed by the patient.
     *
     * Governed by reschedule_min_hours. Past the cutoff the interface shows
     * the clinic's phone number instead of a disabled button: a patient who
     * needs to cancel two hours before still needs to cancel, and a dead
     * control tells them nothing about what to do instead.
     */
    public function isChangeable(): bool
    {
        $appointment = $this->appointment();

        if ($appointment === null) {
            return false;
        }

        if ($appointment->status === AppointmentStatus::Cancelled) {
            return false;
        }

        // A past appointment is history, whatever the cutoff says.
        if ($appointment->starts_at->isPast()) {
            return false;
        }

        $cutoff = CarbonImmutable::now()->addHours(
            (int) config('clinic.booking.reschedule_min_hours', 1),
        );

        return $appointment->starts_at->greaterThan($cutoff);
    }

    /**
     * Which of the four situations this appointment is in.
     *
     * Computed here rather than as an @if/@elseif chain in the view, for two
     * reasons. The view should not be deciding whether an appointment is
     * beyond its cancellation cutoff — that is a rule, not presentation. And
     * Livewire wraps conditionals in DOM-morphing markers, which it does not
     * place correctly around a long @elseif chain; keeping the template to
     * simple, separate @if blocks avoids the problem entirely.
     *
     * @return 'cancelled'|'past'|'too_late'|'open'
     */
    public function state(): string
    {
        $appointment = $this->appointment();

        if ($appointment === null || $appointment->status === AppointmentStatus::Cancelled) {
            return 'cancelled';
        }

        if ($appointment->starts_at->isPast()) {
            return 'past';
        }

        return $this->isChangeable() ? 'open' : 'too_late';
    }

    public function cancel(): void
    {
        $appointment = $this->appointment();

        if ($appointment === null || ! $this->isChangeable()) {
            return;
        }

        // cancel() nulls slot_key through the model hook, which is what
        // actually hands the hour back to the calendar.
        $appointment->cancel(__('booking.manage.cancelled_by_patient'));

        $this->showReschedule = false;
        $this->flash = 'manage.cancelled';
    }

    public function startReschedule(): void
    {
        if (! $this->isChangeable()) {
            return;
        }

        $this->showReschedule = true;
        $this->flash = null;
    }

    /**
     * Show what the patient submitted, decrypted.
     *
     * No guard beyond holding the token: they wrote this about themselves, and
     * a system that stores someone's medical history but will not show it back
     * to them is not protecting them from anything.
     */
    public function toggleIntake(): void
    {
        $this->showIntake = ! $this->showIntake;
        $this->editingIntake = false;
        $this->confirmingErasure = false;
    }

    public function startEditingIntake(): void
    {
        $intake = $this->intake();

        if ($intake === null || ! $intake->isCorrectable()) {
            return;
        }

        // Load the current values into the form.
        $content = $intake->clinicalContent();

        $this->goal = (string) ($content['goal'] ?? '');
        $this->medications = (string) ($content['medications'] ?? '');
        $this->conditions = (string) ($content['conditions'] ?? '');
        $this->avoidFoods = (string) ($content['avoid_foods'] ?? '');
        $this->note = (string) ($content['note'] ?? '');

        $this->showIntake = true;
        $this->editingIntake = true;
        $this->confirmingErasure = false;
        $this->flash = null;
    }

    public function cancelEditingIntake(): void
    {
        $this->editingIntake = false;
    }

    /**
     * Save a correction.
     *
     * Same validation as booking — a correction is not a lesser kind of
     * clinical record. The activity log records that a correction happened and
     * which fields, never the content; see IntakeForm::buildChanges().
     */
    public function saveIntake(): void
    {
        $intake = $this->intake();

        if ($intake === null || ! $intake->isCorrectable()) {
            return;
        }

        $this->validate([
            'goal' => ['required', 'string', 'in:'.implode(',', IntakeGoal::values())],
            'medications' => ['nullable', 'string', 'max:2000'],
            'conditions' => ['nullable', 'string', 'max:2000'],
            'avoidFoods' => ['nullable', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'goal' => __('booking.fields.goal'),
            'medications' => __('booking.fields.medications'),
            'conditions' => __('booking.fields.conditions'),
            'avoidFoods' => __('booking.fields.avoid_foods'),
            'note' => __('booking.fields.note'),
        ]);

        $intake->update([
            'goal' => $this->goal,
            'medications' => $this->blankToNull($this->medications),
            'conditions' => $this->blankToNull($this->conditions),
            'avoid_foods' => $this->blankToNull($this->avoidFoods),
            'note' => $this->blankToNull($this->note),
        ]);

        $this->editingIntake = false;
        $this->flash = 'rights.updated';
    }

    public function startErasure(): void
    {
        if ($this->intake() === null || $this->intake()->isErased()) {
            return;
        }

        $this->confirmingErasure = true;
        $this->editingIntake = false;
        $this->showIntake = true;
        $this->flash = null;
    }

    public function cancelErasure(): void
    {
        $this->confirmingErasure = false;
    }

    /**
     * Erase the clinical content. The appointment stays.
     *
     * Deliberately NOT a hard delete of the booking: the clinic ran that hour
     * and has to account for it. What the patient has a right to remove is
     * what they wrote about their health, and that is what goes.
     *
     * Available even after the appointment has passed — the right to erase
     * clinical content does not expire when a consultation ends, unlike the
     * right to rewrite it.
     */
    public function eraseIntake(): void
    {
        $intake = $this->intake();

        if ($intake === null || $intake->isErased()) {
            return;
        }

        $intake->eraseClinicalContent();

        $this->confirmingErasure = false;
        $this->editingIntake = false;
        $this->flash = 'rights.erased';
    }

    public function intake(): ?IntakeForm
    {
        return $this->appointment()?->intakeForm;
    }

    private function blankToNull(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->slotKey = null;
    }

    public function selectSlot(string $slotKey): void
    {
        if ($this->slots()->doesntContain(fn (Slot $slot): bool => $slot->key() === $slotKey)) {
            return;
        }

        $this->slotKey = $slotKey;
        $this->slotWasTaken = false;
    }

    public function confirmReschedule(BookingService $booking): void
    {
        $appointment = $this->appointment();

        if ($appointment === null || ! $this->isChangeable() || $this->slotKey === null) {
            return;
        }

        $slot = $this->slots()->first(fn (Slot $candidate): bool => $candidate->key() === $this->slotKey);

        if ($slot === null) {
            $this->slotWasTaken = true;
            $this->slotKey = null;

            return;
        }

        try {
            // Same transaction discipline as booking: the row is locked, the
            // slot re-checked inside, and the unique index arbitrates.
            $booking->reschedule($appointment, $slot->startsAtUtc);
        } catch (SlotTakenException) {
            $this->slotWasTaken = true;
            $this->slotKey = null;

            return;
        }

        $this->showReschedule = false;
        $this->slotKey = null;
        $this->flash = 'manage.rescheduled';
    }

    /**
     * @return Collection<int, Slot>
     */
    public function slots(): Collection
    {
        $appointment = $this->appointment();

        if ($appointment === null) {
            return collect();
        }

        $timezone = config('clinic.timezone');

        $date = $this->selectedDate !== null
            ? CarbonImmutable::parse($this->selectedDate, $timezone)
            : CarbonImmutable::now($timezone);

        return app(AvailabilityEngine::class)->availableSlots(
            $date->startOfDay()->utc(),
            $date->endOfDay()->utc(),
            $appointment->staff_id,
            $appointment->service,
        );
    }

    /**
     * @return list<array{date: string, label: string, weekday: string, available: bool, selected: bool}>
     */
    public function days(): array
    {
        $appointment = $this->appointment();

        if ($appointment === null) {
            return [];
        }

        $timezone = config('clinic.timezone');
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $span = 14;

        $slots = app(AvailabilityEngine::class)->availableSlots(
            $today->utc(),
            $today->addDays($span)->endOfDay()->utc(),
            $appointment->staff_id,
            $appointment->service,
        );

        $withSlots = $slots->map(fn (Slot $slot): string => $slot->cairoDate())->unique()->all();

        $days = [];

        for ($offset = 0; $offset < $span; $offset++) {
            $date = $today->addDays($offset);
            $key = $date->format('Y-m-d');

            $days[] = [
                'date' => $key,
                'label' => $date->translatedFormat('j M'),
                'weekday' => $date->translatedFormat('D'),
                'available' => in_array($key, $withSlots, true),
                'selected' => $this->selectedDate === $key,
            ];
        }

        return $days;
    }

    public function render(): View
    {
        return view('livewire.appointment-manager', [
            'appointment' => $this->appointment(),
            'intake' => $this->intake(),
            'goals' => IntakeGoal::options(),
        ]);
    }
}
