<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * The companion to the PCOS claim-judging article: that one is about what
 * reaches you online, this one is about what happens in the room.
 *
 * The diagnostic section is the most useful part and the most carefully
 * handled. The international guideline's position on ultrasound in the years
 * after menarche is a genuinely valuable thing for a young woman to know,
 * because being labelled with a lifelong condition at seventeen on the basis
 * of a scan is a real harm — and it is reported as the guideline's position,
 * with the reasoning, rather than as a suggestion that anybody's diagnosis is
 * wrong.
 */
class QuestionsToAskAboutHormonesAndFood extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'questions-to-ask-about-hormones-and-food',
            'category' => 'pcos-hormonal',
            'tags' => ['questions-to-ask'],
            'cover' => 'food-market-counter',

            'title' => [
                'ar' => 'أسئلة تسأليها عن الهرمونات والأكل',
                'en' => 'Questions to ask about hormones and food',
            ],

            'excerpt' => [
                'ar' => 'الكشف بيعدي بسرعة والأسئلة بتتفتكر في العربية. دي الأسئلة اللي بتغيّر الخطة فعلًا.',
                'en' => 'A consultation goes quickly and the questions come back to you in the car. These are the ones that actually change the plan.',
            ],

            'body' => [
                'ar' => <<<'AR'
معظم الناس بتخرج من الكشف ومعاها ورقة وتشخيص، والأسئلة بتيجي بعدين — في العربية، أو بالليل، أو لما حد يسأل «هو قالك إيه بالظبط؟».

المقال ده مجموعة أسئلة تستحق تتسأل في كشف عن الهرمونات والأكل. مش عشان تشكّي في حد، لكن عشان الإجابات دي هي اللي بتحدد شكل الخطة، وأغلبها مش بيتقال من نفسه لأن الوقت ضيق.

## اسألي عن التشخيص نفسه: اتحدد إزاي؟

ده أول سؤال وأهمه، وأقل واحد بيتسأل.

متلازمة تكيس المبايض بتتشخص بمجموعة معايير مش بحاجة واحدة: اضطراب أو غياب التبويض، وعلامات زيادة الأندروجينات (إكلينيكية أو في التحاليل)، وشكل المبيض في السونار. والتشخيص بيتطلب استبعاد حالات تانية ليها أعراض متشابهة — زي مشاكل الغدة الدرقية وارتفاع هرمون البرولاكتين.

يعني السونار لوحده مش تشخيص. ودي معلومة عملية جدًا، لأن «المبيض شكله متكيس في السونار» جملة بتتقال كتير وبتتحول لتشخيص عمر بحاله.

والدليل الدولي المبني على الأدلة لتقييم وإدارة تكيس المبايض، في نسخته المحدّثة سنة ٢٠٢٣، بياخد النقطة دي أبعد: بيوصي بعدم استخدام السونار للتشخيص في السنوات الأولى بعد أول دورة، لأن شكل المبيض في السن ده بيبقى متغيّر بطبيعته، وبيتشابه مع الشكل اللي بيتحسب علامة. وبيتعامل مع هرمون AMH كبديل ممكن للسونار في البالغات.

فالسؤال اللي يتسأل: التشخيص اتبنى على إيه بالظبط؟ وهل اتعمل استبعاد للحالات التانية؟

## اسألي عن التحاليل: اتعمل إيه وليه؟

«التحاليل تمام» جملة معناها بيعتمد على اتعمل إيه.

اسألي عن قايمة التحاليل نفسها، مش الخلاصة. واسألي عن كل تحليل: هو بيقيس إيه، والنتيجة كام، والنطاق كام.

واسألي لو في حاجة قريبة من الحافة. دي مش وسوسة — ده سؤال إكلينيكي مشروع، والقراية اللي عند الحافة بتتفسر مع الأعراض مش لوحدها.

ولو الشكوى فيها تعب مستمر، في مقال منفصل عن معنى كلمة «طبيعي» في ورقة التحليل: [[article:normal-results-still-tired|التحاليل سليمة وأنا لسه تعبانة]].

## اسألي عن الميكانيكية: إيه اللي بيحصل في جسمي أصلًا؟

السؤال ده بيبان نظري، وهو عملي جدًا: الشخص اللي فاهم ليه بيعمل حاجة بيكمّل فيها أطول من الشخص اللي حافظ إنه لازم يعملها.

الفكرة المركزية في جزء كبير من حالات تكيس المبايض هي مقاومة الإنسولين. الإنسولين هرمون بيتفرز من البنكرياس بعد الأكل، وشغلته إنه يخلي الخلايا تسحب السكر من الدم. في مقاومة الإنسولين الخلايا بتستجيب بكفاءة أقل، فالبنكرياس بيفرز كمية أكبر عشان يوصل لنفس النتيجة، ومستوى الإنسولين في الدم بيفضل أعلى من المعتاد.

والجزء اللي بيربط ده بالهرمونات: الإنسولين مش بيشتغل على السكر بس. مستوياته العالية بتأثر على المبيض وعلى إنتاج الأندروجينات، والأندروجينات الزيادة مرتبطة بأعراض زي الشعر الزايد وحب الشباب واضطراب التبويض.

فاسألي: في مقاومة إنسولين في حالتي؟ واتحددت إزاي؟ والتعديل اللي بتقترحيه بيشتغل على النقطة دي إزاي؟

## اسألي عن الحاجات اللي بتتفحص جنبها

تكيس المبايض مش موضوع دورة وشعر بس. الدليل الدولي بيتعامل مع الحالة كحالة ليها أبعاد أيضية ونفسية كمان، وبيوصي بالانتباه لمؤشرات زي سكر الدم والدهون والضغط، وبالسؤال عن الحالة المزاجية والقلق.

الجزء النفسي ده بيتساب دايمًا، وهو مش تفصيلة: حالة مزمنة بتمس الشكل والخصوبة وبتتشخص في سن صغير ليها تأثير حقيقي، والدليل بيقول ده صراحة.

فاسألي: في حاجات تانية المفروض تتابع مع الوقت؟ وكل قد إيه؟

## واسألي عن الدورة نفسها

لو الدورة غزيرة أو غير منتظمة، دي معلومة ليها علاقة مباشرة بالأكل من ناحية مش واضحة: الفقد المتكرر للدم بيستهلك مخزون الحديد، ومخزون الحديد بيقل قبل ما الهيموجلوبين يتأثر.

فاسألي: هل الدورة عندي ليها علاقة بمخزون الحديد؟ وهل ده اتقيّم؟

## اسألي عن الهدف: إحنا بنعالج إيه بالظبط؟

دي النقطة اللي بتغيّر الخطة أكتر من أي حاجة تانية، وبتتساب غالبًا.

الشخص اللي جاي عشان الدورة غير الشخص اللي جاي عشان الخصوبة غير الشخص اللي جاي عشان الشعر الزايد غير الشخص اللي جاي عشان مؤشرات السكر. الأهداف دي مش متعارضة، بس هي مش نفس الخطة ومش نفس الأولوية ومش نفس الجدول الزمني.

فاسألي: الخطة دي بتستهدف إيه أول حاجة؟ ولو عندي أكتر من هدف، إيه اللي بيتعمل الأول؟

وفي سؤال فرعي مفيد: الهدف ده بيتقاس إزاي؟ يعني إحنا هنعرف إننا وصلنا منين؟ الهدف اللي مالوش مؤشر بيتقاس بيه بيفضل إحساس، والإحساس بيتغير مع المزاج.

## اسألي عن الأدوية والأكل مع بعض

لو في دوا موصوف، اسألي: هو بيشتغل إزاي؟ وبياخد وقت قد إيه عشان أثره يبان؟ وفي حاجة في الأكل ليها علاقة بتوقيته أو بامتصاصه؟ وفي أعراض جانبية ليها علاقة بالأكل أو بالمعدة؟

والسؤال العكسي مهم بنفس القدر: الخطة الغذائية دي ليها أي تأثير على الدوا؟

الدليل الدولي بيتعامل مع الأدوية كجزء من الإدارة حسب الحالة والهدف، والاختيار بينها قرار إكلينيكي. الحاجة اللي مش مقبولة إن حد يوقف علاج موصوف بناءً على منشور.

## اسألي عن الجدول الزمني ونقطة إعادة التقييم

سؤالين بيمنعوا شهور ضايعة:

إمتى المفروض أشوف تغيير؟ الإجابة بتختلف حسب الهدف — مؤشر في تحليل بيتحرك بمعدل غير الدورة غير الشعر.

وإمتى بنقعد نراجع لو مفيش تغيير؟ الخطة اللي مالهاش نقطة مراجعة بتستمر بالقصور الذاتي.

## اسألي عن اللي مالوش دليل

اسألي مباشرة عن أي حاجة سمعتيها: المكمل الفلاني، النظام الفلاني، منع الألبان، منع الجلوتين.

والإجابة المفيدة مش «ده كلام فاضي» ولا «جربي مش هيضر». الإجابة المفيدة هي: الأدلة على ده قد إيه، وهل الدليل الدولي بيقول فيه حاجة، وهل في ضرر محتمل، وهل هياخد وقت من حاجة أنفع.

في مقال عن طريقة الحكم على الادعاءات دي: [[article:pcos-and-food-judging-a-claim|تكيس المبايض والأكل — إزاي تحكمي على أي كلام]].

## واسألي عن الحاجة اللي محدش بيسألها: أنا أعمل إيه لو حصل كذا؟

الخطة اللي مفيهاش تعليمات لحالة الطوارئ ناقصة.

لو حصل دوخة أو إرهاق شديد أو أعراض جديدة، أنا أعمل إيه؟ وأتصل بمين؟ وفي حاجة معينة لازم أوقفها فورًا؟

ولو الخطة فيها تعديل كبير في الأكل، وأنا بأخد دوا بيأثر على السكر: في احتياطات؟ السؤال ده بالذات مهم لأي حد بياخد علاج ليه علاقة بسكر الدم، لأن تغيير الأكل من غير تعديل مقابل هو الحاجة اللي بتعمل مشاكل.

## خرافة: «السؤال معناه إني مش واثقة في الدكتور»

الخرافة دي بتخلي ناس كتير تسكت، وهي غلط من ناحيتين.

الناحية الأولى: السؤال مش تشكيك، هو استكمال. الكشف فيه معلومات كتير بتتقال بسرعة، والذاكرة بعد كشف فيه تشخيص جديد مش بتكون في أحسن حالاتها. السؤال بيثبّت المعلومة.

والناحية التانية إن الخطة اللي الشخص فاهمها بتتنفذ أحسن من الخطة اللي حافظها. ده مش كلام لطيف — ده الفرق بين حد بيعرف ليه بيعمل حاجة وحد بيعمل حاجة لحد ما يزهق.

الخرافة بتنتشر لأن العلاقة بين المريض والطبيب في ثقافتنا فيها تبجيل، والتبجيل حلو بس مش المفروض يمنع السؤال.

والخرافة التانية اللي جنبها: «الدكتور مش هيبقى معاه وقت». ده أحيانًا صح، وعشان كده الأسئلة تتكتب وتترتب بالأهم. تلات أسئلة مكتوبة بتاخد وقت أقل من عشر أسئلة بتيجي متقطعة، وبتطلع بمعلومات أكتر.

## في السياق المصري

الكشف عندنا غالبًا قصير، والعيادة مزحومة، والوقت مضغوط. ده واقع مش هيتغير بالتمني، والتعامل معاه هو إن الأسئلة تيجي مكتوبة ومرتبة بالأهم.

وفي حاجة تانية: تكيس المبايض بيتقال عنه كلام كتير في العيلة. «خالتك كان عندها ونزلت وزن وخلصت»، «دي بتروح بالجواز»، «دي بتروح بالحمل». الجمل دي بتوصل بحسن نية وبتعمل ضغط حقيقي. الحاجة اللي تستاهل تتعرف إن التكيس حالة بتتدار وبتختلف من واحدة للتانية، وإن تجربة قريبة مش خطة.

والصيدلية بتصرف مكملات كتير من غير روشتة، ومنها حاجات بتتباع تحديدًا لتكيس المبايض. الحاجات دي تتقال في الكشف حتى لو الشخص شايفها بسيطة.

CLINICAL_INPUT: إيه السؤال اللي نفسك المريضة تسأله في أول كشف عن الهرمونات ونادرًا بيتسأل؟

CLINICAL_INPUT: لما يكون الهدف الخصوبة، إيه اللي بيتغير في أولويات الخطة عندك؟

PRACTITIONER_VOICE: إيه أكتر جملة بتسمعيها من مريضة تكيس مبايض في أول كشف؟

وأخيرًا: اطلبي الخلاصة مكتوبة. سطرين على ورقة الروشتة — التشخيص، والهدف، والخطوة الجاية — بيوفروا نقاش شهر بعدين، وبيبقوا مرجع لأي حد تاني بيشوف الحالة.

## اللي يستاهل تفتكريه

التشخيص اتحدد إزاي، التحاليل اتعمل فيها إيه، إحنا بنستهدف إيه أول حاجة، الأدوية بتشتغل إزاي، إمتى نراجع، وإيه اللي مالوش دليل.

ستة أسئلة، وكلها مشروعة، وكلها بتغيّر الخطة.

اكتبيهم قبل الكشف. الأسئلة اللي في الدماغ بتتنسى، واللي على الورق بتتسأل.

لو عايزة كشف الأسئلة دي جزء منه: [[specialty:pcos-hormonal|تغذية تكيس المبايض والاضطرابات الهرمونية]] أو [[booking|احجزي موعد]].
AR,

                'en' => <<<'EN'
Most people leave a consultation with a piece of paper and a diagnosis, and the questions arrive afterwards — in the car, or at night, or when somebody asks "so what exactly did they say?".

This article is a set of questions worth asking in a consultation about hormones and food. Not in order to doubt anybody, but because these answers are what determine the shape of the plan, and most of them do not get said unprompted because time is short.

## Ask about the diagnosis itself: how was it made?

This is the first and most important question, and the one least often asked.

Polycystic ovary syndrome is diagnosed by a set of criteria rather than by any single finding: disrupted or absent ovulation, signs of raised androgens (clinical or on tests), and the appearance of the ovary on ultrasound. And diagnosis requires excluding other conditions with overlapping symptoms — thyroid problems and raised prolactin among them.

So an ultrasound alone is not a diagnosis. That is a very practical piece of information, because "your ovaries look polycystic on the scan" is a sentence said often, and it turns into a lifelong label.

The international evidence-based guideline for the assessment and management of PCOS, in its 2023 update, takes the point further: it recommends against using ultrasound for diagnosis in the first years after menarche, because ovarian appearance at that age is naturally variable and overlaps with the appearance treated as a sign. And it treats anti-Müllerian hormone as a possible alternative to ultrasound in adults.

So the question to ask is: what exactly was the diagnosis built on? And were the other conditions excluded?

## Ask about the tests: what was done, and why?

"Your tests are fine" is a sentence whose meaning depends entirely on what was tested.

Ask for the list of tests themselves, not the summary. And ask, of each: what does it measure, what was the number, and what was the range?

Ask whether anything is sitting near an edge. This is not fussing — it is a legitimate clinical question, and a result at an edge is interpreted alongside symptoms rather than alone.

And if the complaint includes persistent exhaustion, there is a separate article about what "normal" means on a lab report: [[article:normal-results-still-tired|my results are normal and I am still exhausted]].

## Ask about the mechanism: what is actually happening in my body?

This looks theoretical and is intensely practical: somebody who understands why she is doing something carries on with it longer than somebody who has memorised that she must.

The central idea in a large proportion of PCOS is insulin resistance. Insulin is a hormone secreted by the pancreas after eating, and its job is to make cells take glucose out of the blood. In insulin resistance the cells respond less efficiently, so the pancreas secretes more to reach the same result, and circulating insulin stays higher than usual.

And the part that connects this to hormones: insulin does not act only on glucose. Raised levels affect the ovary and the production of androgens, and excess androgens are associated with symptoms such as unwanted hair growth, acne and disrupted ovulation.

So ask: is there insulin resistance in my case? How was that established? And how does the change you are proposing act on it?

## Ask what else gets checked alongside

PCOS is not only a matter of periods and hair. The international guideline treats it as a condition with metabolic and psychological dimensions as well, recommending attention to markers such as blood glucose, lipids and blood pressure, and asking about mood and anxiety.

That psychological part is always left out, and it is not a detail: a chronic condition affecting appearance and fertility, diagnosed young, has a real effect, and the guideline says so explicitly.

So ask: is there anything else that should be followed over time? And how often?

## And ask about the periods themselves

If periods are heavy or irregular, that is information with a direct bearing on food from a direction that is not obvious: repeated blood loss depletes the iron store, and the iron store falls before haemoglobin is affected.

So ask: do my periods have any bearing on my iron stores? And has that been assessed?

## Ask about the goal: what exactly are we treating?

This changes the plan more than anything else, and is usually left out.

Somebody who has come about her periods is not somebody who has come about fertility is not somebody who has come about unwanted hair growth is not somebody who has come about glucose markers. Those goals are not in conflict, but they are not the same plan, not the same priority and not the same timescale.

So ask: what is this plan aiming at first? And if I have more than one goal, which is addressed first?

And a useful sub-question: how is that goal measured? How will we know we have got there? A goal with no marker attached remains a feeling, and feelings move with mood.

## Ask about medication and food together

If something has been prescribed, ask: how does it work? How long before its effect shows? Is there anything in food that affects its timing or its absorption? Are there side effects related to food or to the stomach?

And the reverse question matters as much: does this eating plan have any effect on the medication?

The international guideline treats medications as part of management depending on the case and the goal, and choosing between them is a clinical decision. What is not acceptable is somebody stopping a prescribed treatment on the strength of a post.

## Ask about the timescale and the point of reassessment

Two questions that prevent wasted months:

When should I expect to see a change? The answer differs by goal — a marker on a test moves on a different schedule from periods, which move on a different schedule from hair.

And when do we sit down and review if nothing has changed? A plan with no review point continues by inertia.

## Ask about the things with no evidence behind them

Ask directly about anything you have heard: that supplement, that diet, cutting out dairy, cutting out gluten.

And the useful answer is neither "that is nonsense" nor "try it, it will not hurt". The useful answer is: how strong is the evidence, does the international guideline say anything about it, is there potential harm, and will it take time away from something that would work better?

There is an article on how to judge those claims: [[article:pcos-and-food-judging-a-claim|PCOS and food — how to judge a claim]].

## And ask the question nobody asks: what do I do if something happens?

A plan with no instructions for the bad day is incomplete.

If there is dizziness, severe exhaustion, or a new symptom, what do I do? Who do I contact? Is there anything I should stop immediately?

And if the plan involves a large change in eating while I am taking something that affects glucose: are there precautions? That question matters particularly for anybody on treatment related to blood sugar, because changing food without a corresponding adjustment is the thing that causes problems.

## The myth: "asking means I do not trust my doctor"

This keeps a great many people silent, and it is wrong in two ways.

First: a question is not doubt, it is completion. A consultation carries a lot of information said quickly, and memory after an appointment containing a new diagnosis is not at its best. A question fixes the information in place.

Second: a plan somebody understands gets followed better than a plan somebody has memorised. That is not a pleasantry — it is the difference between somebody who knows why she is doing something and somebody doing it until she is tired of it.

The myth spreads because the relationship between patient and doctor in our culture carries deference, and deference is fine but should not prevent a question.

And the myth beside it: "the doctor will not have time". That is sometimes true, which is exactly why the questions should be written down and ordered. Three written questions take less time than ten arriving piecemeal, and come away with more.

## In the Egyptian context

Consultations here are usually short, clinics are crowded, and time is compressed. That is a reality that will not change by wishing, and the way to deal with it is to arrive with the questions written down and ordered by importance.

And another thing: a great deal gets said about PCOS within the family. "Your aunt had it and lost weight and it went", "it goes when you marry", "it goes with pregnancy". Those sentences arrive in good faith and create real pressure. What is worth knowing is that PCOS is a condition that is managed and differs between women, and that a relative's experience is not a plan.

Pharmacies dispense a great many supplements without prescription, including things sold specifically for PCOS. Those should be mentioned in the consultation even if they seem trivial.

CLINICAL_INPUT: What is the question you most wish a patient would ask in a first hormonal consultation, and rarely does?

CLINICAL_INPUT: When the goal is fertility, what changes in your ordering of priorities?

PRACTITIONER_VOICE: What is the sentence you hear most often from a PCOS patient at a first consultation?

And finally: ask for the summary in writing. Two lines on the prescription pad — the diagnosis, the goal, the next step — save a month of argument later, and become a reference for anybody else who sees the case.

## Worth remembering

How the diagnosis was made, what was tested, what we are aiming at first, how the medication works, when we review, and what has no evidence behind it.

Six questions, all of them legitimate, all of them changing the plan.

Write them down before the appointment. Questions kept in your head are forgotten; questions on paper get asked.

If you want a consultation these questions are part of: [[specialty:pcos-hormonal|PCOS and hormonal nutrition]] or [[booking|book an appointment]].
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
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Carries two specific claims: (1) the recommendation AGAINST using ultrasound '
                        .'for diagnosis in the years shortly after menarche, and (2) AMH as a possible '
                        .'alternative to ultrasound in adults. Both are genuinely useful and both are '
                        .'specific enough to be got wrong — in particular, the article deliberately does not '
                        .'state the number of years after menarche, and if the guideline gives one it should '
                        .'be added at verification. Marked medium rather than high for that reason, despite '
                        .'the guideline itself being certain.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الجمعية الأوروبية للتكاثر البشري وعلم الأجنة (ESHRE) والجهات المشاركة',
                        'en' => 'European Society of Human Reproduction and Embryology (ESHRE) and partner bodies',
                    ],
                    'title' => [
                        'ar' => 'الدليل الدولي لتكيس المبايض — معايير التشخيص واستبعاد الحالات المشابهة',
                        'en' => 'International PCOS guideline — diagnostic criteria and exclusion of mimicking conditions',
                    ],
                    'year' => 2023,
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports the description of diagnosis as a combination of criteria rather '
                        .'than a single finding, and the requirement to exclude other conditions with '
                        .'overlapping presentations including thyroid disease and raised prolactin. Listed '
                        .'separately from the entry above because it is a different claim at a different '
                        .'confidence — this one is core, long-standing guidance; the ultrasound timing '
                        .'recommendation is newer and more specific.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الجمعية الأوروبية للتكاثر البشري وعلم الأجنة (ESHRE) والجهات المشاركة',
                        'en' => 'European Society of Human Reproduction and Embryology (ESHRE) and partner bodies',
                    ],
                    'title' => [
                        'ar' => 'الدليل الدولي لتكيس المبايض — الأبعاد الأيضية والنفسية',
                        'en' => 'International PCOS guideline — metabolic and psychological dimensions',
                    ],
                    'year' => 2023,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "recommends attention to markers such as blood glucose, lipids and '
                        .'blood pressure, and asking about mood and anxiety". Confident the guideline covers '
                        .'both cardiometabolic risk and psychological screening; confirm which specific '
                        .'markers it names and whether screening for depression and anxiety is a formal '
                        .'recommendation rather than a discussion point. No screening interval is stated in '
                        .'the article — add one only if the guideline gives it.',
                ],
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => ['ar' => 'صحيفة وقائع: فقر الدم', 'en' => 'Fact sheet: Anaemia'],
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports the paragraph linking heavy or irregular periods to iron stores. '
                        .'Cited for the general relationship only; no threshold is quoted. Record the '
                        .'revision date current at verification.',
                ],
            ],
        ];
    }
}
