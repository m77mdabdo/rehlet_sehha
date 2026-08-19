<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\AppointmentMode;
use App\Enums\IntakeGoal;
use App\Livewire\Concerns\KeepsLocale;
use App\Models\Appointment;
use App\Models\Service;
use App\Services\Availability\AvailabilityEngine;
use App\Services\Availability\Slot;
use App\Services\Booking\BookingService;
use App\Services\Booking\SlotTakenException;
use App\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The booking wizard.
 *
 * Four steps, one component, ALL STATE ON THE SERVER. Nothing the browser
 * sends is trusted to describe where the patient is or what they have already
 * satisfied — the step number is re-derived from what has actually been
 * validated, so posting {"step": 4} gets you step 1.
 *
 * The component is deliberately thin. Availability lives in the engine and the
 * write lives in BookingService; what is left here is form state, validation,
 * and the rules about what may be attempted. That split is what lets the
 * transaction be tested without rendering anything.
 */
class BookingWizard extends Component
{
    use KeepsLocale;

    /**
     * Locked: Livewire refuses any update to these from the client. The step
     * is server-owned state, and a patient who could set it to 4 would skip
     * consent.
     */
    #[Locked]
    public int $step = 1;

    #[Locked]
    public ?int $serviceId = null;

    #[Locked]
    public ?string $slotKey = null;

    #[Locked]
    public ?int $staffId = null;

    #[Locked]
    public ?string $selectedDate = null;

    #[Locked]
    public ?string $reference = null;

    #[Locked]
    public ?string $cancelToken = null;

    public string $mode = 'online';

    // Step 3 — patient
    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $birthDate = '';

    // Step 3 — intake
    public string $goal = '';

    public string $medications = '';

    public string $conditions = '';

    public string $avoidFoods = '';

    public string $note = '';

    public bool $consent = false;

    /**
     * Honeypot. Named to look like a field a naive bot would fill in, and
     * hidden from humans in CSS rather than with type="hidden" — a hidden
     * input is trivially skipped, a visible-to-the-DOM one is not.
     */
    public string $website = '';

    /**
     * When step 3 was first rendered, as a server timestamp.
     *
     * Locked, so the client cannot backdate it. Compared on submit against a
     * minimum fill time: a human cannot read a consent notice and type a
     * medical history in three seconds, and a script does it instantly.
     */
    #[Locked]
    public ?int $detailsShownAt = null;

    /**
     * Set when a booking loses the race. Read by the view to show the message
     * and re-render the calendar — the form fields are untouched, which is the
     * entire point.
     */
    public bool $slotWasTaken = false;

    public function mount(?string $service = null): void
    {
        // Deep link from the packages section: preselect and open on step 2.
        if ($service !== null) {
            $preselected = $this->bookableServices()->firstWhere('slug', $service);

            if ($preselected !== null) {
                $this->serviceId = $preselected->id;
                $this->step = 2;
            }
        }

        $this->mode = AppointmentMode::bookableValues()[0] ?? 'online';
    }

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    |
    | Steps only ever move one at a time, and only forwards through a
    | validation gate. There is no goToStep(int $step) — such a method is the
    | usual way this kind of wizard ends up skippable.
    |
    */

    public function next(): void
    {
        $this->validateCurrentStep();

        if ($this->step === 2 && $this->detailsShownAt === null) {
            /*
             * Start the fill-time clock the FIRST time step 3 is reached, and
             * never again.
             *
             * Restarting it on every pass through step 2 punished exactly the
             * patient it should not: after a collision they return to the
             * calendar, pick another time, and submit within a second or two
             * because everything else is already typed — and were then told
             * they had filled the form suspiciously fast. The signal that
             * matters is how long this person has had the form open, not how
             * quickly they repicked a slot.
             */
            $this->detailsShownAt = now()->getTimestamp();
        }

        $this->step = min($this->step + 1, 3);
    }

    public function back(): void
    {
        // Going back never needs validation, but it must not escape the
        // confirmation: once booked, there is nothing to go back to.
        if ($this->step >= 4) {
            return;
        }

        $this->step = max($this->step - 1, 1);
    }

