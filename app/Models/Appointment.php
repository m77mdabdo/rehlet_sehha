<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Enums\BookingSource;
use App\Enums\ContactPreference;
use Database\Factories\AppointmentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LogicException;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property string $reference
 * @property string $cancel_token
 * @property int $patient_id
 * @property int $service_id
 * @property int $staff_id
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property AppointmentMode $mode
 * @property AppointmentStatus $status
 * @property string $price
 * @property string $currency
 * @property BookingSource $source
 * @property string|null $staff_notes
 * @property Carbon|null $confirmed_at
 * @property Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property string $locale
 * @property Carbon|null $reminder_24h_sent_at
 * @property Carbon|null $reminder_1h_sent_at
 * @property string|null $slot_key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Patient $patient
 * @property-read Service $service
 * @property-read User $staff
 * @property-read IntakeForm|null $intakeForm
 * @property-read Collection<int, NotificationLog> $notificationLogs
 *
 * @method static \Database\Factories\AppointmentFactory factory($count = null, $state = [])
 */
class Appointment extends Model
{
    /** @use HasFactory<AppointmentFactory> */
    use HasFactory;

    use LogsActivity;
    use SoftDeletes;

    /**
     * Mirrors the database defaults so that the saving() hook below can rely on
     * $this->status being a real enum even on a brand-new unsaved model.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'status' => 'pending',
        'currency' => 'EGP',
        'source' => 'website',
    ];

    /** @var list<string> */
    protected $fillable = [
        'reference',
        'cancel_token',
        'patient_id',
        'service_id',
        'staff_id',
        'starts_at',
        'ends_at',
        'mode',
        'status',
        'price',
        'currency',
        'source',
        'staff_notes',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
        // The language the booking was made in; every notification is sent in
        // it. See the migration that added the column for why it cannot be
        // worked out later.
        'locale',
    ];

    /**
     * slot_key is deliberately absent from $fillable. It is derived state,
     * owned entirely by syncSlotKey() below — letting a request body set it
     * would hand an attacker the ability to free an occupied slot.
     *
     * The two reminder stamps are absent for the same reason. They are a
     * concurrency claim rather than data: the reminder command sets one with a
     * conditional UPDATE so that two overlapping cron runs cannot both send.
     * Mass assignment would let a payload clear a stamp and re-arm a reminder
     * that has already gone out.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reminder_24h_sent_at' => 'datetime',
            'reminder_1h_sent_at' => 'datetime',
            'mode' => AppointmentMode::class,
            'status' => AppointmentStatus::class,
            'source' => BookingSource::class,
            'price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        // Derive the slot key on every write path, so there is no way to create
        // or move an appointment without the unique index seeing it.
        static::saving(function (self $appointment): void {
            $appointment->syncSlotKey();
        });

        // A soft-deleted appointment is gone as far as the clinic is concerned,
        // but its row — and therefore its slot_key — survives. Without this the
        // deleted record would hold its hour hostage forever, because nothing
        // else ever clears the key.
        static::deleted(function (self $appointment): void {
            /*
             * SOFT deletes only. On a force delete the row is already gone, and
             * Laravel has set exists = false — so saveQuietly() below is not an
             * UPDATE, it is an INSERT, and it silently RESURRECTS the
             * appointment that was just destroyed.
             *
             * The bug hid because it only fires when the appointment still
             * holds its slot: cancel first (which nulls the key) and the guard
             * below skips the save, so a force delete of a cancelled booking
             * behaves correctly. Force-delete a live one and it comes back,
             * with its id, its reference and its slot — and no error anywhere.
             *
             * Found while testing the queue guard: a hard-deleted appointment
             * kept restoring successfully in the job, because it had never
             * actually been deleted.
             */
            if ($appointment->isForceDeleting()) {
                return;
            }

