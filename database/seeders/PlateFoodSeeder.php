<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\FoodGroup;
use App\Enums\PortionUnit;
use App\Models\PlateFood;
use Illuminate\Database\Seeder;

/**
 * The foods on the plate builder, with portions and a DRAFT energy estimate.
 *
 * Real Egyptian food, not a generic international list. A tool that offers
 * quinoa and kale to somebody who eats foul and baladi bread is not teaching
 * her anything about her own plate — it is teaching her that her food is not
 * what the chart means, which is the opposite of the point.
 *
 * ---------------------------------------------------------------------------
 * EVERY ENERGY VALUE IN THIS FILE IS UNVERIFIED AND FLAGGED AS SUCH.
 * ---------------------------------------------------------------------------
 *
 * These were drafted WITHOUT internet access, from memory of what USDA
 * FoodData Central and the Egyptian food composition tables say. That is a
 * legitimate way to draft and an indefensible way to publish — the same
 * position the article citations were in, and it is handled the same way:
 *
 *   - every row names the source it is supposed to come from, in the database;
 *   - every row ships with `needs_review => true`;
 *   - a food awaiting review appears on the plate and counts towards the
 *     proportion feedback, and shows NO estimate and is left out of the total.
 *
 * So on a fresh seed the feature displays no numbers at all. That is the
 * correct state, not an unfinished one. docs/content/plate-nutrition-to-verify.md
 * lists every value for the practitioner to check against the named source and
 * sign off; until she does, the tool behaves exactly as it did before this
 * change.
 *
 * WHY EVERY VALUE IS A SPAN, including the ones that look precise in a
 * database: the unit is a household measure. A spoon is not a calibrated
 * volume and a baladi loaf is not a standard weight, so anything derived from
 * one is a range before the food itself varies at all. PlateFood::booted()
 * refuses to save min === max for this reason.
 *
 * THE FOUR COMPOSITE DISHES — كشري، مسقعة، ملوخية، كرنب محشي — carry
 * deliberately wide spans. Nobody measures the oil in molokhia or the pasta in
 * koshari, and household recipes differ by more than any figure could capture.
 * A tight-looking number for a dish nobody measures is worse than an honest
 * span, so these are the rows where the span is the point rather than a
 * concession.
 *
 * Macros are recorded because the practitioner asked for them on record. They
 * are NOT rendered anywhere. Displaying them would be a new decision.
 */
class PlateFoodSeeder extends Seeder
{
    /**
     * Named sources, written once so a typo cannot create a fifth one.
     */
    private const USDA = 'USDA FoodData Central';

    private const EGYPT = 'Egyptian food composition tables (National Nutrition Institute)';

