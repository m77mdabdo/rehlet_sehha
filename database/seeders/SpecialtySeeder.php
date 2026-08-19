<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

/**
 * The eight clinical areas the practice covers.
 *
 * These are not bookable and carry no price — see the specialties migration
 * for why they are a separate table from `services`. A visitor reads this list
 * to answer "do they handle my situation?", then books a consultation from the
 * packages section.
 *
 * The Arabic is Egyptian and direct, matching the rest of the site: "تكيس
 * المبايض" rather than a clinical transliteration, because that is what a
 * woman searching for help actually types.
 */
class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            [
                'slug' => 'medical-nutrition',
                'icon' => 'stethoscope',
                'name' => [
                    'ar' => 'التغذية العلاجية',
                    'en' => 'Medical Nutrition',
                ],
                'description' => [
                    'ar' => 'خطط غذائية للحالات المرضية — السكري، الضغط، القولون، الكبد الدهني — متظبطة على حالتك وتحاليلك.',
                    'en' => 'Nutrition plans for medical conditions — diabetes, blood pressure, IBS, fatty liver — built around your case and your lab work.',
                ],
            ],
            [
                'slug' => 'weight-management',
                'icon' => 'target',
                'name' => [
                    'ar' => 'إدارة الوزن',
                    'en' => 'Weight Management',
                ],
                'description' => [
                    'ar' => 'خطة تقدري تكمّلي عليها من أكل بيتك، بمتابعة تظبط معاكِ لحد ما تبقى عادة مش نظام.',
                    'en' => 'A plan you can stay on, built from the food already in your kitchen, adjusted with you until it becomes a habit rather than a diet.',
                ],
            ],
            [
                'slug' => 'pregnancy-nutrition',
                'icon' => 'heart',
                'name' => [
                    'ar' => 'تغذية الحمل والرضاعة',
                    'en' => 'Pregnancy & Breastfeeding',
                ],
                'description' => [
                    'ar' => 'متابعة غذائية خلال الحمل والرضاعة، مع الانتباه للحديد وفيتامين د وسكر الحمل.',
                    'en' => 'Nutritional care through pregnancy and breastfeeding, with attention to iron, vitamin D and gestational diabetes.',
                ],
            ],
            [
                'slug' => 'sports-nutrition',
                'icon' => 'bolt',
                'name' => [
                    'ar' => 'التغذية الرياضية',
                    'en' => 'Sports Nutrition',
                ],
                'description' => [
                    'ar' => 'تغذية حول التمرين لبناء العضل والاستشفاء، من غير مكملات مش محتاجها.',
                    'en' => 'Nutrition around training for muscle gain and recovery, without supplements you do not need.',
                ],
            ],
            [
                'slug' => 'child-nutrition',
                'icon' => 'smile',
                'name' => [
                    'ar' => 'تغذية الأطفال',
                    'en' => 'Child Nutrition',
                ],
                'description' => [
                    'ar' => 'من بداية الطعام الصلب للمراهقة — الأكل الانتقائي، الأنيميا، والنمو.',
                    'en' => 'From first solids to adolescence — picky eating, anaemia, and growth.',
                ],
            ],
            [
                'slug' => 'pcos-hormonal',
                'icon' => 'cycle',
                'name' => [
                    'ar' => 'تكيس المبايض والهرمونات',
                    'en' => 'PCOS & Hormonal Health',
                ],
                'description' => [
                    'ar' => 'تغذية بتساعد على ضبط مقاومة الإنسولين وانتظام الدورة، جنب علاج دكتورك.',
                    'en' => 'Nutrition that supports insulin resistance and cycle regularity, alongside your doctor’s treatment.',
                ],
            ],
            [
                'slug' => 'lab-review',
                'icon' => 'flask',
                'name' => [
                    'ar' => 'قراءة التحاليل',
                    'en' => 'Lab Review',
                ],
                'description' => [
                    'ar' => 'نقرا تحاليلك مع بعض ونترجمها لخطوات في الأكل، مش أرقام على ورقة.',
                    'en' => 'We read your results together and turn them into changes on your plate, not numbers on a page.',
                ],
            ],
            [
                'slug' => 'corporate-wellness',
                'icon' => 'briefcase',
                'name' => [
                    'ar' => 'برامج الشركات',
                    'en' => 'Corporate Wellness',
                ],
                'description' => [
                    'ar' => 'جلسات توعية وبرامج متابعة لفرق العمل، أونلاين أو في مقر الشركة.',
                    'en' => 'Awareness sessions and follow-up programmes for teams, online or on site.',
                ],
            ],
        ];

        foreach ($specialties as $index => $specialty) {
            Specialty::updateOrCreate(
                ['slug' => $specialty['slug']],
                $specialty + ['is_active' => true, 'sort_order' => $index + 1],
            );
        }

        $this->command?->info(sprintf('Seeded %d specialties.', count($specialties)));
    }
}
