<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * PCOS attracts more nutritional nonsense than any other condition this clinic
 * sees, for structural reasons the article names: it is common, it is chronic,
 * it has no cure, it affects appearance and fertility, and it is diagnosed in
 * young women who are online.
 *
 * So the article teaches a method rather than a list. A list of debunked
 * claims is out of date in a year; somebody who can tell a mechanism from an
 * outcome can judge the next claim herself.
 *
 * The 2023 international guideline carries almost the whole evidential weight
 * here and is the first citation to verify.
 *
 * ---------------------------------------------------------------------------
 *
 * HER THREE QUESTIONS ARE THE POINT OF THIS ARTICLE. They are in the callout,
 * and the callout is the reason ArticleBody::isCallout() exists — the draft had
 * no way to set a block apart from the prose around it, and a tool the reader
 * is meant to carry out of the piece and use on the NEXT claim cannot be set in
 * the same type as the paragraph arguing with the last one.
 *
 * THE DRAFT HAD SIX QUESTIONS OF ITS OWN AND THEY ARE GONE. Hers replaced them
 * rather than joining them. Two checklists in one article is not twice the
 * help: the reader has to decide which one to use, and the one written by the
 * practitioner she is about to sit with is obviously the one that should
 * survive. Everything the six carried that hers do not — the vague verb, the
 * "no medication, it's natural" claim — was folded into the sections where it
 * belonged, so nothing was lost except the duplication.
 *
 * The cinnamon example is then run THROUGH her three questions rather than
 * sitting next to them, which is what makes them a tool instead of a slogan.
 *
 * ON THE ACTIVITY ANSWER. She did not name a type of exercise, a duration or a
 * frequency, and none has been invented for her. What she gave is a criterion —
 * whether it fits the day and can be sustained — and that is what the section
 * says. An article that filled the gap with a plausible-sounding suggestion
 * would be putting a prescription in her mouth.
 */
