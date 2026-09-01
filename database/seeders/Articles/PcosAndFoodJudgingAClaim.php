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

## الأول: ليه تكيس المبايض بالذات بيجذب الكلام ده

مش صدفة. في خصائص في الحالة نفسها بتخليها أرض خصبة:

هي منتشرة، يعني الجمهور كبير. وهي مزمنة، يعني الشخص بيدور لفترة طويلة. ومفيش علاج نهائي بيخلّصها، يعني في مساحة دايمًا فاضية لوعد جديد. وأعراضها بتمس حاجات حساسة — الوزن، الشعر، البشرة، الدورة، والخصوبة. وبتتشخص غالبًا في ستات صغيرة في السن وموجودين على السوشيال ميديا.

كل خاصية من دول بتزوّد الطلب على إجابة بسيطة. واللي بيبيع إجابة بسيطة عنده جمهور جاهز.

ده مش معناه إن كل اللي بيتقال كدب. معناه إن الحاجة دي بالذات محتاجة تُقرا بحرص أعلى من المتوسط.

## اللي الدليل الدولي بيقوله فعلًا

في دليل دولي مبني على الأدلة لتقييم وإدارة متلازمة تكيس المبايض، طلع في نسخته المحدّثة سنة ٢٠٢٣، وشاركت في تطويره واعتماده جهات منها الجمعية الأوروبية للتكاثر البشري وعلم الأجنة (ESHRE).

الدليل ده بيقول حاجات محددة تستاهل تتقري بالظبط:

إن تعديل نمط الحياة — الأكل والحركة والجانب السلوكي — موصى بيه كجزء أساسي من الإدارة.

وإن مفيش نمط غذائي واحد أثبت تفوقه على غيره في تكيس المبايض. يعني الدليل نفسه، اللي مهمته كلها إنه يقول إيه اللي ينفع، بيقول إن مفيش نظام معيّن هو الإجابة.

وإنه بيتعامل بحذر مع المكملات والعلاجات اللي الأدلة عليها محدودة، بدل ما يوصي بيها.

خلي بالك مين اللي بيقول ده. دي مش عيادة، ولا حد بيبيع كورس. ده دليل دولي بيشتغل عليه أطباء نساء وغدد في العالم كله. ولو كان في نظام أكل واحد بيعالج تكيس المبايض، دي بالظبط الجهة اللي كانت هتقوله ــ وبصوت عالي.

## الفسيولوجي: إيه علاقة الإنسولين بالموضوع أصلًا

عشان تقدري تحكمي على أي ادعاء، لازم تعرفي الميكانيكية اللي بيتقال إنها بتشتغل.

الإنسولين هرمون بيتفرز من البنكرياس بعد الأكل، وشغلته إنه يخلي الخلايا تسحب السكر من الدم وتستخدمه. في مقاومة الإنسولين الخلايا بتستجيب للهرمون ده بكفاءة أقل، فالبنكرياس بيفرز كمية أكبر عشان يوصل لنفس النتيجة. النتيجة إن مستوى الإنسولين في الدم بيبقى أعلى من المعتاد.

الجزء المهم: الإنسولين مش بيشتغل على السكر بس. مستوياته العالية بتأثر على المبيض وعلى إنتاج الأندروجينات — الهرمونات اللي زيادتها مرتبطة بأعراض زي الشعر الزايد وحب الشباب واضطراب التبويض.

وده اللي بيفسّر ليه الكلام عن الأكل في تكيس المبايض مش كلام فاضي: الأكل بيأثر على استجابة الإنسولين، والإنسولين ليه علاقة بالصورة الهرمونية.

بس — ودي النقطة اللي بتضيع دايمًا — وجود ميكانيكية معقولة مش نفس إثبات نتيجة. ودي أهم أداة في المقال ده كله.

## الفرق بين ميكانيكية ونتيجة

معظم الادعاءات اللي بتوصلك بتقف عند نص الطريق.

