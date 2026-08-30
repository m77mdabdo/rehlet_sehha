<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * The article categories.
 *
 * They mirror the clinical areas the practice works in, because that is how a
 * reader looks for something — she arrives searching her own diagnosis, not an
 * editorial theme. They are NOT the `specialties` rows: a specialty is a
 * service with persuasive copy and a place in a price list, and a category
 * index needs one plain sentence saying what a reader will find.
 *
 * Ordered by how often the clinic is asked about them, not alphabetically.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'weight-management',
                'name' => ['ar' => 'إدارة الوزن', 'en' => 'Weight management'],
                'description' => [
                    'ar' => 'مقالات عن الالتزام، والوقت، والحاجات اللي بتخلي خطة تكمل أو تقف. من غير أرقام تتحاسبي عليها.',
                    'en' => 'On sticking to a plan, on time, and on what makes one last or stop. Without numbers to be measured against.',
                ],
                'meta_description' => [
                    'ar' => 'مقالات عن إدارة الوزن من عيادة رحلة صحة: ليه الخطط بتقف، وإيه اللي بيتقاس غير الميزان.',
                    'en' => 'Weight management articles from Rehlet Sehha: why plans stall, and what gets measured besides the scale.',
                ],
            ],
            [
                'slug' => 'medical-nutrition',
                'name' => ['ar' => 'التغذية العلاجية', 'en' => 'Medical nutrition'],
                'description' => [
                    'ar' => 'لما الدكتور يقول «ظبطي أكلك» — إيه اللي بيقصده، وإيه اللي بيحصل في جلسة تغذية إكلينيكية.',
                    'en' => 'When a doctor says "fix your diet" — what that means, and what happens in a clinical nutrition session.',
                ],
                'meta_description' => [
                    'ar' => 'التغذية العلاجية: يعني إيه، ومين بيحتاجها، وإيه الفرق بينها وبين خطة جاهزة.',
                    'en' => 'Medical nutrition: what it is, who needs it, and how it differs from a template plan.',
                ],
            ],
            [
                'slug' => 'lab-review',
                'name' => ['ar' => 'قراءة التحاليل', 'en' => 'Reading lab results'],
                'description' => [
                    'ar' => 'إيه معنى «النطاق الطبيعي»، وليه تحليل واحد مش بيحكي القصة. من غير أرقام ولا نطاقات تقارني نفسك بيها.',
                    'en' => 'What a reference range is, and why one test does not tell the story. Without figures to measure yourself against.',
                ],
                'meta_description' => [
                    'ar' => 'إزاي تتقري نتيجة تحليل: معنى النطاق الطبيعي، والاتجاه عبر الوقت، وإمتى تسألي.',
                    'en' => 'How a lab result is read: what a reference range means, trends over time, and when to ask.',
                ],
            ],
            [
                'slug' => 'pcos-hormonal',
                'name' => ['ar' => 'تكيس المبايض والهرمونات', 'en' => 'PCOS and hormones'],
                'description' => [
                    'ar' => 'إزاي تحكمي على ادعاء عن تكيس المبايض والأكل، وإيه الأسئلة اللي تستاهل تتسأل لدكتورتك.',
                    'en' => 'How to judge a claim about PCOS and food, and the questions worth asking your doctor.',
                ],
                'meta_description' => [
                    'ar' => 'تكيس المبايض والأكل: إزاي تفرّقي بين ادعاء وادعاء، ومين بيقرر الخطة.',
                    'en' => 'PCOS and food: how to tell one claim from another, and who decides the plan.',
                ],
            ],
            [
                'slug' => 'pregnancy-nutrition',
                'name' => ['ar' => 'تغذية الحمل والرضاعة', 'en' => 'Pregnancy and feeding'],
                'description' => [
                    'ar' => 'الخرافات اللي بتتقال في كل بيت، والأسئلة اللي تستاهل تروح بيها لدكتورة النسا.',
                    'en' => 'The things everyone tells you, and the questions worth taking to your obstetrician.',
                ],
                'meta_description' => [
                    'ar' => 'تغذية الحمل والرضاعة: خرافات شائعة، وإمتى الكلام ده يبقى سؤال لدكتورتك.',
                    'en' => 'Pregnancy and feeding: common myths, and when a question belongs with your doctor.',
                ],
            ],
            [
                'slug' => 'child-nutrition',
                'name' => ['ar' => 'تغذية الأطفال', 'en' => 'Child nutrition'],
                'description' => [
                    'ar' => 'الطفل اللي «مش بياكل»: إيه اللي بيحصل فعلاً، ومين بيشوفه الأول.',
                    'en' => 'The child who "will not eat": what is usually happening, and who sees them first.',
                ],
                'meta_description' => [
                    'ar' => 'تغذية الأطفال: الأكل والضغط على السفرة، وإمتى تروحي لدكتور الأطفال.',
                    'en' => 'Child nutrition: mealtime pressure, and when to see the paediatrician.',
                ],
            ],
            [
                'slug' => 'sports-nutrition',
                'name' => ['ar' => 'التغذية الرياضية', 'en' => 'Sports nutrition'],
                'description' => [
                    'ar' => 'الأكل حوالين التمرين: إيه اللي مكتوب للرياضيين المحترفين، وإيه اللي ينفع لحد بيتمرن تلات مرات في الأسبوع.',
                    'en' => 'Eating around training: what is written for competitive athletes, and what applies to somebody training three times a week.',
                ],
                'meta_description' => [
                    'ar' => 'التغذية الرياضية: الفرق بين نصيحة للمحترفين وواحدة تنفعك، والمكملات.',
                    'en' => 'Sports nutrition: advice written for professionals versus advice that fits you, and supplements.',
                ],
            ],
        ];

        foreach ($categories as $order => $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category + ['sort_order' => $order, 'is_active' => true],
            );
        }
    }
}
