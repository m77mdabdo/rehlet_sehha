<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * The most practical of the fourteen, and the one that most improves the
 * appointment it is about.
 *
 * The biotin section is the reason this article is worth more than a checklist.
 * High-dose biotin is sold across Egyptian pharmacies as a hair and nail
 * supplement, is not thought of as a medicine by anybody taking it, and can
 * interfere with immunoassays — including thyroid tests. Somebody can be
 * investigated for a thyroid problem she does not have because of a capsule
 * she did not think to mention. That is a concrete, checkable, genuinely
 * useful thing to tell a reader, and it makes the case for "bring everything"
 * far better than the instruction alone.
 */
class WhatToBringToAFirstAppointment extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'what-to-bring-to-a-first-appointment',
            'category' => 'lab-review',
            'tags' => ['first-visit', 'questions-to-ask'],
            'cover' => 'packing-papers-into-bag',

            'title' => [
                'ar' => 'إيه اللي تجيبيه معاكي في أول زيارة؟',
                'en' => 'What to bring to a first appointment',
            ],

            'excerpt' => [
                'ar' => 'نص الكشف الأول بيروح في تجميع معلومات كانت ممكن تيجي جاهزة. القايمة دي بتوفّر الوقت ده لحاجة أهم.',
                'en' => 'Half of a first consultation goes into assembling information that could have arrived ready. This list frees that time for something more useful.',
            ],

            'body' => [
                'ar' => <<<'AR'
أول كشف بيحصل فيه حاجة متكررة: نص الوقت بيروح في تجميع معلومات كانت ممكن تيجي جاهزة. تحليل معمول من ستة شهور موجود في درج في البيت، واسم دوا الشخص مش فاكره بالظبط، ومكمل بيتاخد كل يوم محدش اعتبره «دوا».

المقال ده قايمة عملية، وورا كل بند فيها سبب.

## اللي بيحصل فعلًا في أول كشف

التقييم الغذائي الجدي مش سؤال «بتاكلي إيه؟». هو تجميع صورة: التاريخ المرضي، الأدوية، نتايج التحاليل لو موجودة، شكل اليوم، تاريخ المحاولات السابقة، والتفضيلات والميزانية.

الجمعية الأمريكية للسكري في معاييرها السنوية للرعاية بتوصف التقييم الطبي الشامل كأساس لأي خطة، وبتحط مراجعة الأدوية كجزء أصيل منه. والجمعية الأوروبية للتغذية السريرية والأيض (ESPEN) عندها دليل مخصص لتعريفات ومصطلحات التغذية السريرية، وفيه إن التقييم خطوة مستقلة قبل التدخل.

يعني المعلومة اللي هاتيها مش «إجراءات» — هي المادة الخام اللي الخطة بتتبني عليها.

## أول حاجة: الأدوية والمكملات، كلها

اكتبي أو صوّري كل حاجة بتتاخد بانتظام. الأدوية الموصوفة، والأدوية اللي بتتاخد من غير روشتة، والفيتامينات، والمكملات، والأعشاب.

وده أهم بند في القايمة كلها، لسببين.

الأول إن في أدوية بتأثر على الأكل والوزن مباشرة: بتغيّر الشهية، أو بتأثر على امتصاص عناصر معيّنة، أو بيبقى توقيتها مربوط بالوجبات. المعهد الوطني البريطاني (NICE) في إرشاداته عن الاستخدام الأمثل للأدوية بيوصي بمطابقة قايمة الأدوية عند أي انتقال في الرعاية — يعني ببساطة: الشخص الجديد اللي بيشوفك لازم يعرف بالظبط إنتِ بتاخدي إيه.

والسبب التاني أدق وبيتفاجئ بيه ناس كتير: في مكملات بتأثر على نتايج التحاليل نفسها.

## المكملات ممكن تغيّر نتيجة التحليل — مثال حقيقي

البيوتين، اللي بيتباع في الصيدليات كمكمل للشعر والأظافر بجرعات عالية، ممكن يتداخل مع أنواع من تحاليل الدم اللي بتشتغل بتقنية معيّنة — ومنها تحاليل الغدة الدرقية.

الإدارة الأمريكية للغذاء والدواء (FDA) أصدرت تنبيه سلامة عن التداخل ده، لأن نتيجة متأثرة ممكن تتقري على إنها مشكلة في الغدة مش موجودة أصلًا — أو تخفي مشكلة موجودة.

والمكمل ده بالذات محدش بيعتبره دوا. هو «فيتامين للشعر»، وبيتاخد من غير روشتة، وبينتسي في العد.

مش المطلوب إن حد يقلق من مكملاته. المطلوب إنها تتقال، عشان اللي بيقرا النتيجة يبقى عارف.

CLINICAL_INPUT: في مكملات إنتِ بتسألي عنها بالاسم في أول كشف لأنها بتأثر على التحاليل أو على الخطة؟

## وليه المكمل مش «حاجة بسيطة»

في اعتقاد شائع إن المكمل أسوأ حاجة ممكن يعملها إنه ميعملش حاجة. وده مش دقيق.

فيتامينات زي أ ود وهـ وك بتذوب في الدهون، يعني الجسم بيخزنها بدل ما يتخلص من الزيادة في البول زي الفيتامينات اللي بتذوب في المية. الفرق ده مهم: الزيادة المستمرة في النوع ده بتتراكم مع الوقت.

وفي تداخلات بين المكملات نفسها. الكالسيوم بياخد نفس المسارات اللي الحديد بيتمتص بيها وبيزاحمه، فمكمل كالسيوم بيتاخد مع مكمل حديد بيقلل الاستفادة من الاتنين. والزنك والنحاس بينهم نفس النوع من المزاحمة.

وفي تداخلات بين المكملات والأدوية. أشهر مثال معروف هو فيتامين ك ومضادات التخثر، لأن الدوا ده بيشتغل على نفس المسار بالظبط.

ومع ده كله، المكملات في مصر بتتباع من غير روشتة، وبتتاخد بناءً على نصيحة من قريب أو إعلان، ولفترات طويلة. مش كل ده مشكلة — بس كله لازم يتعرف.

## تاني حاجة: الورق

كل تحليل أو أشعة أو تقرير من آخر سنة على الأقل، حتى لو حد قال إنه «تمام».

السبب إن النتيجة الواحدة بتوصف لحظة، والنتيجتين بيوصفوا اتجاه. رقم عند الحافة السفلى نازل من رقم أعلى السنة اللي فاتت حاجة، ونفس الرقم ثابت من ثلاث سنين حاجة تانية خالص — وشكلهم واحد في ورقة واحدة.

والصورة بالموبايل تنفع. مش لازم الأصل.

ولو في تشخيص مزمن — سكري، ضغط، غدة، قولون، تكيس مبايض، حساسية قمح — هاتي آخر تقرير عنه.

ولو التحاليل اتعملت في معامل مختلفة، قولي ده كمان. النطاقات المرجعية بتختلف من معمل لمعمل حسب الجهاز والطريقة، فمقارنة رقم من هنا برقم من هناك محتاجة انتباه.

## تالت حاجة: يوم أكل حقيقي

اكتبي أكل يومين أو تلاتة قبل الميعاد، ساعة بساعة تقريبًا.

وأهم شرط: يوم عادي، مش يوم مثالي. اليوم المثالي بيدي خطة لشخص مش موجود.

اكتبي كمان الحاجات اللي مش بتتحسب أكل عادة: الشاي بالسكر، العصاير، المشروبات الغازية، اللقمة أثناء الطبخ، وأكل الشغل.

## رابع حاجة: تاريخ اللي جربتيه قبل كده

إيه اللي اتجرب، وإيه اللي نفع، وإيه اللي وقف، وليه وقف.

دي المعلومة اللي بتوفر شهور. الخطة اللي شبه حاجة مانفعتش قبل كده هتقف بنفس الطريقة، ومعرفة ليه وقفت بتغيّر التصميم من أول يوم.

ولو في محاولة وقفت بسبب عرض — دوخة، صداع، إمساك، جوع مستمر، انخفاض في الطاقة — ده بالذات يتقال. العرض ده معلومة إكلينيكية، مش شكوى.

## خامس حاجة: أسئلتك، مكتوبة

الأسئلة بتتنسى في الكشف. اكتبيها على ورقة أو في الموبايل.

ولو الجملة اللي جابتك هي «ظبّطي أكلك» من غير تفاصيل، في مقال عن معناها: [[article:what-fix-your-diet-actually-means|«ظبّطي أكلك» معناها إيه بالظبط]].

## اللي مش محتاج تجيبيه

عشان القايمة متبقاش عبء:

مش محتاجة تعملي تحاليل جديدة قبل الزيارة. اللي هيتطلب هيتحدد بعد الكشف، والتحليل اللي بيتعمل من غير سبب بيكلف فلوس وبيدي أرقام محدش هيستخدمها.

ومش محتاجة تيجي صايمة إلا لو حد قالك كده تحديدًا.

ومش محتاجة تظبطي أكلك الأسبوع اللي قبل الميعاد. ده أكتر حاجة بتحصل، وهي بتضر: اليوم اللي هيتوصف مش هيبقى اليوم الحقيقي، والخطة هتتبني على معلومة غلط. الأسبوع العادي بالظبط هو المطلوب.

ومش محتاجة تحفظي حاجة. لو مش فاكرة اسم دوا، صوّري العلبة.

## سادس حاجة: الظروف اللي بتحدد شكل يومك

الحاجات دي بتبان تفاصيل شخصية، وهي في الحقيقة اللي بتقرر الخطة تنفع ولا لأ.

مواعيد الشغل، وهل في شفتات ولا لأ. الشخص اللي بيشتغل شفت ليلي مش بياخد نفس الخطة اللي بتفترض نوم بالليل وفطار الصبح.

مين بيطبخ في البيت، والأكل بيتحضر إمتى. لو الطبخ بيحصل مرة في الأسبوع، الخطة لازم تتعامل مع ده.

الميزانية، بصراحة. الخطة اللي بتفترض مصروف مش موجود هتقف في أسبوعين، والمشكلة هتتقري على إنها التزام.

في حد تاني في البيت بحالة مزمنة أو نظام معيّن؟ الأكل الواحد للعيلة كلها معناه إن الخطة لازم تشتغل جوه الحلة الموجودة.

وهل في سفر أو مناسبات قريبة؟ رمضان، عيد، فرح، امتحانات، سفر شغل. الأسابيع دي بتيجي مهما حصل، والخطة اللي عارفة إنها جاية أحسن من الخطة اللي بتتفاجئ بيها.

## خرافة: «هو هيعرف كل حاجة من الكشف»

الجملة دي بتفترض إن الفحص الإكلينيكي بيغني عن التاريخ. وهو مش بيغني، ومحدش قال إنه بيغني.

الفحص بيدي معلومات مهمة، بس أشياء كتير مش بتظهر فيه: دوا بيتاخد من سنة، تحليل اتعمل الشهر اللي فات، محاولة سابقة وقفت بسبب عرض معيّن، شغل بيمنع وجبة الضهر. المعلومات دي مش موجودة غير عندك.

الخرافة بتنتشر لأنها بتحوّل المسؤولية كلها للطرف التاني، وده مريح. بس النتيجة إن الخطة بتتبني على نص المعلومات.

## في السياق المصري

الورق عندنا ورق فعلًا. مفيش سجل إلكتروني موحّد بيتنقل مع المريض، يعني اللي في الشنطة هو كل الموجود. ودي حاجة تستاهل تتقال بصراحة: احتفظي بالورق، وصوّريه، وحطيه في فولدر على الموبايل.

والصيدلية عندنا بتصرف حاجات كتير من غير روشتة، ومنها فيتامينات ومكملات وأحيانًا أدوية. الحاجات دي بتتاخد لفترات طويلة ومش بتتحسب. اكتبيها.

والعيلة جزء من الصورة. اللي بيطبخ، والميزانية، والوجبة الواحدة للكل، ومواعيد الأكل المرتبطة بمواعيد الناس — كل ده بيغيّر الخطة، وكلامك عنه في الكشف بيوفر تعديلات كتير بعدين.

ولو الميعاد في رمضان أو قريب منه، قولي. شكل اليوم بيتغير كليًا، والخطة اللي اتكتبت لشكل يوم تاني مش هتنفع.

PRACTITIONER_VOICE: إيه أكتر حاجة نفسك المرضى يجيبوها معاهم وبيجوا من غيرها؟

وحاجة أخيرة عملية: لو ينفع، خلي حد معاكي أو سجلي الكلام لو الطرف التاني موافق. الكشف فيه معلومات كتير بتتقال بسرعة، والذاكرة بعد الكشف مش بتكون في أحسن حالاتها — خصوصًا لو الموضوع فيه قلق.

## اللي يستاهل تفتكريه

الكشف الأول بيبني خطة على المعلومات المتاحة فيه. المعلومات اللي مش موجودة مش بتتحسب، والخطة بتتعوّج بقدر ما ينقص منها.

الأدوية والمكملات كلها، الورق كله، يومين أكل حقيقيين، تاريخ المحاولات، والأسئلة. خمس حاجات، وكلها موجودة عندك أصلًا.

لو جاهزة، تقدري [[booking|تحجزي موعد]]. ولو عندك ورق تحاليل مش متأكدة منه، ده بالظبط اللي بتعمله [[specialty:lab-review|مراجعة التحاليل]].
AR,

                'en' => <<<'EN'
Something recurring happens in a first consultation: half the time goes into assembling information that could have arrived ready. A test done six months ago sitting in a drawer at home, a medication whose name is not quite remembered, and a supplement taken daily that nobody counted as a medicine.

This article is a practical list, and there is a reason behind every item on it.

## What actually happens in a first consultation

A serious nutritional assessment is not the question "what do you eat?". It is the assembly of a picture: medical history, medications, lab results if they exist, the shape of the day, the history of previous attempts, and preferences and budget.

The American Diabetes Association, in its annual Standards of Care, describes a comprehensive medical evaluation as the foundation of any plan, and places medication review inside it as an integral part. And the European Society for Clinical Nutrition and Metabolism (ESPEN) has a guideline devoted to the definitions and terminology of clinical nutrition, in which assessment is a distinct step preceding intervention.

So what you bring is not paperwork — it is the raw material the plan is built from.

## First: medications and supplements, all of them

Write down or photograph everything taken regularly. Prescribed medicines, medicines taken without a prescription, vitamins, supplements, herbal preparations.

This is the most important item on the whole list, for two reasons.

The first is that some medications affect food and weight directly: they change appetite, or affect the absorption of particular nutrients, or have timings tied to meals. NICE, in its guidance on medicines optimisation, recommends reconciling the medication list at any transition of care — which simply means that the new person seeing you needs to know exactly what you are taking.

The second reason is more subtle and surprises a great many people: some supplements affect the test results themselves.

## A supplement can change a test result — a real example

Biotin, sold in pharmacies as a hair and nail supplement at high doses, can interfere with certain kinds of blood test that use a particular assay technique — including thyroid tests.

The United States Food and Drug Administration issued a safety communication about this interference, because an affected result can be read as a thyroid problem that does not exist — or can conceal one that does.

And this supplement in particular is not thought of as a medicine by anybody. It is "a vitamin for hair", bought without a prescription, and forgotten when the list is made.

The point is not that anybody should be alarmed about her supplements. The point is that they should be mentioned, so that whoever reads the result knows.

CLINICAL_INPUT: Are there supplements you ask about by name in a first consultation because of their effect on tests or on the plan?

## And why a supplement is not "a small thing"

There is a common belief that the worst a supplement can do is nothing. That is not accurate.

Vitamins A, D, E and K are fat-soluble, which means the body stores them rather than clearing the excess in urine as it does with the water-soluble ones. That difference matters: a sustained excess of this kind accumulates over time.

There are interactions between supplements themselves. Calcium uses the same absorption pathways as iron and competes with it, so a calcium supplement taken with an iron supplement reduces the benefit of both. Zinc and copper compete in the same way.

And there are interactions between supplements and medicines. The best-known example is vitamin K and anticoagulants, because that medication acts on precisely the same pathway.

Despite all of which, supplements in Egypt are sold without prescription, taken on the advice of a relative or an advertisement, and continued for long periods. None of that is necessarily a problem — but all of it needs to be known.

## Second: the paper

Every test, scan or report from at least the past year, even if somebody said it was fine.

The reason is that one result describes a moment and two results describe a direction. A number at the low edge that has fallen from a higher one last year is one thing; the same number steady for three years is something else entirely — and they look identical on a single page.

A photograph on your phone is fine. The original is not needed.

And if there is a chronic diagnosis — diabetes, blood pressure, thyroid, bowel, polycystic ovary syndrome, coeliac disease — bring the most recent report on it.

And if the tests were done at different laboratories, say so as well. Reference ranges differ between laboratories according to the instrument and the method, so comparing a number from one with a number from another needs care.

## Third: a real day of eating

Write down two or three days of food before the appointment, roughly hour by hour.

And the essential condition: an ordinary day, not an ideal one. An ideal day produces a plan for a person who does not exist.

Write down the things that do not usually get counted as food, too: tea with sugar, juices, fizzy drinks, the mouthful taken while cooking, and food at work.

## Fourth: the history of what you have already tried

What was tried, what worked, what stopped, and why it stopped.

This is the information that saves months. A plan resembling something that did not work before will stop in the same way, and knowing why it stopped changes the design from the first day.

And if an attempt stopped because of a symptom — dizziness, headache, constipation, constant hunger, a drop in energy — that in particular should be said. The symptom is clinical information, not a complaint.

## Fifth: your questions, written down

Questions get forgotten in a consultation. Put them on paper or in your phone.

And if the sentence that brought you here was "sort your diet out" with no detail, there is an article on what it means: [[article:what-fix-your-diet-actually-means|what that sentence actually means]].

## What you do not need to bring

So that the list does not become a burden:

You do not need to have new tests done before the visit. What is needed will be decided after the consultation, and a test done without a reason costs money and produces numbers nobody will use.

You do not need to come fasting unless somebody has specifically told you to.

You do not need to tidy up your eating in the week before the appointment. This is the most common thing people do, and it is counterproductive: the day described will not be the real day, and the plan will be built on wrong information. An ordinary week is exactly what is wanted.

And you do not need to memorise anything. If you cannot remember the name of a medicine, photograph the box.

## Sixth: the circumstances that shape your day

These look like personal details and are in fact what decides whether a plan works.

Working hours, and whether there are shifts. Somebody on night shifts does not get the plan that assumes sleeping at night and breakfast in the morning.

Who cooks at home, and when food is prepared. If cooking happens once a week, the plan has to deal with that.

Budget, honestly. A plan that assumes money that is not there will stop within a fortnight, and the problem will be read as a failure of commitment.

Is there somebody else in the house with a chronic condition or a particular diet? One meal for the whole family means the plan has to work inside the pot that already exists.

And is there travel or an occasion coming? Ramadan, a feast, a wedding, exams, a work trip. Those weeks arrive regardless, and a plan that knows they are coming is better than one taken by surprise.

## The myth: "they will work it all out from the examination"

This assumes that a clinical examination substitutes for a history. It does not, and nobody has ever claimed it does.

An examination gives important information, but a great deal does not appear in it: a medication taken for a year, a test done last month, a previous attempt that stopped because of a particular symptom, a job that makes a midday meal impossible. That information exists nowhere except with you.

The myth spreads because it transfers all responsibility to the other party, which is comfortable. The result is a plan built on half the information.

## In the Egyptian context

Here, paper is genuinely paper. There is no unified electronic record travelling with the patient, so what is in the bag is all there is. That is worth saying plainly: keep the paperwork, photograph it, and put it in a folder on your phone.

And pharmacies here dispense a great deal without a prescription, including vitamins, supplements and sometimes medicines. These get taken for long periods and do not get counted. Write them down.

Family is part of the picture. Who cooks, the budget, one meal for everybody, and mealtimes tied to other people's schedules — all of it changes the plan, and saying so in the consultation saves a great many adjustments later.

And if the appointment falls in Ramadan or near it, say so. The shape of the day changes completely, and a plan written for a different shape of day will not work.

PRACTITIONER_VOICE: What is the thing you most wish patients brought with them, and most often arrive without?

One last practical thing: if you can, bring somebody with you, or record the conversation if the other party agrees. A consultation carries a lot of information said quickly, and memory afterwards is not at its best — particularly when the subject is one you are anxious about.

## Worth remembering

A first consultation builds a plan on the information available inside it. Information that is not there does not count, and the plan bends by however much is missing.

All the medications and supplements, all the paper, two real days of eating, the history of attempts, and your questions. Five things, and you already have all of them.

If you are ready, you can [[booking|book an appointment]]. And if you have results you are unsure about, that is exactly what a [[specialty:lab-review|lab review]] is for.
EN,
            ],

            'citations' => [
                [
                    'organisation' => [
                        'ar' => 'الجمعية الأمريكية للسكري (ADA)',
                        'en' => 'American Diabetes Association (ADA)',
                    ],
                    'title' => ['ar' => 'معايير الرعاية في السكري', 'en' => 'Standards of Care in Diabetes'],
                    'year' => 2025,
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports "describes a comprehensive medical evaluation as the foundation of '
                        .'any plan, with medication review inside it". Reissued annually — confirm the '
                        .'current edition and update the year.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الجمعية الأوروبية للتغذية السريرية والأيض (ESPEN)',
                        'en' => 'European Society for Clinical Nutrition and Metabolism (ESPEN)',
                    ],
                    'title' => [
                        'ar' => 'دليل ESPEN لتعريفات ومصطلحات التغذية السريرية',
                        'en' => 'ESPEN guideline on definitions and terminology of clinical nutrition',
                    ],
                    'year' => 2017,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "assessment is a distinct step preceding intervention". Confirm the '
                        .'year and that the guideline defines assessment as a separate stage.',
                ],
                [
                    'organisation' => [
                        'ar' => 'المعهد الوطني للصحة وجودة الرعاية (NICE)',
                        'en' => 'National Institute for Health and Care Excellence (NICE)',
                    ],
                    'title' => [
                        'ar' => 'الاستخدام الأمثل للأدوية',
                        'en' => 'Medicines optimisation',
                    ],
                    'year' => 2015,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "recommends reconciling the medication list at any transition of '
                        .'care". Confident NICE guidance on medicines optimisation exists and covers '
                        .'reconciliation; confirm the title and year.',
                ],
                [
                    'organisation' => [
                        'ar' => 'إدارة الغذاء والدواء الأمريكية (FDA)',
                        'en' => 'United States Food and Drug Administration (FDA)',
                    ],
                    'title' => [
                        'ar' => 'تنبيه سلامة: تداخل البيوتين مع تحاليل المختبر',
                        'en' => 'Safety communication: biotin interference with laboratory tests',
                    ],
                    'year' => 2017,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the whole biotin section, which is the most specific claim in this '
                        .'article and therefore the one most worth checking. Confident the FDA issued a '
                        .'communication on biotin interference and that thyroid immunoassays are among those '
                        .'affected; confirm the year, the exact title, and whether it has been updated. If '
                        .'the detail cannot be confirmed, the section should be cut rather than softened — '
                        .'a half-remembered warning about test interference is worse than none.',
                ],
            ],
        ];
    }
}
