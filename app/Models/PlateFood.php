<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FoodGroup;
use App\Models\Concerns\FlushesPublicContentCache;
use Database\Factories\PlateFoodFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * A food a patient can put on the plate.
 *
 * THERE ARE NO NUMBERS ON THIS MODEL, AND THERE MUST NEVER BE.
 *
 * No calories, no grams, no portion weight, no macro split, no serving size.
 * Not hidden, not "for internal use", not on a related table. The constraint is
 * the feature.
 *
 * The reason is clinical rather than technical. Numeric feedback teaches people
 * to measure food, and measuring food is the habit this clinic exists to undo —
 * its whole positioning is a real plan built on your own history and bloodwork
 * rather than a number to hit. A calorie readout on the homepage would
 * contradict the service being sold three sections above it.
 *
 * It is also actively harmful to a specific group of visitors. For anyone with
 * a disordered relationship to eating, a number attached to a food is not
 * neutral information — it is the mechanism of the disorder, and a clinic that
 * hands it out on a public page has done harm before the patient ever speaks to
 * a practitioner. A nutrition clinic's website must not be a calorie counter in
 * disguise.
 *
 * PlateFeedbackHasNoNumbersTest enforces this against the rendered output and
 * the translation files, and it is the one test in this project that must never
 * be relaxed.
 *
 * @property int $id
 * @property string $name
 * @property FoodGroup $group
 * @property string $emoji
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static PlateFoodFactory factory($count = null, $state = [])
 */
class PlateFood extends Model
{
    use FlushesPublicContentCache;

    /** @use HasFactory<PlateFoodFactory> */
    use HasFactory;

    use HasTranslations;

    /**
     * Laravel pluralises PlateFood to "plate_food"; the table is plate_foods.
     */
    protected $table = 'plate_foods';

    /** @var list<string> */
    protected $fillable = [
        'name',
        'group',
        'emoji',
        'sort_order',
        'is_active',
    ];

    /** @var array<int, string> */
    public array $translatable = ['name'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'group' => FoodGroup::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }
}