    public function selectService(int $serviceId): void
    {
        if ($this->bookableServices()->doesntContain('id', $serviceId)) {
            return;
        }

        $this->serviceId = $serviceId;

        // Changing the package changes which slots fit, so any previous choice
        // is now meaningless rather than merely stale.
        $this->slotKey = null;
        $this->staffId = null;
    }

    public function selectDate(string $date): void
    {
        $this->selectedDate = $date;
        $this->slotKey = null;
        $this->staffId = null;
    }

    public function selectSlot(string $slotKey): void
    {
        $slot = $this->slots()->first(fn (Slot $candidate): bool => $candidate->key() === $slotKey);

        // Only a slot the engine offered on THIS render may be chosen. A key
        // for a slot that has since gone simply does not match.
        if ($slot === null) {
            return;
        }

        $this->slotKey = $slot->key();
        $this->staffId = $slot->staffId;
        $this->slotWasTaken = false;
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    /**
     * Validate whichever step the SERVER believes we are on.
     *
     * Called on every transition and again on submit. The step is not a
     * parameter — it is read from server state — so a client cannot ask to
     * validate step 1 while submitting step 3.
     */
    private function validateCurrentStep(): void
    {
        match ($this->step) {
            1 => $this->validateServiceStep(),
            2 => $this->validateTimeStep(),
            3 => $this->validateDetailsStep(),
            default => null,
        };
    }

    private function validateServiceStep(): void
    {
        $this->validate(
            [
                'serviceId' => ['required', 'integer'],
                'mode' => ['required', 'string'],
            ],
            [],
            ['serviceId' => __('booking.fields.service'), 'mode' => __('booking.fields.mode')],
        );

        if ($this->bookableServices()->doesntContain('id', $this->serviceId)) {
            throw ValidationException::withMessages([
                'serviceId' => __('booking.errors.service_unavailable'),
            ]);
        }

        // Re-checked against config, not against the rendered options: a
        // tampered payload must not be able to book a disabled mode.
        if (! in_array($this->mode, AppointmentMode::bookableValues(), true)) {
            throw ValidationException::withMessages([
                'mode' => __('booking.errors.mode_unavailable'),
            ]);
        }
    }

    private function validateTimeStep(): void
    {
        if ($this->slotKey === null) {
            throw ValidationException::withMessages([
                'slotKey' => __('booking.errors.slot_required'),
            ]);
        }

        // The slot must still be on offer at this moment, not merely have been
        // chosen at some point.
        if ($this->resolveSelectedSlot() === null) {
            $this->slotKey = null;
            $this->staffId = null;

            throw ValidationException::withMessages([
                'slotKey' => __('booking.errors.slot_expired'),
            ]);
        }
    }

    private function validateDetailsStep(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string'],
            'email' => ['nullable', 'email', 'max:190'],
            'birthDate' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'goal' => ['required', 'string', 'in:'.implode(',', IntakeGoal::values())],
            'medications' => ['nullable', 'string', 'max:2000'],
            'conditions' => ['nullable', 'string', 'max:2000'],
            'avoidFoods' => ['nullable', 'string', 'max:2000'],
            'note' => ['nullable', 'string', 'max:2000'],
            // accepted, not boolean: an unticked box must fail, and `boolean`
            // would happily accept false.
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => __('booking.errors.consent_required'),
        ], [
            'name' => __('booking.fields.name'),
            'phone' => __('booking.fields.phone'),
            'email' => __('booking.fields.email'),
            'birthDate' => __('booking.fields.birth_date'),
            'goal' => __('booking.fields.goal'),
            'medications' => __('booking.fields.medications'),
            'conditions' => __('booking.fields.conditions'),
            'avoidFoods' => __('booking.fields.avoid_foods'),
            'note' => __('booking.fields.note'),
        ]);

