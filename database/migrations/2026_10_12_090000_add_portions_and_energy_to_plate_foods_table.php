<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portions and an energy ESTIMATE on the plate builder.
 *
 * READ THIS BEFORE CHANGING ANY COLUMN HERE.
 *
 * The original table carried a comment saying there is deliberately no
 * calories column, no grams, no macro split and no portion size, and that
 * "adding a number here later would not be a new column, it would be a
 * different feature". That was correct, and this migration is that different
 * feature — approved by the practitioner on 2026-10-12, not an erosion of the
 * rule by somebody who found the comment inconvenient.
 *
 * What the original rule was protecting against, and what still holds:
 *
 *   - A calorie counter in disguise on a public page. The qualitative
 *     proportion feedback remains the primary output and stays visually
 *     dominant; the estimate is secondary.
 *   - A number a patient can fall short of. There is still no target, no
 *     daily total, no remaining count, no percentage, no progress bar and no
 *     pass/fail colour, anywhere, in either locale.
 *   - False precision. Hence the shape of the columns below.
 *
 * WHY EVERY ENERGY VALUE IS A RANGE, ENFORCED HERE RATHER THAN IN THE UI.
 *
 * There is no `energy` column and there will not be one. Energy is stored as
 * energy_min/energy_max and PlateFood refuses to save a row where min equals
 * max. A household spoon is not a measured tablespoon and nobody weighs
 * koshari, so a single integer would be a lie told to three significant
 * figures — and once a single figure exists in the database, something will
 * eventually render it. The schema makes the honest form the only storable
 * one, the same way the original schema made the absent column the only
 * option.
 *
 * `needs_review` defaults to TRUE. A food with no practitioner sign-off is
 * shown on the plate and contributes to the proportion feedback, but carries
 * no estimate and is excluded from the total. Unverified nutrition data on a
 * clinic's page is worse than none.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plate_foods', function (Blueprint $table) {
            /*
             * The household measure this food is counted in — ملعقة, كوب,
             * رغيف, قطعة, حبة, طبق صغير. Never grams: a patient who has to
             * weigh her food to use this tool is being taught the habit the
             * clinic exists to undo, and "two spoons of tahina" is a thing she
             * can actually answer.
             */
            $table->string('unit', 20)->after('emoji');

            /*
             * How much of that unit is one helping, so the first tap means
             * something sensible. Decimal because half a loaf and half an
             * avocado are both ordinary answers.
             */
            $table->decimal('default_portion', 4, 2)->default(1)->after('unit');

            /*
             * Energy for ONE unit, as a span. See the note above for why
             * there is no single-value column.
             */
            $table->unsignedSmallInteger('energy_min')->nullable()->after('default_portion');
            $table->unsignedSmallInteger('energy_max')->nullable()->after('energy_min');

            /*
             * Macros for one unit, as {protein: [min,max], carbs: [...],
             * fat: [...]} in grams. Stored because the practitioner asked for
             * them on record; NOT rendered anywhere. If a future change starts
             * displaying these, it is a new decision and needs her again.
             */
            $table->json('macros')->nullable()->after('energy_max');

            /*
             * WHERE THE NUMBER CAME FROM, PER FOOD, IN THE DATABASE.
             *
             * Not a code comment — a comment cannot be queried, cannot be
             * shown to the practitioner for sign-off, and does not survive
             * somebody editing the row in the admin panel. Same discipline as
             * the article citations: a figure whose provenance nobody can
             * trace is worse than no figure.
             */
            $table->string('source', 120)->nullable()->after('macros');
            $table->text('source_note')->nullable()->after('source');

            /*
             * True until the practitioner has signed the value off. The tool
             * shows the food and withholds the estimate while this is set.
             */
            $table->boolean('needs_review')->default(true)->after('source_note');

            $table->timestamp('reviewed_at')->nullable()->after('needs_review');
            $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('plate_foods', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');

            $table->dropColumn([
                'unit',
                'default_portion',
                'energy_min',
                'energy_max',
                'macros',
                'source',
                'source_note',
                'needs_review',
                'reviewed_at',
            ]);
        });
    }
};
