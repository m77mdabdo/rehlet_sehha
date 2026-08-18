<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WorkingHourFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $staff_id
 * @property int $day_of_week
 * @property string $start_time
 * @property string $end_time
 * @property int $slot_minutes
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $staff
 *
 * @method static \Database\Factories\WorkingHourFactory factory($count = null, $state = [])
 */
class WorkingHour extends Model
{
    /** @use HasFactory<WorkingHourFactory> */
    use HasFactory;

    protected $table = 'working_hours';

    /** @var list<string> */
    protected $fillable = [
        'staff_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_minutes',
        'is_active',
    ];

    /**
     * start_time and end_time are intentionally NOT cast to datetime. They are
     * bare Cairo wall-clock times with no date attached; casting would bolt a
     * meaningless date onto them and invite an accidental UTC conversion.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