الشكل بيكون كده: «مادة كذا بتأثر على حساسية الإنسولين» — وده ممكن يكون صح ومقاس في المعمل — «يبقى هي بتعالج تكيس المبايض» — وده قفزة محدش قاسها.

بين الاتنين في مسافة طويلة: هل الأثر ده بيحصل في الجسم البشري بالجرعة اللي في الأكل؟ وهل بيستمر؟ وهل بيترجم لتغيير في الأعراض اللي الشخص جاي عشانها — الدورة، التبويض، الشعر — ولا بيفضل رقم في تحليل؟ وهل اتقاس في دراسات كفاية عشان نبقى واثقين؟

القرفة مثال كويس. في دراسات صغيرة بصت على أثرها على سكر الدم. ده مش نفس القول إنها بتعالج تكيس المبايض، والدليل الدولي مش بيوصي بيها كعلاج. الفرق مش تفصيلة لغوية — الفرق إن الشخص ممكن يعتمد عليها بدل حاجة ليها أدلة.

والإينوزيتول مثال أدق، وأمين إننا نقوله بوضوح: ده مركب اتدرس فعلًا في تكيس المبايض، والدليل الدولي بيتعامل معاه وبيناقشه، والأدلة عليه لسه محدودة ومش وصلت لمستوى توصية قاطعة. يعني هو مش خرافة، وهو كمان مش حقيقة مستقرة. والفرق بين التوصيف ده وبين «الإينوزيتول بيعالج التكيس» هو الفرق بين الأمانة والتسويق.

## وحاجة تانية الدليل بيقولها وبتتنسى: الحركة

الكلام كله بيتركز على الأكل، والدليل الدولي بيتكلم عن الأكل والحركة والجانب السلوكي مع بعض كحزمة واحدة، مش عن الأكل لوحده.

ده مهم عمليًا لأن الحركة بتأثر على حساسية الإنسولين بطريقة مباشرة: العضلة اللي بتشتغل بتسحب سكر من الدم بآلية مش محتاجة إنسولين بنفس الدرجة، والنشاط المنتظم بيحسّن استجابة الأنسجة للهرمون ده مع الوقت.

يعني اللي بيدور على الأكل بس بيسيب نص التوصية. ومش الكلام عن جيم ولا برنامج — الكلام عن نشاط منتظم بشكل ما.

CLINICAL_INPUT: لما تتكلمي عن الحركة مع مريضة تكيس مبايض، إنتِ بتقترحي إيه فعلًا؟

## والوزن — نقطة تستاهل تتقال بحرص

في علاقة معروفة بين الوزن ومقاومة الإنسولين، والدليل الدولي بيتعامل مع تعديل نمط الحياة كجزء من الإدارة.

بس في حاجتين لازم يتقالوا مع بعض: تكيس المبايض بيحصل في ستات بأوزان مختلفة، ومش كل حالة مرتبطة بالوزن. والافتراض إن الحل هو النزول بس بيسيب ستات كتير من غير إجابة، وبيحمّل الموضوع كله على حاجة واحدة.

الكلام عن الأكل في تكيس المبايض مش بالضرورة كلام عن إنقاص وزن. أحيانًا بيبقى عن شكل الوجبة وتوزيعها واستجابة السكر، وده موضوع تاني خالص.

## ست أسئلة بتفرز أي ادعاء في دقيقة

مين قال؟ جهة مرجعية ولا حساب بيبيع؟ ولو بيبيع، ده مش دليل على الكدب، بس هو معلومة عن الحافز.

الادعاء إيه بالظبط؟ «بيحسّن» غير «بيعالج» غير «بيمنع». الكلمة المبهمة غالبًا مقصودة.

ده أثر مقاس على البشر ولا فكرة عن ميكانيكية؟

