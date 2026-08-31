<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * The PDF that circulates on WhatsApp, judged fairly.
 *
 * The argument is deliberately not "generic plans are bad". It is that a plan
 * is a set of assumptions about a person, that a downloadable one cannot know
 * whether its assumptions hold, and that the ADA's own consensus position —
 * no single eating pattern is ideal for everybody — is the strongest available
 * statement of the point, made by a body with no stake in this clinic.
 */
class DietitianVersusDownloadablePlan extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'dietitian-versus-downloadable-plan',
            'category' => 'medical-nutrition',
            'tags' => ['first-visit'],
            'cover' => 'consultation-meal-plan',

            'title' => [
                'ar' => 'النظام الجاهز والخطة الفردية — الفرق فين بالظبط؟',
                'en' => 'A downloadable plan and an individual one — where is the difference?',
            ],

            'excerpt' => [
                'ar' => 'الخطة الجاهزة مش «وحشة». هي بتفترض حاجات عن جسمك من غير ما تسألك عنها — والمقال ده عن الافتراضات دي.',
                'en' => 'A ready-made plan is not "bad". It makes assumptions about your body without asking you about them — and this article is about those assumptions.',
            ],

            'body' => [
                'ar' => <<<'AR'
كل واحدة تقريبًا وصلها في وقت من الأوقات ملف PDF على واتساب: «نظام أسبوعين»، «نظام الكيتو المصري»، «نظام الدكتور فلان». مجاني، جاهز، وفيه جداول.

المقال ده مش عن إن الملفات دي وحشة. هو عن حاجة أدق: كل خطة أكل هي مجموعة افتراضات عن الشخص اللي هياكلها — والملف الجاهز بيفترض من غير ما يسأل، ومن غير ما يقول إنه بيفترض.

## الخطة الجاهزة بتفترض إيه من غير ما تقول

أي جدول أكل مكتوب فيه «الفطار: كذا» بيفترض ضمنيًا:

إن مفيش حالة مرضية بتفرض قيود. إن مفيش أدوية بتتفاعل مع الأكل ده أو مع توقيته. إن مفيش حساسية أو عدم تحمّل. إن الاحتياج من الطاقة والبروتين مناسب لشخص بالطول والوزن والنشاط دول. إن الأكل المكتوب متاح في السوق وفي الميزانية. وإن اليوم بتاعك شكله زي اليوم اللي الجدول اتكتب له.

كل افتراض من دول ممكن يكون صح. المشكلة إن الملف مش عارف، ومش قادر يعرف، وأهم من كده: لو افتراض منهم غلط، مفيش حاجة في الملف هتقول لك.

## اللي التوصيات المنشورة بتقوله عن «النظام الأمثل»

هنا في جملة تستاهل تتقري بالظبط. الجمعية الأمريكية للسكري (ADA)، في تقريرها التوافقي عن العلاج الغذائي، بتقول إن مفيش نمط غذائي واحد مثالي لكل الناس المصابين بالسكري، وإن الخطة لازم تتفرد حسب الحالة والتفضيلات والأهداف.

خلي بالك مين اللي بيقول ده. مش عيادة بتبيع متابعة — دي جهة مرجعية بتصدر معايير سنوية بيشتغل عليها أطباء في العالم كله. ولو كان في نظام واحد أفضل من غيره لكل الناس، دي بالظبط الجهة اللي كانت هتقوله.

والمعهد الوطني البريطاني (NICE) بيمشي في نفس الاتجاه: النصيحة الغذائية تبقى فردية، ومقدَّمة من حد عنده كفاءة في التغذية.

والاتحاد الأوروبي لدراسة السمنة (EASO) بيوصي بنفس المنطق في إدارة السمنة: تقييم فردي، أهداف واقعية، ومتابعة ممتدة.

## ليه ده مش مجرد رأي — الفسيولوجي ورا الكلام

فيه أسباب ملموسة بتخلي نفس الخطة تدي نتايج مختلفة:

استجابة سكر الدم لنفس الوجبة بتختلف من شخص للتاني، حسب حساسية الإنسولين وحسب الوجبة اتاكلت مع إيه وإمتى. اللي بيحصل جوه الجسم مش نفس اللي مكتوب في الجدول.

الامتصاص بيختلف. الحديد النباتي بيتأثر بالشاي والقهوة والكالسيوم اللي بيتاخدوا معاه، وبيتحسن مع فيتامين C. جدول فيه عدس وشاي بعده مباشرة بيدي حديد أقل من نفس الجدول من غير الشاي — والملف مش عارف إن في أنيميا أصلًا.

والأدوية. في أدوية بتتاخد قبل الأكل وأدوية بعده، وفي أدوية بتقلل امتصاص عناصر معينة، وفي أدوية بتغيّر الشهية. الجدول اللي مش عارف الروشتة بتاعتك مش قادر ياخد ده في الحساب.

والحالات المزمنة. الكلى والكبد والغدة الدرقية والقولون كل واحدة فيها بتغيّر شكل الخطة المناسبة، وبعض التعديلات المفيدة في حالة ممكن تكون غير مناسبة في حالة تانية.

## الأنظمة قليلة السعرات جدًا — نقطة تستاهل الوقوف عندها

في نوع من الخطط بتنتشر على واتساب وبتكون منخفضة السعرات بشكل كبير جدًا وسريعة النتيجة.

المعهد الوطني البريطاني للصحة وجودة الرعاية بيتعامل مع الأنظمة منخفضة السعرات جدًا كتدخّل ليه مكان محدود، وبيوصي إنها متتستخدمش كحل عام، وإن استخدامها يبقى ضمن برنامج متكامل وتحت إشراف إكلينيكي.

يعني الحاجة اللي بتتبعت في ملف مجاني هي بالظبط الحاجة اللي التوصيات بتقول إنها محتاجة إشراف. ودي مش تفصيلة إجرائية — دي المسافة بين تدخّل طبي وحاجة اتنسخت من مجموعة.

## طب الملفات دي مفيدة في حاجة؟

آه، وده لازم يتقال بإنصاف.

المعلومات العامة المنشورة من جهات محترمة — زي إرشادات منظمة الصحة العالمية عن الأكل الصحي — مفيدة فعلًا، ومجانية، وبتغطي حاجات صح لمعظم الناس: خضار وفاكهة أكتر، سكر مضاف أقل، ملح أقل. حد بيقرا الكلام ده وبيطبق منه حاجة بيبقى أحسن من حد مش عارفه.

الفرق إن الكلام ده اسمه إرشاد سكاني، ومصمم كده. هو بيقلل الخطر على مستوى بلد، مش بيدير حالة فردية. والملف اللي بيتوزع على واتساب غالبًا مش ده أصلًا — هو جدول صارم بوجبات محددة، وده حاجة تانية خالص: الإرشاد بيقول اتجاه، والجدول بيدّعي إنه يعرف التفاصيل.

## إزاي تحكمي على ملف موجود معاكي دلوقتي

في أسئلة بتفرز بسرعة:

الملف بيقول مين كتبه ومؤهله إيه؟ الملف اللي مالوش اسم مالوش مسؤول.

بيسأل عن أي حاجة عنك قبل ما يدّي جدول؟ لو الجواب لأ، فهو نفس الجدول لكل حد.

فيه كميات صارمة وممنوعات مطلقة؟ ده مؤشر على خطة اتكتبت لمجهول، لأن الحد الفاصل بيتحدد بالحالة.

بيوعد بمعدل نزول محدد في وقت محدد؟ الوعد ده مش معلومة، ده تسويق — لأن اللي بيوعد مش عارف وزنك ولا حالتك ولا أدويتك.

فيه مكونات مش موجودة في السوق المصري أو أغلى من ميزانيتك؟ يبقى مكتوب لمكان تاني، ومش هيكمل معاكي أسبوعين.

بيقول تعملي إيه لو حصل حاجة — دوخة، تعب، سكر واطي؟ الملف اللي مفيهوش الجزء ده هو الجزء الأخطر فيه.

## خرافة: «النظام ده نفع صاحبتي، يبقى هينفعني»

الخرافة دي منطقية على السطح، وعشان كده هي أقوى واحدة في الموضوع ده كله.

ليه بتنتشر: لأن الشخص اللي نجح بيحكي، والشخص اللي مانفعش معاه مش بيحكي. اللي بيوصلك هو الحالات اللي نفعت بس، فبتبان النسبة أعلى بكتير من الحقيقة. وده قبل ما نحسب إن الشخصين ممكن يكونوا مختلفين في الوزن والنشاط والحالة المرضية والأدوية والسن وشكل اليوم.

واللي بيحصل بعد كده أسوأ من عدم النتيجة. الشخص اللي جرب نظام نفع مع غيره ومانفعش معاه بيستنتج إن المشكلة فيه هو، مش في إن الخطة اتعملت لجسم تاني. والاستنتاج ده بيتراكم ويخلي المحاولة الجاية أصعب.

الأكل اللي نفع مع صاحبتك ممكن فعلًا يكون كويس. السؤال مش «هو كويس؟» — السؤال «هو مناسب لحالة زي حالتك؟»، وده سؤال محدش جاوبه.

## في المطبخ المصري: الخطط المترجمة

في مشكلة عملية في معظم الملفات اللي بتتوزع هنا: هي مترجمة أو منقولة من محتوى أجنبي، والأكل اللي فيها مش أكل بيوتنا.

جدول فيه «شوفان بالتوت الأزرق» و«صدر ديك رومي» و«كينوا» هو جدول لسوق تاني وميزانية تانية. اللي بيحصل إن الشخص بيحاول أسبوع، ويلاقي المكونات غالية أو مش موجودة، وبعدين يسيب — وياخد الإحساس إن الالتزام صعب، مع إن اللي كان صعب هو التسوّق.

والبديل مش أقل جودة. الفول والعدس والحمص واللوبيا بقوليات، والبيض والجبنة القريش والزبادي مصادر بروتين متاحة، والسمك البلدي والفراخ موجودين، والخضار الموسمي في السوق أرخص وأطزج من أي حاجة مستوردة. والملوخية والمسقعة والبامية والشوربة أكل حقيقي.

الخطة اللي بتشتغل في مصر هي اللي بتبدأ من الحلة اللي في المطبخ، مش من جدول اتكتب في مكان تاني.

CLINICAL_INPUT: لما تيجي مريضة بملف نظام جاهز وتسألك «ده كويس؟» — إنتِ بتردي إزاي؟

## طب إيه اللي بتدفعي فيه فعلًا؟

سؤال عادل ويستاهل إجابة صريحة. اللي بيتدفع فيه مش قايمة أكل — القايمة أرخص حاجة في الموضوع.

اللي بيتدفع فيه هو: تقييم بيسأل عن التاريخ والأدوية والتحاليل وشكل اليوم، وربط الخطة بالحالة دي بالذات، ومتابعة بتعدّل لما حاجة متنفعش، وحد مسؤول قدامك لما تسألي.

الجزء الأخير ده هو الفرق الحقيقي. الملف مش بيتابع، ومش بيعدّل، ومش بيرد. ولو حصلت مشكلة، مفيش حد.

وفي حاجة الملف مش بيعملها بطبيعته: بيقول لك إمتى تبطلي. الخطة الفردية فيها نقطة إعادة تقييم — بعد فترة معينة بيتشاف إيه اتحرك وإيه لأ، والخطة بتتغير. الملف مالوش نهاية ومالوش مراجعة، فالشخص بيفضل ماشي عليه لحد ما يزهق، وبعدين يسيبه فجأة من غير خروج مخطط. والخروج المفاجئ ده هو اللي بيرجّع اللي حصل غالبًا.

PRACTITIONER_VOICE: إيه أكتر خطة جاهزة شوفتيها بتتكرر عند المرضى؟ وإيه الحاجة اللي بتقلقك فيها لما تشوفيها؟

والمتابعة بتعمل حاجة تانية مش واضحة من بره: بتفرّق بين «الخطة مش شغالة» و«الخطة مش بتتنفذ»، والاتنين شكلهم واحد من بعيد وعلاجهم مختلف تمامًا.

## اللي يستاهل تفتكريه

الخطة الجاهزة مش عدوة. هي مجموعة افتراضات عن شخص مش إنتِ، اتكتبت من غير ما حد يشوف حالتك.

والجهات المرجعية نفسها — ADA وNICE وEASO — بتقول إن مفيش نمط غذائي واحد مناسب للكل، وإن التفريد والمتابعة جزء من التدخل. ودي جهات مالهاش مصلحة في إنك تحجزي عند حد.

لو عندك ملف جاهز دلوقتي وحابة تعرفي هو مناسب لحالتك ولا لأ، ده سؤال بيتجاوب في كشف واحد: [[specialty:medical-nutrition|التغذية العلاجية]] أو [[booking|احجزي موعد]] وهاتي الملف معاكي.

ولو حابة تعرفي الأول إيه اللي بيتسأل في التقييم، في مقال عن ده: [[article:what-fix-your-diet-actually-means|«ظبّطي أكلك» معناها إيه بالظبط]].
AR,

                'en' => <<<'EN'
Almost everybody has, at some point, been sent a PDF on WhatsApp: "the two-week plan", "the Egyptian keto plan", "Dr So-and-so's plan". Free, ready to use, and full of tables.

This article is not about those files being bad. It is about something more precise: every eating plan is a set of assumptions about the person who is going to eat it — and a ready-made file assumes without asking, and without saying that it is assuming.

## What a ready-made plan assumes without saying so

Any table that says "breakfast: such and such" is implicitly assuming:

That there is no medical condition imposing constraints. That there are no medications interacting with this food or its timing. That there is no allergy or intolerance. That the energy and protein requirement suits somebody of this height, weight and activity level. That the food listed is available in the market and within the budget. And that your day looks like the day the table was written for.

Every one of those assumptions may be correct. The difficulty is that the file does not know, cannot know, and — most importantly — if one of them is wrong, nothing in the file will tell you.

## What published guidance says about "the best plan"

There is a sentence here worth reading exactly. The American Diabetes Association (ADA), in its consensus report on nutrition therapy, states that there is no single ideal eating pattern for everybody with diabetes, and that the plan must be individualised to the case, the preferences and the goals.

Notice who is saying it. Not a clinic selling follow-up — a reference body issuing annual standards that doctors around the world work from. If there were one pattern better than the others for everybody, this is precisely the organisation that would have said so.

NICE goes in the same direction: dietary advice should be individual, delivered by somebody with competence in nutrition.

And the European Association for the Study of Obesity (EASO) applies the same logic to obesity management: individual assessment, realistic goals, sustained follow-up.

## Why this is not merely an opinion — the physiology behind it

There are concrete reasons the same plan produces different results:

The blood glucose response to an identical meal differs between people, according to insulin sensitivity and according to what the meal was eaten with and when. What happens inside the body is not what is written in the table.

Absorption differs. Iron from plants is affected by tea, coffee and calcium taken alongside it, and improved by vitamin C. A table with lentils followed immediately by tea delivers less iron than the same table without the tea — and the file does not know there is anaemia in the first place.

Medications. Some are taken before food and some after; some reduce the absorption of particular nutrients; some change appetite. A table that does not know your prescription cannot account for any of it.

And chronic conditions. Kidneys, liver, thyroid and bowel each change the shape of an appropriate plan, and an adjustment that helps in one case may be unsuitable in another.

## Very low calorie plans — a point worth stopping on

There is a category of plan that circulates on WhatsApp with a very low calorie content and a fast result.

NICE treats very low calorie diets as an intervention with a limited place, recommends against using them as a general solution, and advises that where they are used it should be within a broader programme and under clinical supervision.

So the thing being sent around in a free file is precisely the thing the guidance says needs supervision. That is not a procedural detail — it is the distance between a medical intervention and something copied out of a group chat.

## Are these files useful for anything?

Yes, and that should be said fairly.

General information published by respectable bodies — WHO guidance on healthy eating, for instance — really is useful, is free, and covers things that are true for most people: more vegetables and fruit, less added sugar, less salt. Somebody who reads that and applies part of it is better off than somebody who has never seen it.

The distinction is that this is population guidance, and is designed as such. It reduces risk at the level of a country; it does not manage an individual case. And the file circulating on WhatsApp is usually not that at all — it is a rigid table of specified meals, which is a different thing entirely: guidance states a direction, while a table claims to know the details.

## How to judge a file you already have

There are questions that sort quickly:

Does the file say who wrote it and what their qualification is? A file with no name attached has nobody answerable for it.

Does it ask anything about you before giving you a table? If not, then it is the same table for everybody.

Does it carry rigid quantities and absolute prohibitions? That is a sign of a plan written for a stranger, because where the line falls is determined by the case.

Does it promise a specific rate of loss in a specific time? That promise is not information, it is marketing — whoever made it does not know your weight, your condition or your medications.

Does it contain ingredients not sold in the Egyptian market, or costing more than your budget? Then it was written for somewhere else, and it will not survive a fortnight.

Does it say what to do if something happens — dizziness, exhaustion, low blood sugar? A file with no such section is at its most dangerous exactly there.

## The myth: "this plan worked for my friend, so it will work for me"

This myth is reasonable on the surface, which is what makes it the strongest one in the whole subject.

Why it spreads: the person it worked for talks about it, and the person it did not work for does not. Only the successes reach you, so the success rate looks far higher than it is. And that is before accounting for the fact that two people may differ in weight, activity, medical condition, medications, age and the shape of their day.

What happens afterwards is worse than simply not working. Somebody who tries a plan that worked for others and does not get the same result concludes that the problem is her, rather than that the plan was built for a different body. That conclusion accumulates, and makes the next attempt harder.

The food that worked for your friend may genuinely be good food. The question is not "is it good?" — it is "is it appropriate for a case like yours?", and nobody has answered that.

## In an Egyptian kitchen: the translated plans

There is a practical problem with most of the files that circulate here: they are translated or adapted from foreign content, and the food in them is not the food of our houses.

A table with oats and blueberries, turkey breast and quinoa is a table for a different market and a different budget. What happens is that somebody tries for a week, finds the ingredients expensive or unavailable, and stops — taking away the sense that sticking to things is hard, when what was hard was the shopping.

And the alternative is not inferior. Foul, lentils, chickpeas and black-eyed beans are legumes; eggs, areesh cheese and yoghurt are available protein; local fish and chicken exist; seasonal vegetables in the market are cheaper and fresher than anything imported. Molokhia, moussaka, okra and soup are real food.

The plan that works in Egypt is the one that starts from the pot already in the kitchen, not from a table written somewhere else.

CLINICAL_INPUT: When a patient arrives with a ready-made plan and asks whether it is any good, how do you answer?

## So what are you actually paying for?

A fair question that deserves a straight answer. What is paid for is not a list of foods — the list is the cheapest part of the whole thing.

What is paid for is: an assessment that asks about history, medications, lab work and the shape of the day; a plan tied to that particular case; follow-up that adjusts when something does not work; and somebody answerable to you when you ask.

That last part is the real difference. A file does not follow up, does not adjust, and does not reply. And if something goes wrong, there is nobody.

And there is something a file cannot do by its nature: tell you when to stop. An individual plan has a point of reassessment — after a defined period somebody looks at what moved and what did not, and the plan changes. A file has no ending and no review, so people carry on with it until they are tired of it and then drop it abruptly, with no planned exit. That abrupt exit is usually what undoes the result.

PRACTITIONER_VOICE: Which ready-made plan do you see most often among patients? And what worries you about it when you see it?

Follow-up also does something not obvious from outside: it distinguishes between "the plan is not working" and "the plan is not being followed". Those look identical from a distance and their remedies are entirely different.

## Worth remembering

A ready-made plan is not an enemy. It is a set of assumptions about somebody who is not you, written without anybody having seen your case.

And the reference bodies themselves — the ADA, NICE, EASO — say there is no single eating pattern suitable for everyone, and that individualisation and follow-up are part of the intervention. Those bodies have no interest in whether you book with anybody.

If you have a file now and want to know whether it suits your case, that is a question answered in a single consultation: [[specialty:medical-nutrition|medical nutrition therapy]] or [[booking|book an appointment]] and bring the file with you.

And if you would rather first know what an assessment asks about, there is an article on that: [[article:what-fix-your-diet-actually-means|what "sort your diet out" actually means]].
EN,
            ],

            'citations' => [
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
                    'note' => 'Carries the load of this whole article: "there is no single ideal eating '
                        .'pattern for everybody with diabetes, and the plan must be individualised". This is '
                        .'a well-known statement in the report and the article leans on it hard, so it is '
                        .'the first one to check. Confirm the wording covers eating PATTERN rather than only '
                        .'macronutrient percentages.',
                ],
                [
                    'organisation' => [
                        'ar' => 'المعهد الوطني للصحة وجودة الرعاية (NICE)',
                        'en' => 'National Institute for Health and Care Excellence (NICE)',
                    ],
                    'title' => [
                        'ar' => 'السمنة: التعرّف والتقييم والتعامل',
                        'en' => 'Obesity: identification, assessment and management',
                    ],
                    'year' => 2014,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the section on very low calorie diets: limited place, not a general '
                        .'solution, and only within a broader programme under clinical supervision. '
                        .'Confident the guideline addresses very low calorie diets; confirm the supervision '
                        .'wording and that it survives in the current edition.',
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
                    'note' => 'Supports "dietary advice should be individual, delivered by somebody with '
                        .'competence in nutrition". Original publication year given; the guideline has been '
                        .'updated repeatedly, so confirm the current version.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الاتحاد الأوروبي لدراسة السمنة (EASO)',
                        'en' => 'European Association for the Study of Obesity (EASO)',
                    ],
                    'title' => [
                        'ar' => 'الإرشادات الأوروبية لإدارة السمنة عند البالغين',
                        'en' => 'European Guidelines for Obesity Management in Adults',
                    ],
                    'year' => 2015,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "individual assessment, realistic goals, sustained follow-up". '
                        .'Confirm the exact title and year.',
                ],
            ],
        ];
    }
}
