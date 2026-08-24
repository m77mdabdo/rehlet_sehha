<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\FoodGroup;
use App\Models\PlateFood;
use Illuminate\Database\Seeder;

/**
 * The foods on the plate builder.
 *
 * Real Egyptian food, not a generic international list. A tool that offers
 * quinoa and kale to somebody who eats foul and baladi bread is not teaching
 * her anything about her own plate — it is teaching her that her food is not
 * what the chart means, which is the opposite of the point.
 *
 * NO NUMBERS ANYWHERE IN THIS FILE. No calories, no grams, no portion sizes.
 * See App\Models\PlateFood for why that is the feature rather than an omission.
 */
class PlateFoodSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [
            // Vegetables — the group the feedback most often asks for more of.
            ['🥗', FoodGroup::Vegetable, 'سلطة خضراء', 'Green salad'],
            ['🌿', FoodGroup::Vegetable, 'ملوخية', 'Molokhia'],
            ['🥒', FoodGroup::Vegetable, 'طرشي', 'Torshi (pickles)'],
            ['🍆', FoodGroup::Vegetable, 'مسقعة', 'Mesaqaa (aubergine)'],
            ['🥬', FoodGroup::Vegetable, 'كرنب محشي', 'Stuffed cabbage'],
            ['🍅', FoodGroup::Vegetable, 'طماطم وخيار', 'Tomato and cucumber'],

            // Protein.
            ['🫘', FoodGroup::Protein, 'فول مدمس', 'Foul medames'],
            ['🧆', FoodGroup::Protein, 'طعمية', 'Taameya'],
            ['🍗', FoodGroup::Protein, 'فراخ مشوية', 'Grilled chicken'],
            ['🐟', FoodGroup::Protein, 'سمك بلطي', 'Tilapia'],
            ['🥚', FoodGroup::Protein, 'بيض مسلوق', 'Boiled eggs'],
            ['🍖', FoodGroup::Protein, 'لحمة بتلو', 'Veal'],

            // Starch.
            ['🥖', FoodGroup::Starch, 'عيش بلدي', 'Baladi bread'],
            ['🍚', FoodGroup::Starch, 'رز أبيض', 'White rice'],
            ['🍝', FoodGroup::Starch, 'مكرونة', 'Pasta'],
            ['🥔', FoodGroup::Starch, 'بطاطس', 'Potatoes'],
            ['🍲', FoodGroup::Starch, 'كشري', 'Koshari'],

            // Fat.
            ['🫒', FoodGroup::Fat, 'زيت زيتون', 'Olive oil'],
            ['🥜', FoodGroup::Fat, 'طحينة', 'Tahina'],
            ['🥑', FoodGroup::Fat, 'أفوكادو', 'Avocado'],
            ['🌰', FoodGroup::Fat, 'مكسرات', 'Nuts'],

            // Fruit.
            ['🍊', FoodGroup::Fruit, 'برتقال', 'Orange'],
            ['🍉', FoodGroup::Fruit, 'بطيخ', 'Watermelon'],
            ['🍌', FoodGroup::Fruit, 'موز', 'Banana'],
            ['🌴', FoodGroup::Fruit, 'بلح', 'Dates'],
            ['🥭', FoodGroup::Fruit, 'مانجو', 'Mango'],

            // Dairy.
            ['🧀', FoodGroup::Dairy, 'جبنة قريش', 'Gebna qareesh'],
            ['🥛', FoodGroup::Dairy, 'زبادي', 'Yoghurt'],
            ['🍶', FoodGroup::Dairy, 'لبن', 'Milk'],
        ];

        foreach ($foods as $index => [$emoji, $group, $ar, $en]) {
            PlateFood::updateOrCreate(
                // Keyed on the Arabic name: re-running the seeder must not
                // duplicate a food, and the Arabic name is what the clinic
                // would recognise it by.
                ['name->ar' => $ar],
                [
                    'name' => ['ar' => $ar, 'en' => $en],
                    'group' => $group,
                    'emoji' => $emoji,
                    'sort_order' => $index,
                    'is_active' => true,
                ],
            );
        }
    }
}