اتقاس على كام واحد ولمدة قد إيه؟ حاجة اتجربت على عشرين واحدة لشهر مش نفس حاجة اتجربت في مراجعة منهجية.

الدليل الدولي بيقول إيه في نفس النقطة؟ لو الادعاء حقيقي وقوي، هيكون في الدليل.

وإيه اللي ممكن يثبت إنه غلط؟ الادعاء اللي مفيش حاجة ممكن تنفيه مش ادعاء علمي أصلًا.

وفي نوع تالت من الادعاءات أخطر من الاتنين: اللي بيقول «بلاش أدوية، ده طبيعي». الدليل الدولي بيتكلم عن أدوية ليها مكان في الإدارة حسب الحالة والهدف، والاختيار بينها قرار إكلينيكي. الادعاء اللي بيطلب من حد يوقف علاج موصوف مش نصيحة غذائية — ده تدخل في علاج، ومن حد مش شايف الحالة ومش مسؤول عن نتيجتها.

## في المطبخ المصري

الكلام عن تكيس المبايض بيوصل هنا مترجم غالبًا، والأكل المذكور فيه مش أكلنا.

الجزء العملي إن الأكل المصري فيه أدوات كتير للي الدليل بيوصي بيه فعلًا: البقوليات — فول وعدس وحمص ولوبيا — والخضار الموسمي والسلطة والبيض والجبنة القريش والزبادي والسمك البلدي. دي مش «بدائل» لأكل أجنبي؛ دي أكل بجد.

وفي حاجات بتتقال هنا بالذات وتستاهل توضيح: منع العيش البلدي والرز نهائيًا مش توصية موجودة في الدليل الدولي، ومنع الألبان من غير سبب إكلينيكي مش توصية موجودة، والحمية الخالية من الجلوتين مالهاش علاقة بتكيس المبايض إلا لو في حساسية قمح مشخصة.

والخطة اللي بتتنفذ في بيت مصري هي اللي بتشتغل جوه الحلة اللي بتتطبخ للعيلة كلها، مش اللي بتطلب مطبخ منفصل.

CLINICAL_INPUT: أكتر ادعاء بيوصلك من مريضات التكيس وإنتِ بتردي عليه إزاي؟

CLINICAL_INPUT: في حاجة في الأكل بتبدأي بيها فعلًا مع حالة تكيس مبايض جديدة؟ المبدأ، من غير خطة.

## والحاجة اللي مش بتتقال كفاية

تكيس المبايض حالة بتتدار، مش بتتشال. والإدارة الجادة فيها متابعة على مدى شهور، ومؤشرات بتتقاس، وتعديل لما حاجة متنفعش.

الوعد بالحل السريع مش بس مش صحيح — هو بيسرق الوقت من حاجة كانت هتشتغل. الشخص اللي قضى ستة شهور على مكمل بيرجع للنقطة صفر ومعاه إحباط إضافي.

PRACTITIONER_VOICE: إيه أكتر حاجة نفسك المرضى يعرفوها عن تكيس المبايض من أول يوم؟

## اللي يستاهل تفتكريه

تكيس المبايض بيجذب الادعاءات لأسباب في طبيعة الحالة نفسها، مش لأن اللي عندها بيصدقوا بسهولة.

الدليل الدولي بيوصي بتعديل نمط الحياة، وبيقول بوضوح إن مفيش نظام غذائي واحد أثبت تفوقه. والإنسولين ليه علاقة حقيقية بالصورة، بس وجود ميكانيكية مش إثبات نتيجة.

وست أسئلة — مين قال، الادعاء إيه، أثر على بشر ولا فكرة، اتقاس إزاي، الدليل بيقول إيه، وإيه اللي ينفيه — بتفرز معظم اللي بيوصلك.

لو عندك تشخيص وعايزة خطة مبنية على حالتك إنتِ مش على منشور: [[specialty:pcos-hormonal|تغذية تكيس المبايض والاضطرابات الهرمونية]]، أو [[booking|احجزي موعد]].