    public function run(): void
    {
        /*
         * emoji, group, ar, en, unit, default_portion,
         * energy_min, energy_max, [protein, carbs, fat] as [min,max] grams,
         * source, source_note
         */
        $foods = [
            // ---- Vegetables — the group the feedback most often asks for more of.
            ['🥗', FoodGroup::Vegetable, 'سلطة خضراء', 'Green salad', PortionUnit::SmallPlate, 1,
                25, 60, [[1, 3], [4, 9], [0, 3]], self::USDA,
                'Mixed leaf and tomato salad, undressed at the low end; the upper end allows a spoon of oil.'],

            ['🌿', FoodGroup::Vegetable, 'ملوخية', 'Molokhia', PortionUnit::SmallPlate, 1,
                60, 180, [[3, 6], [6, 12], [2, 13]], self::EGYPT,
                'COMPOSITE DISH — span is wide on purpose. The whole variation is the ghee or oil, '
                .'which is added by eye and differs by household more than the leaf does.'],

            ['🥒', FoodGroup::Vegetable, 'طرشي', 'Torshi (pickles)', PortionUnit::SmallPlate, 1,
                10, 30, [[0, 2], [2, 6], [0, 1]], self::EGYPT,
                'Pickled turnip and carrot, drained. Energy is negligible; salt is the thing that matters '
                .'clinically and this tool does not discuss it.'],

            ['🍆', FoodGroup::Vegetable, 'مسقعة', 'Mesaqaa (aubergine)', PortionUnit::SmallPlate, 1,
                180, 400, [[3, 7], [12, 25], [12, 30]], self::EGYPT,
                'COMPOSITE DISH — span is wide on purpose. Aubergine absorbs frying oil in amounts that '
                .'vary enormously with pan temperature and whether it was fried or oven-baked.'],

            ['🥬', FoodGroup::Vegetable, 'كرنب محشي', 'Stuffed cabbage', PortionUnit::Piece, 3,
                45, 90, [[1, 3], [7, 14], [1, 3]], self::EGYPT,
                'COMPOSITE DISH — span is wide on purpose, and this is PER ROLL. Rice-to-cabbage ratio and '
                .'the amount of samna differ by household.'],

            ['🍅', FoodGroup::Vegetable, 'طماطم وخيار', 'Tomato and cucumber', PortionUnit::SmallPlate, 1,
                20, 40, [[1, 2], [4, 8], [0, 1]], self::USDA,
                'Plain, undressed.'],

            // ---- Protein.
            ['🫘', FoodGroup::Protein, 'فول مدمس', 'Foul medames', PortionUnit::SmallPlate, 1,
                120, 260, [[8, 14], [17, 30], [2, 12]], self::EGYPT,
                'The span is the oil. Foul served dry and foul served with a generous spoon of oil on top '
                .'are the same dish to a patient and very different figures.'],

            ['🧆', FoodGroup::Protein, 'طعمية', 'Taameya', PortionUnit::Piece, 3,
                55, 95, [[2, 4], [5, 9], [3, 7]], self::EGYPT,
                'PER PIECE. Deep fried, so the span follows oil uptake and how thick the piece was pressed.'],

            ['🍗', FoodGroup::Protein, 'فراخ مشوية', 'Grilled chicken', PortionUnit::Piece, 1,
                140, 260, [[22, 32], [0, 2], [4, 15]], self::USDA,
                'One grilled quarter. The span covers breast against thigh and whether the skin is eaten.'],

            ['🐟', FoodGroup::Protein, 'سمك بلطي', 'Tilapia', PortionUnit::Piece, 1,
                110, 200, [[20, 28], [0, 1], [2, 9]], self::USDA,
                'One medium grilled fish. Fried rather than grilled would be higher than this span allows; '
                .'flagged for the practitioner to decide whether to split the two.'],

            ['🥚', FoodGroup::Protein, 'بيض مسلوق', 'Boiled eggs', PortionUnit::Whole, 2,
                70, 80, [[6, 7], [0, 1], [5, 6]], self::USDA,
                'PER EGG, boiled, so no cooking fat. The tightest span in the file, because a medium egg '
                .'genuinely is a standard measure — but still a span, since egg sizes differ.'],

            ['🍖', FoodGroup::Protein, 'لحمة بتلو', 'Veal', PortionUnit::Piece, 1,
                150, 280, [[20, 30], [0, 2], [6, 18]], self::USDA,
                'One cut, trimmed at the low end and untrimmed at the high end.'],

            // ---- Starch.
            ['🥖', FoodGroup::Starch, 'عيش بلدي', 'Baladi bread', PortionUnit::Loaf, 1,
                180, 260, [[6, 9], [36, 52], [1, 3]], self::EGYPT,
                'PER LOAF. Baladi loaves are not a standard weight; the span covers the sizes actually '
                .'sold rather than a nominal figure.'],

            ['🍚', FoodGroup::Starch, 'رز أبيض', 'White rice', PortionUnit::Cup, 1,
                190, 250, [[4, 6], [40, 53], [0, 4]], self::USDA,
                'Cooked, per cup. The upper end allows the samna or oil rice is usually cooked with here.'],

            ['🍝', FoodGroup::Starch, 'مكرونة', 'Pasta', PortionUnit::Cup, 1,
                190, 260, [[6, 9], [38, 50], [1, 5]], self::USDA,
                'Cooked, per cup, sauce not included.'],

            /*
             * ONE CHIP, DELIBERATELY. DO NOT SPLIT THIS INTO BOILED AND FRIED.
             *
             * Reviewed by the practitioner on 2026-10-12, who was asked whether
             * she wanted them separated and said no. The wide span is the
             * honest thing here: splitting them would ask a patient to classify
             * her own cooking before the tool will talk to her, and the whole
             * point of a household-measures tool is that she answers from
             * memory about a meal she already ate. A span that admits boiled
             * and fried are different is better than a precise figure she had
             * to pick a category to get.
             */
            ['🥔', FoodGroup::Starch, 'بطاطس', 'Potatoes', PortionUnit::SmallPlate, 1,
                110, 350, [[2, 5], [25, 45], [0, 18]], self::USDA,
                'ONE CHIP ON PURPOSE — practitioner decision, 2026-10-12. The widest non-composite span '
                .'in the file and the point of it: boiled potatoes and chips are the same food to a '
                .'patient choosing a chip on this screen, and the span is honest about the difference '
                .'rather than making her classify her own cooking. Do not split this into two foods.'],

            ['🍲', FoodGroup::Starch, 'كشري', 'Koshari', PortionUnit::SmallPlate, 1,
                300, 650, [[10, 20], [55, 110], [5, 20]], self::EGYPT,
                'COMPOSITE DISH — the widest span in the file and the clearest case for having spans at '
                .'all. Rice, pasta, lentils, chickpeas, fried onion and oil, in proportions nobody '
                .'measures. A single figure here would be the most precise-looking and least true number '
                .'in the feature.'],

            // ---- Fat.
            ['🫒', FoodGroup::Fat, 'زيت زيتون', 'Olive oil', PortionUnit::Spoon, 1,
                110, 135, [[0, 0], [0, 0], [12, 15]], self::USDA,
                'PER SPOON. The span is how full the spoon is, which is the only variable.'],

            ['🫙', FoodGroup::Fat, 'طحينة', 'Tahina', PortionUnit::Spoon, 1,
                85, 110, [[2, 4], [2, 4], [7, 10]], self::USDA,
                'PER SPOON, plain tahina paste rather than the thinned salad.'],

            ['🥑', FoodGroup::Fat, 'أفوكادو', 'Avocado', PortionUnit::Whole, 0.5,
                110, 180, [[1, 3], [5, 10], [10, 17]], self::USDA,
                'PER HALF, which is why the default portion is a half rather than one.'],

            /*
             * NAMED NUTS, replacing a generic «مكسرات» entry on the
             * practitioner's instruction, 2026-10-12.
             *
             * The generic chip was flagged in the first draft precisely because
             * almonds and cashews are not interchangeable figures, and a span
             * wide enough to cover every nut would have been uninformative
             * rather than honest.
             *
             * All five are per SPOON, unsalted and unroasted-in-oil. The spans
             * are mostly how heaped the spoon is, which for nuts is a real
             * variable — they do not pack like a powder.
             *
             * ON THE EMOJI: there are exactly two nut glyphs in the whole
             * vocabulary, 🥜 and 🌰, so five nuts cannot each have their own.
             * The peanut emoji goes to the actual peanut and the four tree
             * nuts share the generic one; طحينة moved off 🥜 because sesame
             * paste sitting under a peanut icon next to real peanuts reads as
             * a mistake. The label carries the meaning here, not the picture —
             * the emoji is aria-hidden for exactly that reason.
             *
             * None were dropped. Every value in this file was drafted from
             * memory with no way to check it, so my confidence is uniform
             * across the five and picking a "less confident" one to drop would
             * have been an invented distinction. All five are flagged like
             * everything else; she verifies or deletes them.
             */
            ['🌰', FoodGroup::Fat, 'لوز', 'Almonds', PortionUnit::Spoon, 1,
                55, 75, [[2, 3], [2, 3], [5, 7]], self::USDA,
                'PER SPOON, whole and unsalted.'],

            ['🌰', FoodGroup::Fat, 'عين جمل', 'Walnuts', PortionUnit::Spoon, 1,
                50, 75, [[1, 2], [1, 2], [5, 8]], self::USDA,
                'PER SPOON, halves. The lightest of the five by volume, which is why the span sits low '
                .'despite walnuts being the fattiest here by weight.'],

            ['🌰', FoodGroup::Fat, 'كاجو', 'Cashews', PortionUnit::Spoon, 1,
                55, 80, [[2, 3], [3, 4], [4, 6]], self::USDA,
                'PER SPOON, unsalted. The highest carbohydrate of the five, which is the reason a patient '
                .'might be asked to tell them apart at all.'],

            ['🥜', FoodGroup::Fat, 'فول سوداني', 'Peanuts', PortionUnit::Spoon, 1,
                65, 85, [[3, 4], [2, 3], [5, 7]], self::USDA,
                'PER SPOON, unsalted. The one most actually eaten here, and the densest per spoon because '
                .'it packs tighter than the others.'],

            ['🌰', FoodGroup::Fat, 'بندق', 'Hazelnuts', PortionUnit::Spoon, 1,
                55, 80, [[1, 2], [1, 2], [5, 8]], self::USDA,
                'PER SPOON, whole and unsalted.'],

            // ---- Fruit.
            ['🍊', FoodGroup::Fruit, 'برتقال', 'Orange', PortionUnit::Whole, 1,
                50, 85, [[1, 2], [12, 21], [0, 1]], self::USDA,
                'One medium fruit; the span is fruit size.'],

            ['🍉', FoodGroup::Fruit, 'بطيخ', 'Watermelon', PortionUnit::SmallPlate, 1,
                40, 90, [[1, 2], [10, 22], [0, 1]], self::USDA,
                'A plate of cut watermelon, which is how it is eaten here rather than by the slice.'],

            ['🍌', FoodGroup::Fruit, 'موز', 'Banana', PortionUnit::Whole, 1,
                90, 130, [[1, 2], [23, 33], [0, 1]], self::USDA,
                'One medium banana; the span is fruit size, which for bananas is large.'],

            ['🌴', FoodGroup::Fruit, 'بلح', 'Dates', PortionUnit::Whole, 3,
                20, 30, [[0, 1], [5, 8], [0, 1]], self::USDA,
                'PER DATE, dried. Fresh dates are lower and the practitioner may want them separate.'],

            ['🥭', FoodGroup::Fruit, 'مانجو', 'Mango', PortionUnit::Whole, 1,
                130, 200, [[1, 3], [32, 50], [0, 1]], self::USDA,
                'One fruit. Egyptian varieties differ in size considerably, hence the span.'],

            // ---- Dairy.
            ['🧀', FoodGroup::Dairy, 'جبنة قريش', 'Gebna qareesh', PortionUnit::Spoon, 2,
                25, 40, [[4, 6], [1, 2], [0, 2]], self::EGYPT,
                'PER SPOON. Low fat by nature, so the span is mostly how heaped the spoon is.'],

            ['🥛', FoodGroup::Dairy, 'زبادي', 'Yoghurt', PortionUnit::Cup, 1,
                100, 160, [[5, 9], [8, 14], [2, 8]], self::USDA,
                'Plain, per cup. The span covers full fat against low fat.'],

            ['🍶', FoodGroup::Dairy, 'لبن', 'Milk', PortionUnit::Cup, 1,
                100, 150, [[7, 9], [11, 13], [2, 8]], self::USDA,
                'Per cup. The span covers full fat against low fat.'],
        ];

        foreach ($foods as $index => [$emoji, $group, $ar, $en, $unit, $portion, $min, $max, $macros, $source, $note]) {
            PlateFood::updateOrCreate(
                // Keyed on the Arabic name: re-running the seeder must not
                // duplicate a food, and the Arabic name is what the clinic
                // would recognise it by.
                ['name->ar' => $ar],
                [
                    'name' => ['ar' => $ar, 'en' => $en],
                    'group' => $group,
                    'emoji' => $emoji,
                    'unit' => $unit,
                    'default_portion' => $portion,
                    'energy_min' => $min,
                    'energy_max' => $max,
                    'macros' => [
                        'protein' => $macros[0],
                        'carbs' => $macros[1],
                        'fat' => $macros[2],
                    ],
                    'source' => $source,
                    'source_note' => $note,

                    /*
                     * TRUE, for every food, without exception.
                     *
                     * Not a placeholder to tidy up later: these figures were
                     * drafted from memory with no way to check them, so until
                     * the practitioner has read each one against the named
                     * source they must not reach a patient. The seeder does not
                     * get to decide that a value looks about right.
                     *
                     * Re-running the seeder deliberately RESETS this to true.
                     * If a value changes here, the sign-off it had was for a
                     * different number.
                     */
                    'needs_review' => true,
                    'reviewed_at' => null,
                    'reviewed_by' => null,

                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }

        /*
         * REMOVE FOODS THIS SEEDER NO LONGER LISTS.
         *
         * updateOrCreate only ever adds and updates, so replacing the generic
         * «مكسرات» with five named nuts would otherwise have left the generic
         * chip on the plate beside them — still active, still tappable, and
         * still carrying the span the practitioner asked to be replaced.
         *
         * Safe to prune rather than deactivate because this seeder is the ONLY
         * source for this table: there is no PlateFoodResource in the admin
         * panel, so nothing can have been added by hand and lost here. If a
         * panel for these is ever built, this has to become a deactivation
         * instead — a prune would then silently delete somebody's work.
         */
        $listed = array_map(static fn (array $row): string => $row[2], $foods);

        $removed = PlateFood::query()
            ->whereNotIn('name->ar', $listed)
            ->get();

        foreach ($removed as $food) {
            $this->command?->line('  removed '.$food->getTranslation('name', 'ar', false).' — no longer in the seeder');
            $food->delete();
        }
    }
}
