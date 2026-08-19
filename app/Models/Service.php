<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesPublicContentCache;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use LogicException;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $slug
 * @property array<string, string>|string $name
 * @property array<string, string>|string|null $subtitle
 * @property array<string, string>|string|null $description
 * @property array<string, mixed>|null $features
 * @property string $price
 * @property string $currency
 * @property int $duration_minutes
 * @property int $sessions_count
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Appointment> $appointments
 *
 * @method static \Database\Factories\ServiceFactory factory($count = null, $state = [])
 */
class Service extends Model
{
    use FlushesPublicContentCache;

    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'name',
        'subtitle',
        'description',
        'features',
        'price',
        'currency',
        'duration_minutes',
        'sessions_count',
        'is_active',
        'sort_order',
    ];

    /** @var array<int, string> */
    public array $translatable = ['name', 'subtitle', 'description', 'features'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'sessions_count' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $service): void {
            $service->guardAgainstOutgrowingTheSlotGrid();
        });
    }

    /**
     * Refuse to save an active service longer than the clinic's shortest
     * bookable slot.
     *
     * The double-booking guarantee rests on appointments.slot_key, which is
     * unique per (staff member, start instant). That prevents two appointments
     * from STARTING at the same moment. It does not prevent them from
     * OVERLAPPING: a 90-minute appointment at 10:00 and a 60-minute one at
     * 11:00 produce two different keys and both insert happily, even though
     * the doctor cannot be in both.
     *
     * Today no overlap is possible, but only because of an arithmetic accident:
     * every service (25 and 45 minutes) is shorter than the 60-minute slot
     * grid, so consecutive slots never collide. That is an invariant the schema
     * does not state anywhere, and the kind of thing a future admin panel would
     * break in one click. So it is enforced here, at the only door through
     * which a service is normally created.
     *
     * If you need a service longer than the slot grid, do not widen this check:
     * slot_key has to be replaced with real overlap detection first. See
     * docs/architecture/booking-concurrency.md.
     */
    public function guardAgainstOutgrowingTheSlotGrid(): void
    {
        if (! $this->is_active) {
            return;
        }

        $shortestSlot = WorkingHour::query()
            ->where('is_active', true)
            ->min('slot_minutes');

        // No schedule defined yet (a fresh install mid-seed): nothing to
        // violate, and the invariant test covers the seeded end state.
        if ($shortestSlot === null) {
            return;
        }

        if ($this->duration_minutes > (int) $shortestSlot) {
            throw new LogicException(sprintf(
                'Service "%s" is %d minutes long, but the shortest active working-hours slot is %d minutes. '
                .'Booking safety depends on every service fitting inside one slot: appointments.slot_key only '
                .'guarantees that no two appointments SHARE a start instant, not that they never OVERLAP. '
                .'Allowing a longer service requires replacing slot_key with a transactional range check '
                .'over [starts_at, ends_at) first — see docs/architecture/booking-concurrency.md.',
                $this->slug ?? '(unsaved)',
                $this->duration_minutes,
                (int) $shortestSlot,
            ));
        }
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * The clinical areas this package is a sensible fit for.
     *
     * Not every package suits every area, and saying so is the point: a
     * specialty page that lists all four packages has told the visitor
     * nothing.
     *
     * @return BelongsToMany<Specialty, $this>
     */
    public function specialties(): BelongsToMany
    {
        return $this->belongsToMany(Specialty::class)
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