وفي مقال عن الأسئلة اللي تسأليها في الكشف نفسه: [[article:questions-to-ask-about-hormones-and-food|أسئلة تسأليها عن الهرمونات والأكل]].
AR,

                'en' => <<<'EN'
If you have polycystic ovary syndrome, a great deal has been said to you about food. Cut out dairy. Cut out gluten. Cinnamon on an empty stomach. Keto cures it. Apple cider vinegar. A particular herb. A particular vitamin.

Some of it contains something real, some of it has no basis at all, and the difficulty is that both arrive with the same confidence and in the same shape.

This article is not a rebuttal of each claim — such a list would be out of date within a year. It is about a method for judging whatever reaches you, today and in a year's time.

## First: why PCOS in particular attracts this

It is not an accident. There are features of the condition itself that make it fertile ground:

It is common, so the audience is large. It is chronic, so people search for a long time. There is no definitive cure, so there is always an empty space for a new promise. Its symptoms touch sensitive things — weight, hair, skin, periods, fertility. And it is usually diagnosed in young women who are online.

Every one of those features increases the demand for a simple answer. Anybody selling a simple answer has a ready audience.

None of which means everything said is false. It means this subject in particular needs to be read with above-average care.

## What the international guideline actually says

There is an international evidence-based guideline for the assessment and management of polycystic ovary syndrome, whose updated edition appeared in 2023, developed and endorsed by bodies including the European Society of Human Reproduction and Embryology (ESHRE).

It says specific things worth reading exactly:

That lifestyle modification — diet, activity and the behavioural component — is recommended as a core part of management.

That no single dietary pattern has been shown to be superior to another in PCOS. Which is to say: the guideline itself, whose entire purpose is to state what works, says that no particular diet is the answer.

And that it treats supplements and therapies with limited evidence cautiously rather than recommending them.

Notice who is saying this. Not a clinic, and nobody selling a course. It is an international guideline that gynaecologists and endocrinologists worldwide work from. If there were one eating pattern that treated PCOS, this is precisely the body that would have said so — loudly.

## The physiology: what insulin has to do with any of this

To judge a claim, you need to know the mechanism it is claiming to use.

Insulin is a hormone secreted by the pancreas after eating, and its job is to make cells take glucose out of the blood and use it. In insulin resistance the cells respond to that hormone less efficiently, so the pancreas secretes more of it to reach the same result. The consequence is that circulating insulin runs higher than usual.

The important part: insulin does not act only on glucose. Raised levels affect the ovary and the production of androgens — the hormones whose excess is associated with symptoms such as unwanted hair growth, acne and disrupted ovulation.

Which is why talk about food in PCOS is not empty: food affects the insulin response, and insulin has a bearing on the hormonal picture.

But — and this is the point that always gets lost — the existence of a plausible mechanism is not the same as a demonstrated outcome. That is the single most useful tool in this article.

## The difference between a mechanism and an outcome

Most claims that reach you stop halfway.

The shape is this: "substance X affects insulin sensitivity" — which may be true and measured in a laboratory — "therefore it treats PCOS" — which is a leap nobody measured.

Between the two lies a long distance: does that effect occur in a human body at the dose present in food? Does it persist? Does it translate into a change in the symptoms somebody actually came about — periods, ovulation, hair — or does it stay a number in a test? And has it been measured in enough studies to be confident?

Cinnamon is a good example. Small studies have looked at its effect on blood glucose. That is not the same as saying it treats PCOS, and the international guideline does not recommend it as a treatment. The distinction is not a matter of wording — it is that somebody may rely on it instead of something with evidence behind it.

Inositol is a subtler example, and it is only honest to say so plainly: it is a compound that genuinely has been studied in PCOS, the international guideline addresses and discusses it, and the evidence remains limited and has not reached the level of a firm recommendation. So it is not a myth, and it is also not settled fact. The difference between that description and "inositol treats PCOS" is the difference between honesty and marketing.