        if (! PhoneNumber::isValid($this->phone)) {
            throw ValidationException::withMessages([
                'phone' => __('booking.errors.phone_invalid'),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submit(BookingService $booking): void
    {
        // Every earlier gate again. Reaching submit is not evidence that steps
        // 1 and 2 were ever satisfied on this session.
        $this->step = 1;
        $this->validateServiceStep();

        // No slot ever chosen is a validation error; a slot that has since
        // GONE is not, and is handled below.
        if ($this->slotKey === null) {
            $this->step = 2;

            throw ValidationException::withMessages([
                'slotKey' => __('booking.errors.slot_required'),
            ]);
        }

        $this->step = 3;
        $this->validateDetailsStep();

        $this->guardAgainstBots();
        $this->guardRateLimits();

        /*
         * The slot check comes LAST, and deliberately does not go through
         * validateTimeStep().
         *
         * That method reports a vanished slot as a plain validation error —
         * "no longer available, choose another" — which is accurate and
         * useless. A slot that disappears between rendering and submitting is
         * a collision, and the patient deserves to be told that somebody was
         * seconds faster AND that nothing they typed has been lost. Routing it
         * through handleSlotTaken() is what produces that message.
         *
         * It is also last so that a patient with a typo in their email and a
         * slot that has just gone sees the typo first, rather than being sent
         * back to the calendar only to be sent back again.
         */
        $slot = $this->resolveSelectedSlot();

        if ($slot === null) {
            $this->handleSlotTaken();

            return;
        }

        try {
            $appointment = $booking->book(
                service: $this->service(),
                startsAtUtc: $slot->startsAtUtc,
                staffId: $slot->staffId,
                mode: AppointmentMode::from($this->mode),
                patientData: [
                    'name' => trim($this->name),
                    'phone' => PhoneNumber::e164($this->phone),
                    'email' => $this->email === '' ? null : $this->email,
                    'birth_date' => $this->birthDate === '' ? null : $this->birthDate,
                ],
                intakeData: [
                    'goal' => $this->goal,
                    'medications' => $this->blankToNull($this->medications),
                    'conditions' => $this->blankToNull($this->conditions),
                    'avoid_foods' => $this->blankToNull($this->avoidFoods),
                    'note' => $this->blankToNull($this->note),
                ],
                consentIp: (string) request()->ip(),
            );
        } catch (SlotTakenException) {
            $this->handleSlotTaken();

            return;
        }

        RateLimiter::hit($this->ipRateLimitKey(), 3600);
        RateLimiter::hit($this->phoneRateLimitKey(), 3600);

        $this->reference = $appointment->reference;
        $this->cancelToken = $appointment->cancel_token;
        $this->step = 4;
    }

    /**
     * Someone else got the slot.
     *
     * NOTHING THE PATIENT TYPED IS CLEARED. Only the slot selection goes,
     * because only the slot selection is now wrong. Re-typing a medication
     * list because another person clicked a second earlier is the worst
     * outcome this form has, and it is entirely avoidable.
     */
    private function handleSlotTaken(): void
    {
        $this->slotKey = null;
        $this->staffId = null;
        $this->slotWasTaken = true;

        // Back to the calendar, which re-renders from the engine and will no
        // longer show the taken slot.
        $this->step = 2;
    }

    /*
    |--------------------------------------------------------------------------
    | Abuse
    |--------------------------------------------------------------------------
    */

    private function guardAgainstBots(): void
    {
        // The honeypot is invisible to humans, so anything in it came from
        // something reading the DOM rather than the page.
        if (trim($this->website) !== '') {
            throw ValidationException::withMessages([
                'name' => __('booking.errors.rejected'),
            ]);
        }

        $minimumSeconds = (int) config('clinic.booking.minimum_fill_seconds', 6);

        if ($this->detailsShownAt === null
            || now()->getTimestamp() - $this->detailsShownAt < $minimumSeconds) {
            throw ValidationException::withMessages([
                'name' => __('booking.errors.too_fast'),
            ]);
        }
    }

    /**
     * Two limits, because they stop different things.
     *
     * Per IP catches one machine hammering the form. Per phone catches a
     * distributed attempt to fill the calendar with bookings for a number
     * nobody will answer — which is the version that actually hurts a clinic,
     * because every one of those is a held slot and a wasted hour.
     *
     * Both are checked BEFORE the write and only incremented after a
     * successful one, so a patient who mistypes their name four times is not
     * locked out for an hour.
     */
    private function guardRateLimits(): void
    {
        if (RateLimiter::tooManyAttempts($this->ipRateLimitKey(), 5)) {
            throw ValidationException::withMessages([
                'name' => __('booking.errors.too_many_attempts', [
                    'minutes' => (int) ceil(RateLimiter::availableIn($this->ipRateLimitKey()) / 60),
                ]),
            ]);
        }

        if (RateLimiter::tooManyAttempts($this->phoneRateLimitKey(), 3)) {
            throw ValidationException::withMessages([
                'phone' => __('booking.errors.too_many_for_phone', [
                    'minutes' => (int) ceil(RateLimiter::availableIn($this->phoneRateLimitKey()) / 60),
                ]),
            ]);
        }
    }

    private function ipRateLimitKey(): string
    {
        return 'booking:ip:'.request()->ip();
    }

    private function phoneRateLimitKey(): string
    {
        // Hashed: the rate limiter's keys land in the cache store, and a cache
        // full of patient phone numbers is a second copy of the contact list.
        return 'booking:phone:'.hash('sha256', (string) PhoneNumber::e164($this->phone));
    }

    /*
    |--------------------------------------------------------------------------
    | Data for the view
    |--------------------------------------------------------------------------
    */

    /**
     * @return Collection<int, Service>
     */
    public function bookableServices(): Collection
    {
        return Service::active()->get();
    }

    public function service(): ?Service
    {
        if ($this->serviceId === null) {
            return null;
        }

        return $this->bookableServices()->firstWhere('id', $this->serviceId);
    }

    /**
     * Slots for the selected day, or for the first day that has any.
     *
     * @return Collection<int, Slot>
     */
    public function slots(): Collection
    {
        $service = $this->service();

        if ($service === null) {
            return collect();
        }

        $engine = app(AvailabilityEngine::class);

        $date = $this->selectedDate !== null
            ? CarbonImmutable::parse($this->selectedDate, config('clinic.timezone'))
            : CarbonImmutable::now(config('clinic.timezone'));

        return $engine->availableSlots(
            $date->startOfDay()->utc(),
            $date->endOfDay()->utc(),
            null,
            $service,
        );
    }

    /**
     * The days to show in the strip, each flagged with whether it has slots.
     *
     * One engine call for the whole horizon rather than one per day: the day
     * strip is fourteen days wide and a query per day would be fourteen round
     * trips to render a row of buttons.
     *
     * @return list<array{date: string, label: string, weekday: string, available: bool, selected: bool}>
     */
    public function days(): array
    {
        $service = $this->service();

        if ($service === null) {
            return [];
        }

        $timezone = config('clinic.timezone');
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $span = 14;

        $slots = app(AvailabilityEngine::class)->availableSlots(
            $today->utc(),
            $today->addDays($span)->endOfDay()->utc(),
            null,
            $service,
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

    public function selectedSlot(): ?Slot
    {
        return $this->resolveSelectedSlot();
    }

    /**
     * Find the chosen slot among the ones currently on offer.
     *
     * Returns null the moment it stops being offered, which is what makes this
     * the single source of truth for "is the selection still good". Never
     * reconstructs an instant by parsing the key — the key is an identifier,
     * not a value.
     */
    private function resolveSelectedSlot(): ?Slot
    {
        if ($this->slotKey === null || $this->service() === null) {
            return null;
        }

        $engine = app(AvailabilityEngine::class);
        $timezone = config('clinic.timezone');
        $today = CarbonImmutable::now($timezone)->startOfDay();

        return $engine->availableSlots(
            $today->utc(),
            $today->addDays(14)->endOfDay()->utc(),
            null,
            $this->service(),
        )->first(fn (Slot $slot): bool => $slot->key() === $this->slotKey);
    }

    public function bookedAppointment(): ?Appointment
    {
        if ($this->reference === null) {
            return null;
        }

        return Appointment::query()
            ->with(['service', 'staff'])
            ->where('reference', $this->reference)
            ->first();
    }

    private function blankToNull(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    public function render(): View
    {
        return view('livewire.booking-wizard', [
            'services' => $this->bookableServices(),
            'goals' => IntakeGoal::options(),
            'modes' => AppointmentMode::bookableOptions(),
        ]);
    }
}
