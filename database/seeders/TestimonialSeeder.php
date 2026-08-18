<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'منى عبد الرحمن',
                'initials' => 'م ع',
                'context' => [
                    'ar' => 'برنامج ثلاثة أشهر',
                    'en' => 'Three months programme',
                ],
                'quote' => [
                    'ar' => 'أول مرة ألتزم بخطة غذائية لأكثر من شهر. الخطة كانت من أكل بيتي عادي ومش محتاجة حاجات غالية، ونزلت أربعة عشر كيلو في ثلاثة شهور من غير ما أحس إني محرومة.',
                    'en' => 'The first time I have stuck to a plan for more than a month. It was built from ordinary home cooking with nothing expensive, and I lost fourteen kilos in three months without ever feeling deprived.',
                ],
                'rating' => 5,
            ],
            [
                'name' => 'أحمد الشناوي',
                'initials' => 'أ ش',
                'context' => [
                    'ar' => 'متابعة شهر — تغذية رياضية',
                    'en' => 'One month — sports nutrition',
                ],
                'quote' => [
                    'ar' => 'كنت بتمرن من سنتين من غير نتيجة واضحة. بعد تعديل توزيع الوجبات حوالين مواعيد التمرين، الفرق بان في الأداء وفي القياسات خلال أربع أسابيع.',
                    'en' => 'I had been training for two years with no clear result. After we adjusted how my meals were spread around training times, the difference showed in both performance and measurements within four weeks.',
                ],
                'rating' => 5,
            ],
            [
                'name' => 'سارة إبراهيم',
                'initials' => 'س إ',
                'context' => [
                    'ar' => 'تكيس المبايض — متابعة أونلاين',
                    'en' => 'PCOS — online follow-up',
                ],
                'quote' => [
                    'ar' => 'بحكم شغلي بالورديات كنت فاكرة إن أي نظام مستحيل ينفع معايا. الجلسات كانت أونلاين ومرنة، والخطة اتعملت على مواعيدي أنا مش العكس، والتحاليل اتحسنت بعد شهرين.',
                    'en' => 'Working shifts, I assumed no plan could ever fit my life. The sessions were online and flexible, the plan was built around my hours rather than the other way round, and my labs improved after two months.',
                ],
                'rating' => 5,
            ],
        ];

        foreach ($testimonials as $index => $testimonial) {
            Testimonial::updateOrCreate(
                ['sort_order' => $index + 1],
                $testimonial + ['is_active' => true],
            );
        }
    }
}
