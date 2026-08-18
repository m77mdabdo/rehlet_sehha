<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'slug' => 'single-consultation',
                'name' => [
                    'ar' => 'استشارة تغذية فردية',
                    'en' => 'Single Nutrition Consultation',
                ],
                'subtitle' => [
                    'ar' => 'جلسة واحدة لتقييم حالتك ووضع خطة',
                    'en' => 'One session to assess your case and set a plan',
                ],
                'description' => [
                    'ar' => 'جلسة تقييم شاملة نراجع فيها تاريخك الصحي وعاداتك الغذائية وتحاليلك إن وُجدت، وتخرج منها بخطة غذائية مكتوبة مناسبة لميزانيتك وروتين يومك.',
                    'en' => 'A full assessment session covering your medical history, eating habits and any recent lab work. You leave with a written plan that fits your budget and your daily routine.',
                ],
                'features' => [
                    'ar' => [
                        'تقييم كامل للحالة والتاريخ الصحي',
                        'قياس ومناقشة التركيب الجسمي',
                        'خطة غذائية مكتوبة لمدة أسبوعين',
                        'قائمة بدائل للأصناف الأساسية',
                    ],
                    'en' => [
                        'Full case and medical history assessment',
                        'Body composition measurement and discussion',
                        'Written two-week nutrition plan',
                        'Substitution list for core items',
                    ],
                ],
                'price' => 600.00,
                'duration_minutes' => 45,
                'sessions_count' => 1,
                'sort_order' => 1,
            ],
            [
                'slug' => 'one-month-programme',
                'name' => [
                    'ar' => 'برنامج متابعة شهر',
                    'en' => 'One Month Programme',
                ],
                'subtitle' => [
                    'ar' => 'أربع جلسات متابعة أسبوعية',
                    'en' => 'Four weekly follow-up sessions',
                ],
                'description' => [
                    'ar' => 'برنامج شهر كامل بأربع جلسات أسبوعية نضبط فيها الخطة حسب استجابة جسمك، مع متابعة عبر واتساب بين الجلسات للأسئلة السريعة.',
                    'en' => 'A full month with four weekly sessions in which we adjust the plan to how your body actually responds, plus WhatsApp follow-up between sessions for quick questions.',
                ],
                'features' => [
                    'ar' => [
                        'أربع جلسات متابعة أسبوعية',
                        'تعديل الخطة حسب النتائج',
                        'متابعة عبر واتساب بين الجلسات',
                        'قياس دوري للتركيب الجسمي',
                    ],
                    'en' => [
                        'Four weekly follow-up sessions',
                        'Plan adjusted to your results',
                        'WhatsApp support between sessions',
                        'Regular body composition tracking',
                    ],
                ],
                'price' => 1500.00,
                'duration_minutes' => 45,
                'sessions_count' => 4,
                'sort_order' => 2,
            ],
            [
                'slug' => 'three-months-programme',
                'name' => [
                    'ar' => 'برنامج متابعة ثلاثة أشهر',
                    'en' => 'Three Months Programme',
                ],
                'subtitle' => [
                    'ar' => 'اثنتا عشرة جلسة لتغيير يدوم',
                    'en' => 'Twelve sessions for change that lasts',
                ],
                'description' => [
                    'ar' => 'البرنامج الأنسب لمن يريد نتيجة ثابتة لا مؤقتة. اثنتا عشرة جلسة على مدى ثلاثة أشهر نبني فيها عادات غذائية مستقرة، مع مرحلة تثبيت في الشهر الأخير حتى لا يعود الوزن.',
                    'en' => 'The right fit for anyone after a lasting result rather than a temporary one. Twelve sessions across three months to build stable habits, with a maintenance phase in the final month so the weight does not come back.',
                ],
                'features' => [
                    'ar' => [
                        'اثنتا عشرة جلسة متابعة',
                        'مرحلة تثبيت الوزن في الشهر الأخير',
                        'مراجعة التحاليل الدورية',
                        'متابعة مستمرة عبر واتساب',
                        'خطة تغذية للمناسبات والسفر',
                    ],
                    'en' => [
                        'Twelve follow-up sessions',
                        'Weight maintenance phase in the final month',
                        'Periodic lab work review',
                        'Continuous WhatsApp support',
                        'Eating plan for events and travel',
                    ],
                ],
                'price' => 3900.00,
                'duration_minutes' => 45,
                'sessions_count' => 12,
                'sort_order' => 3,
            ],
            [
                'slug' => 'lab-review',
                'name' => [
                    'ar' => 'مراجعة تحاليل',
                    'en' => 'Lab Review',
                ],
                'subtitle' => [
                    'ar' => 'جلسة قصيرة لقراءة نتائج تحاليلك',
                    'en' => 'A short session to read your lab results',
                ],
                'description' => [
                    'ar' => 'جلسة مركزة لقراءة نتائج تحاليلك وشرح ما تعنيه من الناحية الغذائية، مع توصيات عملية وتحديد ما إذا كنت تحتاج إلى برنامج متابعة كامل.',
                    'en' => 'A focused session to read your lab results and explain what they mean nutritionally, with practical recommendations and a clear answer on whether you need a full follow-up programme.',
                ],
                'features' => [
                    'ar' => [
                        'قراءة نتائج التحاليل',
                        'شرح الدلالات الغذائية',
                        'توصيات عملية مباشرة',
                    ],
                    'en' => [
                        'Reading of your lab results',
                        'Explanation of the nutritional implications',
                        'Direct, practical recommendations',
                    ],
                ],
                'price' => 400.00,
                'duration_minutes' => 25,
                'sessions_count' => 1,
                'sort_order' => 4,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['slug' => $service['slug']],
                $service + ['currency' => 'EGP', 'is_active' => true],
            );
        }
    }
}