## And something else the guideline says that gets forgotten: activity

The conversation concentrates entirely on food, while the international guideline talks about diet, activity and the behavioural component together as one package, not about food alone.

That matters practically, because activity affects insulin sensitivity directly: a working muscle draws glucose from the blood by a mechanism that does not depend on insulin to the same degree, and regular activity improves how tissues respond to the hormone over time.

So anybody looking only at food is leaving half the recommendation behind. And this is not about a gym or a programme — it is about regular activity of some kind.

CLINICAL_INPUT: When you talk about activity with a PCOS patient, what do you actually suggest?

## And weight — a point to be made carefully

There is a recognised relationship between weight and insulin resistance, and the international guideline treats lifestyle modification as part of management.

But two things have to be said together: PCOS occurs in women across a range of weights, and not every case is weight-related. Assuming the answer is simply weight loss leaves a great many women without an answer, and loads the whole condition onto one variable.

Talking about food in PCOS is not necessarily talking about losing weight. Sometimes it is about the shape of a meal, how it is distributed, and the glucose response — which is a different subject entirely.

## Six questions that sort any claim in a minute

Who said it? A reference body or an account selling something? And if it is selling, that is not proof of falsehood — it is information about the incentive.

What exactly is the claim? "Improves" is not "treats" is not "prevents". The vague word is usually vague on purpose.

Is this an effect measured in humans, or an idea about a mechanism?

In how many people, and for how long? Something tried in twenty women for a month is not something examined in a systematic review.

What does the international guideline say on the same point? If the claim is real and strong, it will be in there.

And what would show it to be wrong? A claim that nothing could disprove is not a scientific claim at all.

There is a third kind of claim, more dangerous than the other two: the one that says "no medication, this is natural". The international guideline discusses medications that have a place in management depending on the case and the goal, and choosing between them is a clinical decision. A claim that asks somebody to stop a prescribed treatment is not dietary advice — it is an intervention in treatment, from somebody who has not seen the case and is not answerable for the outcome.

## In an Egyptian kitchen

Talk about PCOS reaches us mostly in translation, and the food named in it is not our food.

The practical part is that Egyptian food contains plenty of tools for what the guideline actually recommends: legumes — foul, lentils, chickpeas, black-eyed beans — seasonal vegetables, salad, eggs, areesh cheese, yoghurt and local fish. These are not "substitutes" for foreign food; they are food.

And some things said here specifically deserve clarifying: cutting out baladi bread and rice entirely is not a recommendation in the international guideline; excluding dairy without a clinical reason is not a recommendation; and a gluten-free diet has no bearing on PCOS unless coeliac disease has been diagnosed.

The plan that gets followed in an Egyptian household is the one that works inside the pot already being cooked for everybody, not the one that requires a separate kitchen.

CLINICAL_INPUT: What is the claim you hear most often from PCOS patients, and how do you answer it?

CLINICAL_INPUT: Is there something in eating you actually start with in a new case of PCOS? The principle, not a plan.

## And the thing that does not get said enough

PCOS is a condition that is managed, not removed. Serious management involves follow-up over months, markers that get measured, and adjustment when something does not work.

The promise of a quick resolution is not merely untrue — it takes time away from something that would have worked. Somebody who spends six months on a supplement returns to the starting point carrying additional discouragement.

PRACTITIONER_VOICE: What do you most wish patients knew about PCOS from the first day?

## Worth remembering

PCOS attracts claims because of features of the condition itself, not because the people who have it are credulous.

The international guideline recommends lifestyle modification and states clearly that no single dietary pattern has been shown superior. Insulin genuinely does have a bearing on the picture, but a mechanism is not a demonstrated outcome.

And six questions — who said it, what exactly is claimed, humans or an idea, how it was measured, what the guideline says, and what would disprove it — sort most of what reaches you.

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
