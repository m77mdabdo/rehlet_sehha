<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Enums\BookingSource;
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
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property string $reference
 * @property string $cancel_token
 * @property int $patient_id
 * @property int $service_id
 * @property int|null $staff_id
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
 * @property string|null $slot_key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Patient $patient
 * @property-read Service $service
 * @property-read User|null $staff
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
    ];

    /**
     * slot_key is deliberately absent from $fillable. It is derived state,
     * owned entirely by syncSlotKey() below — letting a request body set it
     * would hand an attacker the ability to free an occupied slot.
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
     * Unassigned appointments (staff_id NULL) collapse to the key "0-<time>",
     * which means the clinic can hold only one unassigned booking per slot.
     * That is the intended reading: with a single practitioner, an unassigned
     * appointment still consumes her hour.
     */
    public function syncSlotKey(): void
    {
        if ($this->status->releasesSlot()) {
            $this->slot_key = null;

            return;
        }

        // clone() because Carbon's utc() mutates in place, and the instance
        // here is the model's own cached starts_at attribute — converting it
        // directly would silently rewrite the value being saved.
        $this->slot_key = sprintf(
            '%d-%s',
            $this->staff_id ?? 0,
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
     * Appointments that still OCCUPY their slot.
     *
     * Deliberately not the same set as scopeActive(), and the difference is
     * the whole point: a no-show is not active, but it does not give its hour
     * back either. It is a record of something that happened at that time, and
     * the clinic has already spent the hour.
     *
     * This scope mirrors AppointmentStatus::releasesSlot() and therefore
     * syncSlotKey(), which is what the UNIQUE index on slot_key is built from.
     * The availability engine must use THIS scope: filtering by scopeActive()
     * would offer a no-show's hour on the calendar, and the insert would then
     * be refused by the index at the last step of the booking form — telling
     * the patient the time they picked was never free, after they had already
     * typed in their phone number.
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
     * Not cancelled and not a no-show.
     *
     * For reporting and lists — "what is still on the books". For anything
     * that asks whether an hour is FREE, use holdingSlot() instead.
     *
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
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
     * @return HasMany<NotificationLog, $this>
     */
    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
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
}
