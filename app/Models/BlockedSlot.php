<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BlockedSlotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $staff_id
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property string|null $reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $staff
 *
 * @method static \Database\Factories\BlockedSlotFactory factory($count = null, $state = [])
 */
class BlockedSlot extends Model
{
    /** @use HasFactory<BlockedSlotFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'staff_id',
        'starts_at',
        'ends_at',
        'reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
