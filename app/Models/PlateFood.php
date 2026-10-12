<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FoodGroup;
use App\Enums\PortionUnit;
use App\Models\Concerns\FlushesPublicContentCache;
use Database\Factories\PlateFoodFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use LogicException;
use Spatie\Translatable\HasTranslations;

/**
 * A food a patient can put on the plate.
 *
 * THIS MODEL CARRIED AN ENERGY ESTIMATE ON 2026-10-12, BY PRACTITIONER
 * DECISION. Read the whole of this note before changing anything about it.
 *
 * What it used to say, and why it said it: there were no numbers on this model
 * and there were never to be any. Numeric feedback teaches people to measure
 * food, and measuring food is the habit this clinic exists to undo — the whole
 * service being sold three sections above the plate builder is a plan built on
 * someone's own history and bloodwork rather than a figure to hit. And for
 * anyone with a disordered relationship to eating, a number attached to a food
 * is not neutral information but the mechanism of the disorder, handed out on
 * a public page before she has spoken to anybody.
 *
 * NONE OF THAT REASONING WAS WRONG, AND NONE OF IT HAS BEEN RETRACTED. The
 * practitioner weighed it and decided an honest estimate, kept secondary and
 * kept free of targets, is more useful to her patients than silence. That is
 * her call to make and she made it explicitly. It is not a conclusion anybody
 * should re-derive from scratch, and it is certainly not licence to go further.
 *
 * So the constraints that survived are enforced here rather than left to
 * good intentions:
 *
 *   - NO SINGLE FIGURE CAN BE STORED. energy_min must be strictly less than
 *     energy_max; booted() throws otherwise. Nobody weighs koshari, so a
 *     single integer would be a lie told to three significant figures — and a
 *     single figure in the database is a single figure something will
 *     eventually render.
 *   - NO UNSOURCED FIGURE CAN BE STORED. An energy value without a named
 *     `source` throws. Same discipline as the article citations.
 *   - NOTHING UNVERIFIED REACHES A PATIENT. needs_review defaults to true, and
 *     while it is set the food still appears on the plate and still counts
 *     towards the proportion feedback, but carries no estimate and is left out
 *     of the total.
 *
 * And what is still absolutely forbidden, everywhere, in both locales: a
 * target, a goal, a daily total, a remaining count, a percentage, a progress
 * bar, a streak, any history, and any colour that reads as pass or fail. The
 * qualitative proportion feedback remains the primary output and stays
 * visually dominant. PlateFeedbackHasNoNumbersTest was narrowed to permit
 * digits inside the scoped estimate component and nowhere else; its header
 * carries the full argument.
 *
 * @property int $id
 * @property string $name
 * @property FoodGroup $group
 * @property string $emoji
 * @property PortionUnit $unit
 * @property float $default_portion
 * @property int|null $energy_min
 * @property int|null $energy_max
 * @property array<string, array<int, float>>|null $macros
 * @property string|null $source
 * @property string|null $source_note
 * @property bool $needs_review
 * @property Carbon|null $reviewed_at
 * @property int|null $reviewed_by
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
        'unit',
        'default_portion',
        'energy_min',
        'energy_max',
        'macros',
        'source',
        'source_note',
        'needs_review',
        'reviewed_at',
        'reviewed_by',
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
            'unit' => PortionUnit::class,
            'default_portion' => 'float',
            'energy_min' => 'integer',
            'energy_max' => 'integer',
            'macros' => 'array',
            'needs_review' => 'boolean',
            'reviewed_at' => 'datetime',
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

    /**
     * The two rules that keep the estimate honest, enforced on save.
     *
     * In the model rather than in a form request, because the admin panel, the
     * seeder, a tinker session and a future import all have to obey them and
     * only one of those goes through a form.
     */
    protected static function booted(): void
    {
        static::saving(function (self $food): void {
            // No energy at all is a perfectly good state: the food appears on
            // the plate, counts towards the proportions, and shows no estimate.
            if ($food->energy_min === null && $food->energy_max === null) {
                return;
            }

            if ($food->energy_min === null || $food->energy_max === null) {
                throw new LogicException(
                    'Energy is stored as a span. '.$food->describe().' has only one end of it. '
                    .'Set both energy_min and energy_max, or neither.'
                );
            }

            /*
             * STRICTLY less than, not "less than or equal".
             *
             * A household spoon is not a measured tablespoon and nobody weighs
             * koshari, so min === max would be a lie told to three significant
             * figures. More practically: a single figure in the database is a
             * single figure something will eventually render, and the whole
             * presentation rule is that a patient never sees one. Making it
             * unstorable is cheaper than policing every view.
             */
            if ($food->energy_min >= $food->energy_max) {
                throw new LogicException(
                    'Energy must be a real span. '.$food->describe()
                    ." has energy_min {$food->energy_min} and energy_max {$food->energy_max}. "
                    .'A single figure for a household portion is false precision — see the class note.'
                );
            }

            /*
             * A figure nobody can trace is worse than no figure. Same rule the
             * article citations are held to.
             */
            if (blank($food->source)) {
                throw new LogicException(
                    'An energy estimate needs a named source. '.$food->describe().' has none. '
                    .'USDA FoodData Central, the Egyptian food composition tables, or a named manufacturer.'
                );
            }
        });
    }

    /**
     * How this food is named in an error a human has to act on.
     */
    public function describe(): string
    {
        return '«'.($this->getTranslation('name', 'ar', false) ?: 'unnamed food').'»';
    }

    /**
     * Whether this food may contribute an estimate to the plate.
     *
     * Reviewed, and with both ends of a span on record. Everything else shows
     * on the plate and silently sits out of the total.
     */
    public function hasPublishableEstimate(): bool
    {
        return ! $this->needs_review
            && $this->energy_min !== null
            && $this->energy_max !== null;
    }
}
