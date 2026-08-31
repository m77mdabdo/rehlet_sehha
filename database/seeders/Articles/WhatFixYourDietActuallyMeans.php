<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * What a doctor means by "sort your diet out" — and what the phrase leaves out.
 *
 * The article that has to define medical nutrition therapy without sounding
 * like a brochure for it. It does that by reporting how the term is defined by
 * the bodies that define it, and letting the definition do the arguing.
 */
class WhatFixYourDietActuallyMeans extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'what-fix-your-diet-actually-means',
            'category' => 'medical-nutrition',
            'tags' => ['first-visit', 'questions-to-ask'],
            'cover' => 'consultation-desk-wide',

            'title' => [
                'ar' => '«ظبّطي أكلك» — الجملة دي معناها إيه بالظبط؟',
                'en' => '"Sort your diet out" — what does that actually mean?',
            ],

            'excerpt' => [
                'ar' => 'الدكتور بيقولها في آخر الكشف من غير تفاصيل. ورا الجملة دي في حاجة ليها اسم وتعريف وخطوات معروفة.',
                'en' => 'A doctor says it at the end of a consultation with no detail attached. Behind the phrase is something with a name, a definition and known steps.',
            ],

            'body' => [
                'ar' => <<<'AR'
الجملة بتتقال في آخر دقيقة في الكشف: «ظبّطي أكلك». وبتتقال بحسن نية، ومن دكتور محترم، وغالبًا وهو بيكتب الروشتة.

المشكلة إن الجملة دي مش تعليمات. هي عنوان لحاجة كبيرة، ومحدش قال إيه اللي جواها. المقال ده عن اللي جواها.

## في الطب، الحاجة دي ليها اسم

اللي بيتقال عنه بالعامية «ظبّط أكلك» ليه في الأدبيات الطبية اسم محدد: العلاج الغذائي الطبي، أو Medical Nutrition Therapy.

الجمعية الأوروبية للتغذية السريرية والأيض (ESPEN) عملت دليل مخصص لتعريفات ومصطلحات التغذية السريرية، عشان الكلمات دي بالذات كانت بتتستخدم بمعاني مختلفة من مكان لمكان. وجود دليل كامل لتعريف المصطلحات هو نفسه معلومة: ده مجال ليه حدود ومفردات، مش نصايح عامة.

والجمعية الأمريكية للسكري (ADA) في تقرير توافقي مخصص للعلاج الغذائي للبالغين المصابين بالسكري أو ما قبل السكري بتتعامل مع العلاج الغذائي كتدخّل علاجي له خطوات: تقييم، خطة فردية، متابعة، وتعديل. ونفس الجمعية في معاييرها السنوية للرعاية بتوصي إن العلاج الغذائي يتقدّم عن طريق أخصائي تغذية مؤهل، مش كنصيحة عامة تتقال في آخر الكشف.

يعني الجملة اللي اتقالتلك مش خطأ — هي إحالة. بس الإحالة اتقالت من غير ما تتقال.

## الفرق بين نصيحة عامة وخطة

منظمة الصحة العالمية عندها إرشادات واضحة عن الأكل الصحي: خضار وفاكهة، تقليل السكر المضاف، تقليل الملح، تقليل الدهون المشبعة. الكلام ده صح، ومهم، ومنشور، ومجاني.

بس لازم يتقال بوضوح إيه هو: ده إرشاد موجّه للسكان ككل. هو مصمم عشان يقلل خطر الأمراض المزمنة على مستوى بلد بحاله. هو مش خطة لشخص واحد عنده تاريخ معيّن وتحاليل معيّنة وأدوية معيّنة وبيت فيه ناس تانية.

الفرق بين الاتنين هو الفرق بين خريطة المرور في القاهرة وبين الطريق من بيتك لشغلك. الخريطة صح تمامًا، وهي لوحدها مش هتوصلك.

## اللي بيحصل فعلًا في التقييم

التقييم الغذائي الجدي مش سؤال «بتاكلي إيه؟». هو مجموعة أسئلة كل واحدة فيها بتغيّر الخطة لو الإجابة اختلفت:

التاريخ المرضي والأدوية. في أدوية بتأثر على الشهية، وعلى امتصاص عناصر معينة، وعلى سكر الدم، وعلى الوزن. وفي حالات بتفرض قيود حقيقية — الكلى، الكبد، الغدة الدرقية، الحساسية.

التحاليل لما يكون ليها لازمة إكلينيكية. مش كل مريض محتاج تحاليل، ومش كل تحليل بيغيّر قرار.

شكل اليوم. بتصحي إمتى، بتاكلي فين، بتطبخي ولا في حد بيطبخ، وبتشتغلي شفتات ولا لأ. خطة بتفترض تلات وجبات في البيت لشخص بيشتغل بره اثنتي عشرة ساعة هي خطة اتكتبت لشخص تاني.

تاريخ المحاولات السابقة. إيه اللي اتجرب، وإيه اللي نفع، وإيه اللي وقف، وليه وقف. دي أهم معلومة في الكشف وأكترها بيتساب.

والتفضيلات والميزانية والعيلة. الخطة اللي بتتنفذ هي اللي بتتحسب.

## ليه التاريخ أهم من قايمة الأكل

الشخص اللي عنده مقاومة إنسولين، والشخص اللي عنده أنيميا نقص حديد، والشخص اللي عنده قولون متهيج — الثلاثة ممكن يكونوا بياكلوا نفس الأكل تقريبًا، والثلاثة محتاجين تعديلات مختلفة تمامًا، وبعض التعديلات المفيدة لواحد فيهم ممكن تكون غير مناسبة للتاني.

عشان كده «الأكل الصحي» مش إجابة كافية. السؤال مش «إيه الأكل الصح؟» — السؤال «إيه الأكل الصح لحالة زي دي؟».

## ليه نفس الأكل بيدي نتايج مختلفة في ناس مختلفة

دي النقطة اللي بتفسّر ليه الإرشاد العام مش بيكفي، وهي فسيولوجية بحتة مش فلسفية.

خد الحديد مثال. الحديد اللي في المصادر النباتية — العدس والفول والسبانخ والخضار الورقي — بيتمتص بنسبة أقل بكتير من الحديد اللي في اللحوم. وامتصاصه بيتأثر بحاجات بتتاكل أو بتتشرب معاه: الشاي والقهوة فيهم مركبات بتقلل امتصاصه، والكالسيوم بيزاحمه، وفيتامين C بيزوّد امتصاصه. يعني نفس طبق العدس بالظبط ممكن يدي كمية حديد مختلفة حسب هو اتشرب معاه إيه.

والشاي بعد الأكل عادة يومية في بيوت مصرية كتير. الشخص اللي عنده أنيميا نقص حديد وبيشرب شاي بعد الغدا مباشرة بيقلل الاستفادة من أكله من غير ما يعرف — ومفيش إرشاد عام بيقول له كده، لأن الإرشاد العام مش عارف إن عنده أنيميا.

ونفس المنطق بيشتغل في اتجاهات تانية. استجابة سكر الدم لنفس الوجبة بتختلف من شخص للتاني حسب حساسية الإنسولين عنده، وحسب الوجبة اتاكلت مع إيه، وحسب التوقيت. والأدوية بتدخل في المعادلة: في أدوية بتأثر على امتصاص عناصر معينة، وفي أدوية بتغيّر الشهية، وفي أدوية توقيتها مربوط بالأكل.

فالسؤال «الأكل ده صحي؟» ناقص جزء. الجزء الناقص هو: صحي لمين، ومع إيه، وإمتى، وهو بياخد إيه.

## اللي الأبحاث والتوصيات بتقوله عن الفرق

المعهد الوطني البريطاني للصحة وجودة الرعاية (NICE) في دليله عن التعامل مع السكري من النوع التاني بيوصي بإن النصيحة الغذائية تبقى فردية ومقدَّمة من متخصص عنده كفاءة في التغذية — يعني الدليل نفسه بيفرّق بين «كلام عن الأكل» و«تدخّل غذائي».

والجمعية الأمريكية للسكري في تقريرها التوافقي بتوصف العلاج الغذائي كتدخّل له أثر قابل للقياس على مؤشرات إكلينيكية، وبتوصي بإعادة التقييم والمتابعة بدل ما يكون الأمر جلسة واحدة. المتابعة هنا مش خدمة إضافية — هي جزء من التدخّل نفسه.

الخلاصة اللي بتطلع من الاتنين واحدة: الفرق مش في المعلومة، الفرق في إن حد شاف الحالة، وبنى عليها، وبيتابع.

## خرافة: «كل الناس عارفة الأكل الصح، المشكلة في التنفيذ»

الجملة دي بتتقال كتير وبتبان حكيمة. وهي نص صح.

الجزء الصح: المعرفة العامة فعلًا منتشرة. حد ما بيعرفش إن الخضار مفيد.

الجزء الغلط: إن اللي ناقص هو التنفيذ بس. اللي ناقص غالبًا هو الترجمة — من مبدأ عام لقرار في مطبخ بعينه، بميزانية بعينها، لجسم عنده تاريخ بعينه. الشخص اللي عنده أنيميا وبياكل «صحي» ممكن يكون بياكل بطريقة بتقلل امتصاص الحديد من غير ما يعرف. ده مش فشل في التنفيذ — ده نقص في المعلومة الخاصة بحالته.

الخرافة دي منتشرة لأنها بتحوّل المشكلة كلها لمسألة انضباط شخصي، وده تفسير مريح لكل حد بره الموقف.

## في المطبخ المصري

«ظبّطي أكلك» بتترجم في بيوت كتير لحاجة من اتنين: إما امتناع كامل عن العيش والرز، أو شراء منتجات مكتوب عليها «دايت».

الاتنين غالبًا مش المطلوب.

العيش البلدي والرز والمكرونة نشويات، والنشويات جزء من الأكل. الكلام بيبقى عن الكمية والتوزيع وإيه اللي بيتاكل معاهم — الفول مع العيش غير العيش لوحده، والسلطة والخضار مع الرز غير الرز لوحده. والبقوليات المصرية — فول وعدس وحمص ولوبيا — أكل حقيقي ورخيص وموجود في كل بيت.

والمنتجات المكتوب عليها «لايت» أو «دايت» مش بالضرورة مناسبة لأي حالة بعينها؛ الكلمة دي تسويقية مش إكلينيكية.

وفي نقطة عملية: أكل البيت المصري بيتعمل غالبًا في حلة واحدة للعيلة كلها. الخطة اللي بتطلب من واحدة تطبخ لنفسها أكل تاني هي خطة بتضيف مجهود يومي على حد أصلًا مجهد. الخطة اللي بتشتغل هي اللي بتتعامل مع الحلة دي.

وفي حاجة تانية بتتنسى: الأكل مش قرار فردي في بيت مصري. الأم بتطبخ حسب اللي العيلة بتاكله، والميزانية واحدة، والوجبة واحدة. الخطة اللي بتتعامل مع ده بتقترح تعديلات على الحلة نفسها — كمية زيت أقل، خضار أكتر جنب النشوى، بقوليات أكتر — بدل ما تطلب مطبخ منفصل.

CLINICAL_INPUT: لما تيجي مريضة قايلة إن دكتورها قالها «ظبّطي أكلك» ومفيش تفاصيل — إيه أول حاجة بتعمليها في الكشف؟

## الأسئلة اللي تسأليها لما تسمعي الجملة دي

لو الجملة اتقالت من غير تفاصيل، في أسئلة بتحوّلها لحاجة قابلة للتنفيذ:

«ظبّط أكلي عشان إيه بالظبط؟» — التعديل بسبب السكر غير التعديل بسبب الضغط غير التعديل بسبب الأنيميا.

«في حاجة معينة لازم أقللها أو أوقفها بسبب حالتي أو أدويتي؟»

«في تحاليل محتاجة تتعمل الأول قبل ما أغيّر حاجة؟»

«أروح لمين؟» — ودي أهم واحدة، لأن الإجابة عليها بتحوّل الجملة من نصيحة لإحالة.

في مقال منفصل عن اللي تجهزيه قبل أول زيارة: [[article:what-to-bring-to-a-first-appointment|إيه اللي تجيبيه معاكي في أول زيارة]].

PRACTITIONER_VOICE: إيه أكتر سوء فهم بيوصل معاكي من الجملة دي؟ يعني المرضى بيفهموا منها إيه غالبًا وهو غلط؟

## اللي يستاهل تفتكريه

«ظبّطي أكلك» جملة صح، بس هي عنوان مش تعليمات. والحاجة اللي وراها ليها اسم وتعريف وخطوات، وبتتقدّم عن طريق حد مؤهل، مش كنصيحة عامة.

الفرق بين الإرشاد العام والخطة الفردية هو الفرق بين معلومة صحيحة ومعلومة قابلة للتنفيذ في حالتك إنتِ. والتقييم اللي بيعمل الفرق ده بيسأل عن التاريخ والأدوية والتحاليل وشكل يومك ومطبخك، مش عن قايمة أكل.

لو الجملة اتقالتلك ومحدش وضّحها، دي بداية معقولة: [[specialty:medical-nutrition|التغذية العلاجية]]، أو [[booking|احجزي موعد]] وابدأي من السؤال اللي محدش جاوبه.
AR,

                'en' => <<<'EN'
The sentence arrives in the last minute of a consultation: "sort your diet out." It is said in good faith, by a respectable doctor, usually while a prescription is being written.

The difficulty is that it is not an instruction. It is the title of something large, and nobody has said what is inside it. This article is about what is inside it.

## In medicine, this thing has a name

What gets called "sorting your diet out" in ordinary speech has a specific name in the medical literature: medical nutrition therapy.

The European Society for Clinical Nutrition and Metabolism (ESPEN) produced a guideline devoted specifically to the definitions and terminology of clinical nutrition, because these words in particular had been used with different meanings in different places. The existence of a whole guideline defining the terms is itself a piece of information: this is a field with boundaries and a vocabulary, not a set of general suggestions.

And the American Diabetes Association (ADA), in a consensus report devoted to nutrition therapy for adults with diabetes or prediabetes, treats nutrition therapy as a therapeutic intervention with steps: assessment, an individual plan, follow-up, and adjustment. The same association, in its annual Standards of Care, recommends that nutrition therapy be delivered by a qualified dietitian rather than offered as general advice at the end of a consultation.

So the sentence you were given was not wrong — it was a referral. It was simply a referral that was never actually made.

## The difference between general advice and a plan

The World Health Organization has clear guidance on healthy eating: vegetables and fruit, less added sugar, less salt, less saturated fat. That is correct, important, published and free.

But it should be said plainly what it is: guidance aimed at whole populations. It is designed to reduce the risk of chronic disease at the level of a country. It is not a plan for one person with a particular history, particular lab results, particular medications and a household with other people in it.

The difference between the two is the difference between a traffic map of Cairo and the route from your house to your work. The map is entirely correct, and on its own it will not get you there.

## What an assessment actually involves

A serious nutritional assessment is not the question "what do you eat?". It is a set of questions, each of which changes the plan if the answer changes:

Medical history and medications. Some medications affect appetite, the absorption of particular nutrients, blood glucose, and weight. And some conditions impose real constraints — kidneys, liver, thyroid, allergy.

Lab results where there is a clinical reason for them. Not every patient needs tests, and not every test changes a decision.

The shape of the day. When you wake, where you eat, whether you cook or somebody else does, whether you work shifts. A plan that assumes three meals at home, written for somebody who is out of the house for twelve hours, is a plan written for a different person.

The history of previous attempts. What was tried, what worked, what stopped, and why it stopped. This is the most useful information in the consultation and the most often left out.

And preferences, budget and family. The plan that gets followed is the one that counts.

## Why history matters more than a list of foods

Somebody with insulin resistance, somebody with iron-deficiency anaemia, and somebody with irritable bowel can all be eating roughly the same food, all need entirely different adjustments, and some adjustment that helps one of them may be unsuitable for another.

That is why "healthy eating" is not a sufficient answer. The question is not "what is the right food?" — it is "what is the right food for a case like this?".

## Why the same food produces different results in different people

This is the point that explains why general guidance is not enough, and it is physiological rather than philosophical.

Take iron. The iron in plant sources — lentils, foul, spinach, leafy greens — is absorbed far less efficiently than the iron in meat. And its absorption is affected by what is eaten or drunk alongside it: tea and coffee contain compounds that reduce it, calcium competes with it, and vitamin C increases it. So the identical plate of lentils can deliver a different amount of usable iron depending on what was drunk with it.

Tea after a meal is a daily habit in a great many Egyptian households. Somebody with iron-deficiency anaemia who drinks tea straight after lunch is reducing what she gets from her own food without knowing it — and no general guidance is going to tell her so, because general guidance does not know she is anaemic.

The same logic runs in other directions. The blood glucose response to an identical meal differs between people according to insulin sensitivity, what the meal was eaten with, and when. And medications enter the equation: some affect the absorption of particular nutrients, some change appetite, and some have timings tied to food.

So the question "is this food healthy?" is missing a part. The missing part is: healthy for whom, alongside what, at what time, and on what medication.

## What the research and the guidance say about the difference

The National Institute for Health and Care Excellence (NICE), in its guidance on managing type 2 diabetes in adults, recommends that dietary advice be individual and delivered by somebody with competence in nutrition — the guideline itself distinguishes between "talking about food" and a nutritional intervention.

And the ADA consensus report describes nutrition therapy as an intervention with a measurable effect on clinical markers, and recommends reassessment and follow-up rather than a single session. Follow-up here is not an added service — it is part of the intervention.

The conclusion from both is the same: the difference is not in the information. The difference is that somebody has seen the case, built on it, and is following it.

## The myth: "everybody knows what healthy eating is, the problem is doing it"

This gets said often and sounds wise. It is half right.

The right half: general knowledge really is widespread. Nobody is unaware that vegetables are good for them.

The wrong half: that what is missing is only execution. What is usually missing is translation — from a general principle to a decision in a particular kitchen, on a particular budget, for a body with a particular history. Somebody with anaemia who eats "healthily" may be eating in a way that reduces iron absorption without knowing it. That is not a failure of execution — it is a gap in the information specific to her case.

The myth persists because it converts the entire problem into a matter of personal discipline, which is a comfortable explanation for everybody outside the situation.

## In an Egyptian kitchen

"Sort your diet out" gets translated in a great many households into one of two things: cutting out bread and rice entirely, or buying products with "diet" written on the packet.

Neither is usually what was meant.

Baladi bread, rice and pasta are starches, and starch is part of eating. The conversation is about quantity, distribution, and what gets eaten alongside them — foul with bread is not bread on its own, and salad and vegetables with rice is not rice on its own. And Egyptian legumes — foul, lentils, chickpeas, black-eyed beans — are real food, inexpensive, and already in every house.

Products labelled "light" or "diet" are not necessarily suitable for any particular case; the word is a marketing term, not a clinical one.

And a practical point: Egyptian home cooking is usually one pot for the whole family. A plan that requires one woman to cook herself something separate is a plan that adds daily work to somebody who is already tired. The plan that works is the one that deals with that pot.

And something else gets forgotten: eating is not an individual decision in an Egyptian household. The mother cooks according to what the family eats, the budget is one budget, the meal is one meal. A plan that deals with this proposes adjustments to the pot itself — less oil, more vegetables alongside the starch, more legumes — rather than asking for a separate kitchen.

CLINICAL_INPUT: When a patient arrives saying her doctor told her to sort her diet out, with no detail, what is the first thing you do in the consultation?

## The questions to ask when you hear that sentence

If the sentence arrived without detail, there are questions that turn it into something actionable:

"Sort it out for what, specifically?" — an adjustment because of glucose is not an adjustment because of blood pressure is not an adjustment because of anaemia.

"Is there something I specifically need to reduce or stop because of my condition or my medications?"

"Are there tests that should be done before I change anything?"

"Who should I see?" — and this is the important one, because answering it turns the sentence from advice into a referral.

There is a separate article on what to prepare beforehand: [[article:what-to-bring-to-a-first-appointment|What to bring to a first appointment]].

PRACTITIONER_VOICE: What is the most common misunderstanding that reaches you from this sentence — what do patients usually take it to mean that is wrong?

## Worth remembering

"Sort your diet out" is a correct sentence, but it is a title rather than an instruction. The thing behind it has a name, a definition and steps, and is delivered by somebody qualified rather than offered as general advice.

The difference between population guidance and an individual plan is the difference between correct information and information you can act on in your own case. And the assessment that makes that difference asks about history, medications, lab work, the shape of your day and your kitchen — not about a list of foods.

If the sentence was said to you and nobody explained it, this is a reasonable place to start: [[specialty:medical-nutrition|medical nutrition therapy]], or [[booking|book an appointment]] and begin from the question nobody answered.
EN,
            ],

            'citations' => [
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
                    'note' => 'Supports "ESPEN produced a guideline devoted specifically to the definitions '
                        .'and terminology of clinical nutrition", used to establish that this is a field with '
                        .'a defined vocabulary. Confident the guideline exists; confirm the year and the '
                        .'exact title.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الجمعية الأمريكية للسكري (ADA)',
                        'en' => 'American Diabetes Association (ADA)',
                    ],
                    'title' => [
                        'ar' => 'العلاج الغذائي للبالغين المصابين بالسكري أو ما قبل السكري: تقرير توافقي',
                        'en' => 'Nutrition Therapy for Adults With Diabetes or Prediabetes: A Consensus Report',
                    ],
                    'year' => 2019,
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports the description of nutrition therapy as a therapeutic intervention '
                        .'with assessment, an individual plan, follow-up and adjustment. Widely cited '
                        .'consensus report; confirm the year and that this framing is explicit rather than '
                        .'inferred from the structure.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الجمعية الأمريكية للسكري (ADA)',
                        'en' => 'American Diabetes Association (ADA)',
                    ],
                    'title' => [
                        'ar' => 'معايير الرعاية في السكري',
                        'en' => 'Standards of Care in Diabetes',
                    ],
                    'year' => 2025,
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports "recommends that nutrition therapy be delivered by a qualified '
                        .'dietitian". The Standards are reissued annually — confirm the current edition at '
                        .'verification and update the year here, since citing a superseded edition of an '
                        .'annual document is the most likely way this article dates.',
                ],
                [
                    'organisation' => [
                        'ar' => 'المعهد الوطني للصحة وجودة الرعاية (NICE)',
                        'en' => 'National Institute for Health and Care Excellence (NICE)',
                    ],
                    'title' => [
                        'ar' => 'السكري من النوع الثاني عند البالغين: التعامل',
                        'en' => 'Type 2 diabetes in adults: management',
                    ],
                    'year' => 2015,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "recommends that dietary advice be individual and delivered by '
                        .'somebody with competence in nutrition". Confident the guideline exists and covers '
                        .'dietary advice; the year given is the original publication and it has been updated '
                        .'repeatedly — confirm the current version and that the competence wording survives.',
                ],
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => ['ar' => 'صحيفة وقائع: النظام الغذائي الصحي', 'en' => 'Fact sheet: Healthy diet'],
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports the summary of WHO healthy-eating guidance (vegetables and fruit, '
                        .'less free sugar, less salt, less saturated fat) and the article\'s framing of it as '
                        .'population-level guidance. Revised periodically, so no year is given — record the '
                        .'revision date current at verification.',
                ],
            ],
        ];
    }
}