            if ($appointment->slot_key !== null) {
                $appointment->slot_key = null;
                $appointment->saveQuietly();
            }
        });

        // Restoring re-claims the slot, and correctly fails with a
        // QueryException if someone else took it in the meantime.
        static::restored(function (self $appointment): void {
            $appointment->syncSlotKey();
            $appointment->saveQuietly();
        });

        /*
         * A hard delete strands any notification still queued for this row.
         *
         * The job carries the appointment by id and re-queries it when the
         * worker picks it up; against a deleted row that throws before the
         * notification's own code runs. The notifications handle it —
         * $deleteWhenMissingModels discards the job instead of failing it —
         * but by then nothing knows WHY the row vanished, so the reason is
         * recorded here, at the only point where it is known.
         *
         * Written to the application log rather than notification_logs
         * because that table's appointment_id cascades on delete: its rows for
         * this appointment are being removed by the same statement, so there
         * is nothing left there to annotate.
         *
         * Deliberately forceDeleted and not deleted: a soft delete leaves the
         * row in place, queued jobs still restore it (Laravel uses
         * newQueryWithoutScopes), and those notifications should still go out.
         */
        static::forceDeleted(function (self $appointment): void {
            logger()->info('Appointment hard-deleted; any queued notifications for it will be discarded.', [
                'appointment_id' => $appointment->getKey(),
                'reference' => $appointment->reference,
            ]);
        });
    }

    /**
     * Maintain the double-booking guard described in the appointments migration.
     *
     * While the appointment holds its slot, slot_key is a deterministic string
     * built from the staff member and the start instant, and the UNIQUE index
     * on that column makes a second booking of the same slot fail at the
     * database. When the appointment is cancelled the key becomes NULL, and
     * because MySQL allows unlimited NULLs in a unique index the slot is
     * immediately free for someone else.
     *
     * staff_id is NOT NULL at the database level, so the key always names a
     * real practitioner and the lock is genuinely per-person. It used to be
     * nullable, and a NULL collapsed the key to "0-<time>" — harmless with one
     * doctor, wrong in both directions with two: it refused a second
     * unassigned booking the clinic could have taken, and it failed to lock
     * whichever practitioner ended up doing it. See the migration that closed
     * this for why it is a schema constraint rather than a guard.
     */
    public function syncSlotKey(): void
    {
        if ($this->status->releasesSlot()) {
            $this->slot_key = null;

            return;
        }

        // getAttribute rather than $this->staff_id: the property is typed int
        // because the column is NOT NULL, but an unsaved model can still be
        // holding null, and that is exactly the case worth catching early.
        if ($this->getAttribute('staff_id') === null) {
            // The database would refuse this anyway; failing here says why.
            throw new LogicException(
                'An appointment must name the practitioner who will see the patient. '
                .'staff_id is NOT NULL precisely so that slot_key locks one person\'s hour '
                .'rather than collapsing to a clinic-wide "0-<time>" key.'
            );
        }

        // clone() because Carbon's utc() mutates in place, and the instance
        // here is the model's own cached starts_at attribute — converting it
        // directly would silently rewrite the value being saved.
        $this->slot_key = sprintf(
            '%d-%s',
            $this->staff_id,
            $this->starts_at->clone()->utc()->format('Y-m-d H:i:s'),
        );
    }

    /**
     * Cancel the appointment, releasing its slot back to the calendar.
     */
    public function cancel(?string $reason = null): bool
    {
        $this->status = AppointmentStatus::Cancelled;
        $this->cancelled_at = Carbon::now();
        $this->cancellation_reason = $reason;

        return $this->save();
    }

    public function confirm(): bool
    {
        $this->status = AppointmentStatus::Confirmed;
        $this->confirmed_at = Carbon::now();

        return $this->save();
    }

    public static function generateReference(): string
    {
        return 'RS-'.Str::upper(Str::random(8));
    }

    public static function generateCancelToken(): string
    {
        return Str::random(64);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('starts_at', '>=', Carbon::now())->orderBy('starts_at');
    }

    /**
     * Answers ONE question: is this hour still occupied?
     *
     * Use this, and only this, for anything to do with availability. For
     * "how much work did the clinic do" use countsTowardWorkload(), which is
     * the WRONG tool here because it drops no-shows — and a no-show has not
     * given its hour back.
     *
     * Mirrors AppointmentStatus::releasesSlot() and therefore syncSlotKey(),
     * which is what the UNIQUE index on slot_key is built from. Only
     * cancellation frees a slot. A no-show is a record of something that
     * consumed that time; offering it again would produce a calendar that
     * contradicts its own database, and an insert refused by the index at the
     * last step of the booking form — telling a patient the time they picked
     * was never free, after they had typed in their phone number.
     *
     * @param  Builder<self>  $query
     */
    public function scopeHoldingSlot(Builder $query): void
    {
        $releasing = array_map(
            fn (AppointmentStatus $status): string => $status->value,
            array_filter(
                AppointmentStatus::cases(),
                fn (AppointmentStatus $status): bool => $status->releasesSlot(),
            ),
        );

        $query->whereNotIn('status', $releasing);
    }

    /**
     * Answers ONE question: which appointments consumed, or will consume,
     * clinic capacity — excluding cancellations and no-shows?
     *
     * Reporting only. This is the WRONG tool for availability, because it
     * drops no-shows and would therefore offer an hour the clinic has already
     * spent and the slot_key index still holds; use holdingSlot() for that.
     *
     * Named at length on purpose. It was called scopeActive(), which sounds
     * like the obvious answer to every question and is the right answer to
     * only one of them.
     *
     * @param  Builder<self>  $query
     */
    public function scopeCountsTowardWorkload(Builder $query): void
    {
        $query->whereNotIn('status', [
            AppointmentStatus::Cancelled->value,
            AppointmentStatus::NoShow->value,
        ]);
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Never null: every appointment names the practitioner who will see the
     * patient. Enforced by the NOT NULL column, not by convention.
     *
     * @return BelongsTo<User, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * @return HasOne<IntakeForm, $this>
     */
    public function intakeForm(): HasOne
    {
        return $this->hasOne(IntakeForm::class);
    }

    /**
     * The review invitation for this visit, if one has been sent.
     *
     * hasOne rather than hasMany: reviews.appointment_id is unique, so a
     * patient is invited once per visit however many times the scheduler runs.
     *
     * @return HasOne<Review, $this>
     */
    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * @return HasMany<NotificationLog, $this>
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    /**
     * The start time as a human in Cairo reads it.
     *
     * Every notification states the time in the clinic's timezone and names
     * that timezone, because a patient consulting from Riyadh or London
     * otherwise has no way to know which clock "17:00" belongs to.
     */
    public function startsAtClinic(): Carbon
    {
        return $this->starts_at->clone()->setTimezone(config('clinic.timezone'));
    }

    /**
     * Is this appointment still going to happen?
     *
     * The question a reminder must ask before it sends. Cancelled and
     * no-show appointments are in the past tense; reminding someone about an
     * appointment they cancelled is the kind of message that makes a patient
     * distrust everything else the clinic sends them.
     */
    public function isLive(): bool
    {
        return in_array($this->status, [
            AppointmentStatus::Pending,
            AppointmentStatus::Confirmed,
        ], true);
    }

    /**
     * How the clinic can reach this patient — computed, never stored.
     *
     * See App\Enums\ContactPreference for why there is no column. In short:
     * the answer follows the patient's email address, she can add one minutes
     * after booking, and a stale copy would send a receptionist to ring
     * somebody who has been getting reminders all along.
     */
    public function contactPreference(): ContactPreference
    {
        $email = $this->patient->email;

        return $email === null || trim($email) === ''
            ? ContactPreference::PhoneOnly
            : ContactPreference::Email;
    }

    /**
     * Will anything we send actually arrive?
     *
     * False means this patient gets no confirmation, no reminder and no manage
     * link, and the only way she learns anything is if a person telephones
     * her.
     */
    public function isReachableByEmail(): bool
    {
        return $this->contactPreference()->reachesElectronically();
    }

    /**
     * The patient's own management URL, in the language they booked in.
     *
     * Built with an explicit locale rather than the ambient one: a reminder is
     * rendered by a cron run, which has no locale of its own, and route()
     * would otherwise fall back to whatever URL::defaults happened to hold.
     */
    public function manageUrl(): string
    {
        return route('appointment.manage', [
            'locale' => $this->locale,
            'token' => $this->cancel_token,
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status',
                'starts_at',
                'ends_at',
                'staff_id',
                'service_id',
                'mode',
                'price',
                'cancellation_reason',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('appointment');
    }

    /**
     * How long a manage link keeps working after the appointment has ended.
     *
     * A cancel token is a bearer credential: whoever holds the URL can cancel
     * or reschedule without proving anything else. It used to have no expiry
     * at all, so a link mailed a year ago still worked, and every forwarded
     * message, shared screenshot and synced mailbox was a permanent key.
     *
     * Fourteen days past the appointment is enough for the ordinary reasons
     * somebody re-opens it — checking what time it was, reading the intake
     * back, exercising a data right — and short enough that an old mailbox
     * is not a way in.
     */
    public const TOKEN_GRACE_DAYS = 14;

    /**
     * When this appointment's manage link stops working.
     */
    public function tokenExpiresAt(): Carbon
    {
        return $this->ends_at->copy()->addDays(self::TOKEN_GRACE_DAYS);
    }

    public function tokenHasExpired(): bool
    {
        return Carbon::now()->greaterThan($this->tokenExpiresAt());
    }
}