class PcosAndFoodJudgingAClaim extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'pcos-and-food-judging-a-claim',
            'category' => 'pcos-hormonal',
            'tags' => ['myths', 'questions-to-ask'],
            'cover' => 'cinnamon-bowl-dark',

            'title' => [
                'ar' => 'تكيس المبايض والأكل — إزاي تحكمي على أي كلام تسمعيه',
                'en' => 'PCOS and food — how to judge a claim',
            ],

            'excerpt' => [
                'ar' => 'كل أسبوع في «أكلة بتعالج التكيس». المقال ده مش قايمة ردود — هو طريقة تحكمي بيها على اللي جاي كمان.',
                'en' => 'Every week there is a food that "treats PCOS". This is not a list of rebuttals — it is a method for judging the next claim too.',
            ],

            'body' => [
                'ar' => <<<'AR'
لو عندك تكيس مبايض، فأكيد وصلك كلام كتير عن الأكل. امنعي الألبان. امنعي الجلوتين. خُدي قرفة على الريق. الكيتو بيعالجه. خل التفاح. عشبة معيّنة. فيتامين معيّن.

بعض الكلام ده فيه جزء حقيقي، وبعضه مالوش أي أساس، والمشكلة إن الاتنين بيتقالوا بنفس الثقة وبنفس الشكل.

المقال ده مش قايمة رد على كل ادعاء — القايمة دي هتبقى قديمة بعد سنة. المقال ده عن طريقة تحكمي بيها على أي كلام يوصلك، النهارده وبعد سنة.

ود. رنا بتقول إن أول حاجة بتحب توضحها لأي مريضة بتوصلها القايمة دي: «التكيس مش معناه إن فيه قائمة واحدة من الممنوعات تنفع لكل الحالات».

## الأول: ليه تكيس المبايض بالذات بيجذب الكلام ده

مش صدفة. في خصائص في الحالة نفسها بتخليها أرض خصبة:

هي منتشرة، يعني الجمهور كبير. وهي مزمنة، يعني الشخص بيدور لفترة طويلة. ومفيش علاج نهائي بيخلّصها، يعني في مساحة دايمًا فاضية لوعد جديد. وأعراضها بتمس حاجات حساسة — الوزن، الشعر، البشرة، الدورة، والخصوبة. وبتتشخص غالبًا في ستات صغيرة في السن وموجودين على السوشيال ميديا.

كل خاصية من دول بتزوّد الطلب على إجابة بسيطة، واللي بيبيع إجابة بسيطة عنده جمهور جاهز. ده مش معناه إن كل اللي بيتقال كدب — معناه إن الحاجة دي بالذات محتاجة تُقرا بحرص أعلى من المتوسط.

## اللي الدليل الدولي بيقوله فعلًا

في دليل دولي مبني على الأدلة لتقييم وإدارة متلازمة تكيس المبايض، طلع في نسخته المحدّثة سنة ٢٠٢٣، وشاركت في تطويره واعتماده جهات منها الجمعية الأوروبية للتكاثر البشري وعلم الأجنة (ESHRE).

الدليل ده بيقول حاجات محددة تستاهل تتقري بالظبط:

إن تعديل نمط الحياة — الأكل والحركة والجانب السلوكي — موصى بيه كجزء أساسي من الإدارة.

وإن مفيش نمط غذائي واحد أثبت تفوقه على غيره في تكيس المبايض. يعني الدليل نفسه، اللي مهمته كلها إنه يقول إيه اللي ينفع، بيقول إن مفيش نظام معيّن هو الإجابة.

وإنه بيتعامل بحذر مع المكملات والعلاجات اللي الأدلة عليها محدودة، بدل ما يوصي بيها.

خلي بالك مين اللي بيقول ده. دي مش عيادة، ولا حد بيبيع كورس. ده دليل دولي بيشتغل عليه أطباء نساء وغدد في العالم كله. ولو كان في نظام أكل واحد بيعالج تكيس المبايض، دي بالظبط الجهة اللي كانت هتقوله ــ وبصوت عالي.

## الفسيولوجي: إيه علاقة الإنسولين بالموضوع أصلًا

عشان تقدري تحكمي على أي ادعاء، لازم تعرفي الميكانيكية اللي بيتقال إنها بتشتغل.

الإنسولين هرمون بيتفرز من البنكرياس بعد الأكل، وشغلته إنه يخلي الخلايا تسحب السكر من الدم وتستخدمه. في مقاومة الإنسولين الخلايا بتستجيب للهرمون ده بكفاءة أقل، فالبنكرياس بيفرز كمية أكبر عشان يوصل لنفس النتيجة، والنتيجة إن مستوى الإنسولين في الدم بيبقى أعلى من المعتاد.

والجزء المهم إن الإنسولين مش بيشتغل على السكر بس: مستوياته العالية بتأثر على المبيض وعلى إنتاج الأندروجينات — الهرمونات اللي زيادتها مرتبطة بأعراض زي الشعر الزايد وحب الشباب واضطراب التبويض.

فالكلام عن الأكل في تكيس المبايض مش كلام فاضي: الأكل بيأثر على استجابة الإنسولين، والإنسولين ليه علاقة بالصورة الهرمونية. بس — ودي النقطة اللي بتضيع دايمًا — وجود ميكانيكية معقولة مش نفس إثبات نتيجة.

## الفرق بين ميكانيكية ونتيجة

معظم الادعاءات اللي بتوصلك بتقف عند نص الطريق.

الشكل بيكون كده: «مادة كذا بتأثر على حساسية الإنسولين» — وده ممكن يكون صح ومقاس في المعمل — «يبقى هي بتعالج تكيس المبايض» — وده قفزة محدش قاسها.

بين الاتنين في مسافة طويلة: هل الأثر ده بيحصل في الجسم البشري بالجرعة اللي في الأكل؟ وهل بيستمر؟ وهل بيترجم لتغيير في الأعراض اللي الشخص جاي عشانها — الدورة، التبويض، الشعر — ولا بيفضل رقم في تحليل؟

وخلي بالك من الكلمة نفسها: «بيحسّن» غير «بيعالج» غير «بيمنع»، والكلمة المبهمة غالبًا مقصودة.

والإينوزيتول مثال أدق، وأمين إننا نقوله بوضوح: ده مركب اتدرس فعلًا في تكيس المبايض، والدليل الدولي بيتعامل معاه وبيناقشه، والأدلة عليه لسه محدودة ومش وصلت لمستوى توصية قاطعة. يعني هو مش خرافة، وهو كمان مش حقيقة مستقرة. والفرق بين التوصيف ده وبين «الإينوزيتول بيعالج التكيس» هو الفرق بين الأمانة والتسويق.

## تلات أسئلة تقفي عندها قبل ما تغيّري أكلك

د. رنا بتدّي مرضاها اختبار قصير مش مربوط بادعاء بعينه — هو للي جاي كمان:

> كل ما تسمعي نصيحة عن التكيس، اسألي تلات أسئلة:
> - هل الكلام ده عليه دليل؟
> - هل ينطبق على حالتي أنا؟
> - وهل أقدر أستمر عليه بشكل صحي؟
> لو الإجابة مش واضحة، يبقى النصيحة محتاجة مراجعة قبل ما تغيّري أكلك بسببها.

وعشان الأسئلة دي تبقى أداة مش كلام، خلينا نجرّبها على القرفة.

هل عليها دليل؟ في دراسات صغيرة بصت على أثرها على سكر الدم، فالإجابة مش «لأ». الإجابة إن في حاجة اتقاست، بس مش لدرجة إن الدليل الدولي يوصي بيها كعلاج للتكيس.

هل تنطبق على حالتك إنتِ؟ دي السؤال اللي مفيش منشور يقدر يجاوبه. هو بيعتمد على أعراضك إنتِ، وعلى الأدوية اللي بتاخديها، وعلى إيه أكتر حاجة جايالك عشانها أصلًا.

وهل تقدري تستمري عليها بشكل صحي؟ دي أسهل واحدة فيهم — ملعقة قرفة الصبح مش حاجة صعبة. بس السؤال هنا مش عن الصعوبة: هو عن إن الحاجة السهلة دي ممكن تاخد مكان حاجة ليها أدلة، وتخليكي مستنية نتيجة مش جاية.

والتلات إجابات مع بعض بيقولوا حاجة واحدة: القرفة مش أذى، وهي كمان مش خطة. والفرق بين الجملتين هو اللي المقال ده كله عنه.

## علامات إن الكلام محتاج وقفة

د. رنا بتقول إن أول علامة عندها هي الكلام المطلق: «لو حد بيقول (كل مريضة تكيس لازم تعمل كذا)، أو (الأكلة دي ممنوعة تمامًا لكل مرضى التكيس)، أو (اعملي النظام ده وهتعالجي التكيس نهائيًا) — هنا لازم نقف ونسأل».

والعلامة التانية بتخص اللي وراه بيع: «خدي بالك كمان من أي حد بيخوّفك من أكل عادي عشان يبيعلك بعدها مكمل أو منتج باعتباره الحل».

والترتيب في الجملة دي هو المهم: الخوف الأول، والمنتج بعده. لأن الخوف هو اللي بيخلي المنتج يبان ضروري.

وفي المقابل، الشكل اللي بيطمّن: «النصيحة الموثوقة عادةً بتشرح ليه، ومين ممكن تستفيد منها، ومين ممكن ما تناسبهاش. ومبتوعدكيش بنتيجة مضمونة في وقت قياسي».

وفي علامة تالتة أخطر من الاتنين: الادعاء اللي بيقول «بلاش أدوية، ده طبيعي». الدليل الدولي بيتكلم عن أدوية ليها مكان في الإدارة حسب الحالة والهدف، والاختيار بينها قرار إكلينيكي. اللي بيطلب من حد يوقف علاج موصوف مش بيدي نصيحة غذائية — ده تدخل في علاج، ومن حد مش شايف الحالة ومش مسؤول عن نتيجتها.

## وحاجة تانية الدليل بيقولها وبتتنسى: الحركة

الكلام كله بيتركز على الأكل، والدليل الدولي بيتكلم عن الأكل والحركة والجانب السلوكي مع بعض كحزمة واحدة، مش عن الأكل لوحده.

ده مهم عمليًا لأن الحركة بتأثر على حساسية الإنسولين بطريقة مباشرة: العضلة اللي بتشتغل بتسحب سكر من الدم بآلية مش محتاجة إنسولين بنفس الدرجة، والنشاط المنتظم بيحسّن استجابة الأنسجة للهرمون ده مع الوقت.

يعني اللي بيدور على الأكل بس بيسيب نص التوصية. ومش الكلام عن جيم ولا برنامج — الكلام عن نشاط منتظم بشكل ما.

ود. رنا بتحط الحركة والنوم في نفس الميزان مع الأكل وهي بتبني الخطة، والمعيار عندها مش نوع التمرين ولا اسمه: بنشوف إيه اللي يناسب يومك وتقدري تستمري عليه. يعني السؤال مش «إيه أحسن رياضة للتكيس؟» — السؤال إيه اللي هيفضل موجود في يومك.

## والوزن — نقطة تستاهل تتقال بحرص

في علاقة معروفة بين الوزن ومقاومة الإنسولين، والدليل الدولي بيتعامل مع تعديل نمط الحياة كجزء من الإدارة.

بس في حاجتين لازم يتقالوا مع بعض: تكيس المبايض بيحصل في ستات بأوزان مختلفة، ومش كل حالة مرتبطة بالوزن. والافتراض إن الحل هو النزول بس بيسيب ستات كتير من غير إجابة، وبيحمّل الموضوع كله على حاجة واحدة.

ود. رنا بتبدأ من الهدف قبل ما تبدأ من الأكل: «مش كل واحدة عندها تكيس هدفها لازم يكون نزول الوزن. وحتى لو محتاجة تنزل وزن، مش لازم تنزل كمية ضخمة عشان تبدأ تشوف تحسن».

## في المطبخ المصري

الكلام عن تكيس المبايض بيوصل هنا مترجم غالبًا، والأكل المذكور فيه مش أكلنا. والجزء العملي إن الأكل المصري فيه أدوات كتير للي الدليل بيوصي بيه فعلًا: البقوليات — فول وعدس وحمص ولوبيا — والخضار الموسمي والسلطة والبيض والجبنة القريش والزبادي والسمك البلدي. دي مش «بدائل» لأكل أجنبي؛ دي أكل بجد.

وفي حاجات بتتقال هنا بالذات وتستاهل توضيح: منع العيش البلدي والرز نهائيًا مش توصية موجودة في الدليل الدولي، ومنع الألبان من غير سبب إكلينيكي مش توصية موجودة، والحمية الخالية من الجلوتين مالهاش علاقة بتكيس المبايض إلا لو في حساسية قمح مشخصة.

والخطة اللي بتتنفذ في بيت مصري هي اللي بتشتغل جوه الحلة اللي بتتطبخ للعيلة كلها، مش اللي بتطلب مطبخ منفصل.

## أكتر حاجة بتوصل للعيادة

د. رنا بتقول إن ده أكتر كلام غلط بيوصلها عن التكيس: «أول ما واحدة تعرف إن عندها تكيس، تفتكر إن لازم تمنع النشويات والسكر تمامًا، أو إن الفاكهة ممنوعة، أو إن كل مريضة تكيس لازم تعمل كيتو أو صيام متقطع».

وساعات القايمة بتطول أكتر من كده: «ممنوع عيش، ممنوع رز، ممنوع لبن، ممنوع فاكهة معينة… فتلاقي المريضة داخلة الجلسة وهي حاسة إن كل الأكل تقريبًا بقى ضدها».

والجملة الأخيرة دي هي المشكلة الحقيقية، مش القايمة نفسها. حد بيبدأ متابعة طويلة وهو حاسس إن الأكل كله بقى في الناحية التانية.

## طب بنبدأ منين؟

د. رنا بتقول إنها مش بتبدأ من الأكل أصلًا: «لما مريضة عندها تكيس تيجي لي أول مرة، أنا مش ببدأ بسؤال (هنشيل النشويات إزاي؟) — ببدأ بيها هي».

والأسئلة اللي بتبدأ بيها:

إيه الأعراض اللي عندك؟

الدورة منتظمة ولا لأ؟

هل في زيادة وزن أو صعوبة في نزوله؟

هل في شعر زائد أو حب شباب؟

نومك وحركتك عاملين إزاي؟

بتاكلي إيه خلال يومك؟

وإيه أكتر حاجة مضايقاكي وعايزة تحسنيها؟

وبتراجع معاكي التشخيص والمتابعة الطبية وأي تحاليل أو أدوية مرتبطة بالحالة.

بعد كده بيتحدد الهدف — وزي ما اتقال فوق، مش دايمًا نزول الوزن. وبعدين بيتبني الأكل بشكل واقعي: جودة الوجبات، البروتين والألياف، كمية ونوع الكربوهيدرات، الحركة والنوم، وإيه اللي يناسب يومك وتقدري تستمري عليه.

والترتيب ده مش تفصيلة إدارية. اللي بيبدأ من قايمة الممنوعات بيكتب خطة لحالة هو مشافهاش.

## والحاجة اللي مش بتتقال كفاية

تكيس المبايض حالة بتتدار، مش بتتشال. والإدارة الجادة فيها متابعة على مدى شهور، ومؤشرات بتتقاس، وتعديل لما حاجة متنفعش. والوعد بالحل السريع مش بس مش صحيح — هو بيسرق الوقت من حاجة كانت هتشتغل.

ود. رنا بتضيف حاجة بتشوفها كتير: «متاخديش تجربة واحدة على السوشيال ميديا كأنها قاعدة طبية. كون واحدة قالت (بطلت النشويات والدورة انتظمت) — ده يحكي تجربتها هي، لكنه مش دليل إن كل مريضة تكيس محتاجة تعمل نفس الحاجة».

## اللي يستاهل تفتكريه

الدليل الدولي بيوصي بتعديل نمط الحياة، وبيقول بوضوح إن مفيش نظام غذائي واحد أثبت تفوقه. والإنسولين ليه علاقة حقيقية بالصورة، بس وجود ميكانيكية مش إثبات نتيجة. وتلات أسئلة — في دليل؟ تنطبق عليّا؟ أقدر أستمر عليها؟ — بيفرزوا معظم اللي بيوصلك.

وزي ما بتقول د. رنا: «مع التكيس إحنا مش بندوّر على أكتر نظام فيه ممنوعات — إحنا بندوّر على طريقة أكل مناسبة لحالتك، تساعدنا نوصل لهدف واضح، وتقدري تكمّلي عليها من غير ما تحسي إن كل وجبة اختبار».

لو عندك تشخيص وعايزة خطة مبنية على حالتك إنتِ مش على منشور: [[specialty:pcos-hormonal|تغذية تكيس المبايض والاضطرابات الهرمونية]]، أو [[booking|احجزي موعد]].

وفي مقال عن الأسئلة اللي تسأليها في الكشف نفسه: [[article:questions-to-ask-about-hormones-and-food|أسئلة تسأليها عن الهرمونات والأكل]].
AR,

                'en' => <<<'EN'
If you have polycystic ovary syndrome, a great deal has been said to you about food. Cut out dairy. Cut out gluten. Cinnamon on an empty stomach. Keto cures it. Apple cider vinegar. A particular herb. A particular vitamin.

Some of it contains something real, some of it has no basis at all, and the difficulty is that both arrive with the same confidence and in the same shape.

This article is not a rebuttal of each claim — such a list would be out of date within a year. It is about a method for judging whatever reaches you, today and in a year's time.

Dr Rana says the first thing she wants to make clear to any patient who arrives carrying that list is this: "PCOS does not mean there is one list of forbidden foods that works for every case."

## First: why PCOS in particular attracts this

It is not an accident. There are features of the condition itself that make it fertile ground:

It is common, so the audience is large. It is chronic, so people search for a long time. There is no definitive cure, so there is always an empty space for a new promise. Its symptoms touch sensitive things — weight, hair, skin, periods, fertility. And it is usually diagnosed in young women who are online.

Every one of those features increases the demand for a simple answer, and anybody selling a simple answer has a ready audience. None of which means everything said is false — it means this subject in particular needs to be read with above-average care.

## What the international guideline actually says

There is an international evidence-based guideline for the assessment and management of polycystic ovary syndrome, whose updated edition appeared in 2023, developed and endorsed by bodies including the European Society of Human Reproduction and Embryology (ESHRE).

It says specific things worth reading exactly:

That lifestyle modification — diet, activity and the behavioural component — is recommended as a core part of management.

That no single dietary pattern has been shown to be superior to another in PCOS. Which is to say: the guideline itself, whose entire purpose is to state what works, says that no particular diet is the answer.

And that it treats supplements and therapies with limited evidence cautiously rather than recommending them.

Notice who is saying this. Not a clinic, and nobody selling a course. It is an international guideline that gynaecologists and endocrinologists worldwide work from. If there were one eating pattern that treated PCOS, this is precisely the body that would have said so — loudly.

## The physiology: what insulin has to do with any of this

To judge a claim, you need to know the mechanism it is claiming to use.

Insulin is a hormone secreted by the pancreas after eating, and its job is to make cells take glucose out of the blood and use it. In insulin resistance the cells respond to that hormone less efficiently, so the pancreas secretes more of it to reach the same result, and circulating insulin ends up running higher than usual.

The important part is that insulin does not act only on glucose: raised levels affect the ovary and the production of androgens — the hormones whose excess is associated with symptoms such as unwanted hair growth, acne and disrupted ovulation.

So talk about food in PCOS is not empty: food affects the insulin response, and insulin has a bearing on the hormonal picture. But — and this is the point that always gets lost — the existence of a plausible mechanism is not the same as a demonstrated outcome.

## The difference between a mechanism and an outcome

Most claims that reach you stop halfway.

The shape is this: "substance X affects insulin sensitivity" — which may be true and measured in a laboratory — "therefore it treats PCOS" — which is a leap nobody measured.

Between the two lies a long distance: does that effect occur in a human body at the dose present in food? Does it persist? Does it translate into a change in the symptoms somebody actually came about — periods, ovulation, hair — or does it stay a number in a test?

Watch the verb, too: "improves" is not "treats" is not "prevents", and the vague word is usually vague on purpose.

Inositol is a subtler example, and it is only honest to say so plainly: it is a compound that genuinely has been studied in PCOS, the international guideline addresses and discusses it, and the evidence remains limited and has not reached the level of a firm recommendation. So it is not a myth, and it is also not settled fact. The difference between that description and "inositol treats PCOS" is the difference between honesty and marketing.

## Three questions to stop at before you change how you eat

Dr Rana gives her patients a short test that is not tied to any one claim — it is for the next one too:

> Every time you hear advice about PCOS, ask three questions:
> - Is there evidence for this?
> - Does it apply to my case?
> - And can I keep it up in a healthy way?
> If the answer is not clear, the advice needs a second look before you change how you eat because of it.

So that those questions are a tool rather than a sentiment, try them on cinnamon.

Is there evidence for it? Small studies have looked at its effect on blood glucose, so the answer is not "no". The answer is that something has been measured, but not to the point where the international guideline recommends it as a treatment for PCOS.

Does it apply to your case? That is the question no post can answer. It depends on your symptoms, on the medicines you take, and on what brought you in the first place.

And can you keep it up in a healthy way? That is the easiest of the three — a spoonful of cinnamon in the morning is not hard. But the question here is not about difficulty: it is that this easy thing can take the place of something with evidence behind it, and leave you waiting for a result that is not coming.

The three answers together say one thing. Cinnamon is not harmful, and cinnamon is also not a plan. The distance between those two sentences is what this whole article is about.

## Signs that something needs a second look

Dr Rana says the first sign for her is the absolute statement: "If somebody says 'every PCOS patient must do X', or 'this food is completely forbidden for everyone with PCOS', or 'follow this diet and you will cure PCOS for good' — that is where we stop and ask."

The second sign is about what is being sold: "And be careful of anybody who frightens you about ordinary food and then sells you a supplement or a product as the solution."

The order in that sentence is the part to notice: the fear first, the product after. Because the fear is what makes the product look necessary.

And on the other side, the shape that reassures: "Trustworthy advice usually explains why, and who might benefit from it, and who it might not suit. And it does not promise you a guaranteed result in record time."

There is a third sign, more dangerous than the other two: the claim that says "no medication, this is natural". The international guideline discusses medications that have a place in management depending on the case and the goal, and choosing between them is a clinical decision. Somebody asking you to stop a prescribed treatment is not giving dietary advice — it is an intervention in treatment, from somebody who has not seen the case and is not answerable for the outcome.

## And something else the guideline says that gets forgotten: activity

The conversation concentrates entirely on food, while the international guideline talks about diet, activity and the behavioural component together as one package, not about food alone.

That matters practically, because activity affects insulin sensitivity directly: a working muscle draws glucose from the blood by a mechanism that does not depend on insulin to the same degree, and regular activity improves how tissues respond to the hormone over time.

So anybody looking only at food is leaving half the recommendation behind. And this is not about a gym or a programme — it is about regular activity of some kind.

Dr Rana puts activity and sleep on the same scale as food when she builds the plan, and her criterion is not the type of exercise or its name: we look at what suits your day and what you can keep doing. So the question is not "what is the best exercise for PCOS?" — the question is what will still be in your day later.

## And weight — a point to be made carefully

There is a recognised relationship between weight and insulin resistance, and the international guideline treats lifestyle modification as part of management.

But two things have to be said together: PCOS occurs in women across a range of weights, and not every case is weight-related. Assuming the answer is simply weight loss leaves a great many women without an answer, and loads the whole condition onto one variable.

Dr Rana starts from the goal before she starts from the food: "Not every woman with PCOS has to have weight loss as her goal. And even if she does need to lose weight, she does not have to lose a great deal before she starts to see an improvement."

## In an Egyptian kitchen

Talk about PCOS reaches us mostly in translation, and the food named in it is not our food. The practical part is that Egyptian food contains plenty of tools for what the guideline actually recommends: legumes — foul, lentils, chickpeas, black-eyed beans — seasonal vegetables, salad, eggs, areesh cheese, yoghurt and local fish. These are not "substitutes" for foreign food; they are food.

And some things said here specifically deserve clarifying: cutting out baladi bread and rice entirely is not a recommendation in the international guideline; excluding dairy without a clinical reason is not a recommendation; and a gluten-free diet has no bearing on PCOS unless coeliac disease has been diagnosed.

The plan that gets followed in an Egyptian household is the one that works inside the pot already being cooked for everybody, not the one that requires a separate kitchen.

## What arrives at the clinic most often

Dr Rana says this is the most common piece of wrong information that reaches her about PCOS: "The moment a woman finds out she has PCOS, she thinks she has to cut out starches and sugar completely, or that fruit is forbidden, or that every PCOS patient has to do keto or intermittent fasting."

And sometimes the list gets longer than that: "No bread, no rice, no milk, no particular fruit… so you find the patient coming into the session feeling that almost all food has become her enemy."

That last sentence is the real problem, not the list itself. Somebody is beginning a long course of follow-up already feeling that food is on the other side.

## So where do we start?

Dr Rana says she does not start from food at all: "When a woman with PCOS comes to me for the first time, I don't start with the question 'how do we take the starches out?' — I start with her."

The questions she starts with:

What symptoms do you have?

Is your cycle regular or not?

Has there been weight gain, or difficulty losing it?

Is there unwanted hair growth or acne?

How are your sleep and your activity?

What do you eat through your day?

And what is the thing bothering you most that you want to improve?

And she goes through the diagnosis with you, the medical follow-up, and any tests or medicines connected to the condition.

After that the goal gets set — and as was said above, that is not always weight loss. Then the eating gets built realistically: the quality of the meals, protein and fibre, the amount and type of carbohydrate, activity and sleep, and what suits your day and what you can keep doing.

That order is not an administrative detail. Anybody starting from a list of forbidden foods is writing a plan for a case they have not seen.

## And the thing that does not get said enough

PCOS is a condition that is managed, not removed. Serious management involves follow-up over months, markers that get measured, and adjustment when something does not work. The promise of a quick resolution is not merely untrue — it takes time away from something that would have worked.

And Dr Rana adds something she sees a great deal: "Don't take one person's experience on social media as though it were a medical rule. The fact that somebody said 'I stopped eating starches and my period became regular' — that tells you about her experience, but it is not evidence that every PCOS patient needs to do the same thing."

## Worth remembering

The international guideline recommends lifestyle modification and states clearly that no single dietary pattern has been shown superior. Insulin genuinely does have a bearing on the picture, but a mechanism is not a demonstrated outcome. And three questions — is there evidence, does it apply to me, can I keep it up — sort most of what reaches you.

As Dr Rana puts it: "With PCOS we are not looking for the diet with the most restrictions — we are looking for a way of eating that suits your case, that helps us reach a clear goal, and that you can keep going with without feeling that every meal is a test."

If you have a diagnosis and want a plan built on your case rather than on a post: [[specialty:pcos-hormonal|PCOS and hormonal nutrition]], or [[booking|book an appointment]].

And there is an article on the questions to ask in the consultation itself: [[article:questions-to-ask-about-hormones-and-food|questions to ask about hormones and food]].
EN,
            ],

            'citations' => [
                [
                    'organisation' => [
                        'ar' => 'الجمعية الأوروبية للتكاثر البشري وعلم الأجنة (ESHRE) والجهات المشاركة',
                        'en' => 'European Society of Human Reproduction and Embryology (ESHRE) and partner bodies',
                    ],
                    'title' => [
                        'ar' => 'الدليل الدولي المبني على الأدلة لتقييم وإدارة متلازمة تكيس المبايض',
                        'en' => 'International evidence-based guideline for the assessment and management of polycystic ovary syndrome',
                    ],
                    'year' => 2023,
                    'confidence' => CitationConfidence::High,
                    'note' => 'Carries three separate claims in this article and is the first thing to '
                        .'verify: (1) lifestyle modification recommended as core management; (2) NO SINGLE '
                        .'DIETARY PATTERN shown superior; (3) supplements with limited evidence treated '
                        .'cautiously, and inositol specifically discussed with evidence described as '
                        .'limited. Claim (2) is the load-bearing one — confirm the wording covers dietary '
                        .'PATTERN and not only macronutrient composition. Confirm also which bodies are '
                        .'named as developers and endorsers, since the article names ESHRE specifically.',
                ],
                [
                    'organisation' => ['ar' => 'مكتبة كوكرين', 'en' => 'Cochrane'],
                    'title' => [
                        'ar' => 'الإينوزيتول للنساء المصابات بمتلازمة تكيس المبايض وضعف الخصوبة',
                        'en' => 'Inositol for subfertile women with polycystic ovary syndrome',
                    ],
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the paragraph describing the inositol evidence as real but limited. '
                        .'Confident a Cochrane review on inositol in PCOS exists; NO YEAR IS GIVEN because '
                        .'Cochrane reviews are updated and citing a superseded version would be worse than '
                        .'citing none. Confirm the current version, its year, and that its conclusion is '
                        .'genuinely one of limited or uncertain evidence rather than a positive finding — if '
                        .'the review is more favourable than the article implies, the paragraph must change.',
                ],
                [
                    'organisation' => [
                        'ar' => 'المعهد الوطني للصحة وجودة الرعاية (NICE)',
                        'en' => 'National Institute for Health and Care Excellence (NICE)',
                    ],
                    'title' => [
                        'ar' => 'الداء البطني: التعرّف والتقييم والتعامل',
                        'en' => 'Coeliac disease: recognition, assessment and management',
                    ],
                    'year' => 2015,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the sentence that a gluten-free diet has no bearing on PCOS unless '
                        .'coeliac disease has been diagnosed — cited for what a gluten-free diet is actually '
                        .'indicated for. Confirm the guideline year and current status.',
                ],
            ],
        ];
    }
}
