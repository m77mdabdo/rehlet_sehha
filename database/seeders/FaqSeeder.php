<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => [
                    'ar' => 'هل الاستشارة متاحة أونلاين؟',
                    'en' => 'Is the consultation available online?',
                ],
                'answer' => [
                    'ar' => 'نعم. اختر «استشارة عن بُعد» عند الحجز وسيصلك رابط الجلسة برسالة قبل الموعد بساعة. الجلسة أونلاين لها نفس مدة ونفس سعر جلسة العيادة.',
                    'en' => 'Yes. Choose “remote consultation” when booking and the session link arrives by message an hour beforehand. Online sessions have the same length and the same price as clinic visits.',
                ],
            ],
            [
                'question' => [
                    'ar' => 'ماذا أحضر معي في الجلسة الأولى؟',
                    'en' => 'What should I bring to the first session?',
                ],
                'answer' => [
                    'ar' => 'أحضر أحدث تحاليل أجريتها خلال الستة أشهر الماضية إن وُجدت، وقائمة بالأدوية أو المكملات التي تتناولها. إذا لم تكن لديك تحاليل فلا مشكلة، سنحدد معاً ما إذا كنت تحتاج إليها.',
                    'en' => 'Bring any lab work from the last six months if you have it, plus a list of the medications or supplements you take. If you have no recent labs that is fine — we will decide together whether you need them.',
                ],
            ],
            [
                'question' => [
                    'ar' => 'هل الخطة الغذائية تناسب ميزانيتي؟',
                    'en' => 'Will the plan fit my budget?',
                ],
                'answer' => [
                    'ar' => 'الخطة تُبنى على الأصناف المتوفرة في السوق المصري وبأسعار معقولة. لا نعتمد على مكملات مستوردة أو منتجات باهظة، وكل صنف في الخطة له بديل أو أكثر.',
                    'en' => 'Plans are built around what is actually available in the Egyptian market at reasonable prices. We do not rely on imported supplements or expensive products, and every item on the plan has at least one substitute.',
                ],
            ],
            [
                'question' => [
                    'ar' => 'كم من الوقت أحتاج لرؤية نتيجة؟',
                    'en' => 'How long before I see results?',
                ],
                'answer' => [
                    'ar' => 'معظم المتابعين يلاحظون فرقاً في مستوى الطاقة خلال أول أسبوعين، وتغيراً واضحاً في الوزن والقياسات خلال أربعة إلى ستة أسابيع. المعدل الصحي هو نصف إلى كيلو أسبوعياً.',
                    'en' => 'Most people notice a difference in energy within the first two weeks, and a clear change in weight and measurements within four to six weeks. A healthy rate is half a kilo to a kilo per week.',
                ],
            ],
            [
                'question' => [
                    'ar' => 'هل يمكنني تغيير موعدي أو إلغاؤه؟',
                    'en' => 'Can I reschedule or cancel my appointment?',
                ],
                'answer' => [
                    'ar' => 'نعم. ستجد رابط الإلغاء في رسالة تأكيد الحجز، ويمكنك استخدامه حتى قبل الموعد بأربع وعشرين ساعة. بعد ذلك يُرجى الاتصال بالعيادة مباشرة.',
                    'en' => 'Yes. Your booking confirmation message contains a cancellation link you can use up to twenty-four hours before the appointment. After that, please call the clinic directly.',
                ],
            ],
            [
                'question' => [
                    'ar' => 'هل تتعاملون مع حالات مرضية مثل السكري وتكيس المبايض؟',
                    'en' => 'Do you handle conditions such as diabetes and PCOS?',
                ],
                'answer' => [
                    'ar' => 'نعم، ويُبنى البرنامج في هذه الحالات بالتنسيق مع طبيبك المعالج. التغذية العلاجية جزء من الخطة ولا تحل محل العلاج الدوائي الذي وصفه لك طبيبك.',
                    'en' => 'Yes, and in those cases the programme is built in coordination with your treating physician. Therapeutic nutrition is part of the plan and does not replace the medication your doctor has prescribed.',
                ],
            ],
        ];

        // Keyed on sort_order rather than on the question text: a JSON-path
        // key such as 'question->en' works in a WHERE clause but updateOrCreate
        // would then try to write a literal column of that name on insert.
        foreach ($faqs as $index => $faq) {
            Faq::updateOrCreate(
                ['sort_order' => $index + 1],
                $faq + ['is_active' => true],
            );
        }
    }
}
