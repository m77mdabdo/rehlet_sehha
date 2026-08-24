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
                    'ar' => 'معظم المتابعين يلاحظون فرقاً في مستوى الطاقة خلال أول أسبوعين، وتغيراً واضحاً في القياسات وانتظام النوم خلال أربعة إلى ستة أسابيع. المتابعة عندنا بتتقاس بالالتزام والطاقة والتحاليل، مش برقم على الميزان.',
                    'en' => 'Most people notice a difference in energy within the first two weeks, and a clear change in their measurements and sleep within four to six weeks. Progress here is measured by adherence, energy and lab results, not by a number on the scale.',
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

        /*
         * Questions about BUYING, which live on the packages page rather than
         * the homepage. Each is one somebody actually hesitates over with a
         * card in her hand — switching, paying, missing a session, cancelling —
         * and answering them where the decision is made is the difference
         * between a price list and an offer.
         *
         * Written as commitments the clinic can keep. A refund policy invented
         * to sound generous is a policy the front desk has to break.
         */
        $buying = [
            [
                'question' => ['ar' => 'أقدر أغيّر الباقة بعد ما أبدأ؟', 'en' => 'Can I switch package after I start?'],
                'answer' => [
                    'ar' => 'أيوه. لو بدأتي باستشارة فردية وقررتي تكمّلي متابعة، بنحسب سعر الاستشارة من قيمة الباقة الجديدة، فمش هتدفعي مرتين على نفس الجلسة. والعكس برضه: لو باقة الشهر طلعت أطول من احتياجك، بنوقف الباقي ونحسب الجلسات اللي حصلت بس.',
                    'en' => 'Yes. If you start with a single consultation and decide to continue, the consultation fee comes off the price of the package, so you never pay twice for the same session. It works the other way too: if a monthly package turns out to be more than you need, we stop the rest and charge only for the sessions that happened.',
                ],
            ],
            [
                'question' => ['ar' => 'لازم أدفع الباقة كلها مقدّم؟', 'en' => 'Do I have to pay for the whole package upfront?'],
                'answer' => [
                    'ar' => 'لأ. بتدفعي الجلسة الأولى وبس عشان تجرّبي، وبعدها لو كمّلتي بيتقسّم الباقي على دفعتين. الاستشارة الفردية ومراجعة التحاليل بتتدفع في الجلسة نفسها.',
                    'en' => 'No. You pay for the first session only, so you can see how it goes, and if you continue the rest is split across two payments. Single consultations and lab reviews are paid at the session itself.',
                ],
            ],
            [
                'question' => ['ar' => 'بتقبلوا إيه في الدفع؟', 'en' => 'What payment methods do you take?'],
                'answer' => [
                    'ar' => 'كاش في العيادة، أو تحويل إنستاباي أو محفظة موبايل قبل الجلسة الأونلاين. مفيش رسوم إضافية على أي طريقة، وبيوصلك إيصال على الواتساب في الحالتين.',
                    'en' => 'Cash at the clinic, or InstaPay and mobile wallet transfers before an online session. No method carries an extra fee, and a receipt reaches you on WhatsApp either way.',
                ],
            ],
            [
                'question' => ['ar' => 'لو اضطريت ألغي أو أأجّل؟', 'en' => 'What if I need to cancel or reschedule?'],
                'answer' => [
                    'ar' => 'التأجيل مجاني لو قبل الموعد بأربعة وعشرين ساعة، من اللينك اللي في رسالة التأكيد ومن غير ما تكلّمي حد. أقل من كده الجلسة بتتحسب من الباقة، إلا لو في ظرف صحي — كلّمينا وهنتصرّف. الإلغاء قبل ما الباقة تبدأ بيرجّع المبلغ كامل.',
                    'en' => 'Rescheduling is free up to twenty-four hours before, from the link in your confirmation message and without having to speak to anyone. Later than that the session counts against the package, unless something medical came up — message us and we will sort it. Cancelling before a package starts is refunded in full.',
                ],
            ],
            [
                'question' => ['ar' => 'لو فوّتّ جلسة في نص الباقة؟', 'en' => 'What if I miss a session mid-package?'],
                'answer' => [
                    'ar' => 'الجلسات مش بتضيع بسبب التوقيت. مدة الباقة بتتمدّ عشان تستوعب الجلسة اللي اتأجلت، طالما رجعتي في خلال شهر. اللي بيوقف الخطة مش جلسة اتأخرت، ده انقطاع طويل من غير تواصل.',
                    'en' => 'Sessions are not lost to the calendar. The package extends to absorb a delayed session as long as you come back within a month. What derails a plan is a long silence, not a late appointment.',
                ],
            ],
            [
                'question' => ['ar' => 'الأسعار دي نهائية؟', 'en' => 'Are these prices final?'],
                'answer' => [
                    'ar' => 'أيوه. السعر المكتوب هو اللي بتدفعيه — مفيش رسوم فتح ملف ولا رسوم حجز ولا فرق سعر بين الأونلاين والعيادة. التحاليل نفسها بتتعمل في معمل خارجي وبتدفعيها هناك، واللي بناخد عليه مقابل هو مراجعتها معاكي.',
                    'en' => 'Yes. The listed price is what you pay — no file-opening fee, no booking fee, and no difference between online and in-clinic. Lab tests themselves are done at an external laboratory and paid there; what we charge for is reviewing them with you.',
                ],
            ],
        ];

        /*
         * Keyed on category AND sort_order rather than on the question text: a
         * JSON-path key such as 'question->en' works in a WHERE clause but
         * updateOrCreate would then try to write a literal column of that name
         * on insert.
         *
         * Each category numbers from one, so adding a buying question never
         * renumbers a general one — and the rows that predate the category
         * column match here because the migration defaulted them to 'general'.
         */
        foreach ([Faq::CATEGORY_GENERAL => $faqs, Faq::CATEGORY_BUYING => $buying] as $category => $set) {
            foreach ($set as $index => $faq) {
                Faq::updateOrCreate(
                    ['category' => $category, 'sort_order' => $index + 1],
                    $faq + ['is_active' => true],
                );
            }
        }
    }
}
