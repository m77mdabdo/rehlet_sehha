<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

/**
 * Cross-cutting labels.
 *
 * Deliberately few. A tag earns its place by appearing on more than one
 * article and by being something a reader would actually search; anything
 * narrower belongs in the body, not in a taxonomy that has to be maintained.
 */
class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'adherence' => ['ar' => 'الالتزام', 'en' => 'Sticking to a plan'],
            'first-visit' => ['ar' => 'أول زيارة', 'en' => 'First visit'],
            'lab-results' => ['ar' => 'التحاليل', 'en' => 'Lab results'],
            'myths' => ['ar' => 'خرافات شائعة', 'en' => 'Common myths'],
            'questions-to-ask' => ['ar' => 'أسئلة تسأليها', 'en' => 'Questions to ask'],
            'egyptian-kitchen' => ['ar' => 'المطبخ المصري', 'en' => 'The Egyptian kitchen'],
            'supplements' => ['ar' => 'المكملات', 'en' => 'Supplements'],
            'family' => ['ar' => 'العيلة', 'en' => 'Family'],
        ];

        foreach ($tags as $slug => $name) {
            Tag::updateOrCreate(['slug' => $slug], ['name' => $name]);
        }
    }
}
