<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * The flagship. Built on one sentence heard in the clinic: «زهقت».
 *
 * Behaviour rather than biochemistry, so the citations are behaviour-change
 * and obesity-management guidance rather than nutrient data — and the
 * physiology section explains the early water shift, which is the single most
 * misread thing on a scale in week one.
 */
class WhyWeQuitInWeekThree extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'why-we-quit-in-week-three',
            'category' => 'weight-management',
            'tags' => ['adherence'],
            'featured' => true,
            'cover' => 'pantry-staples-overhead',

            'title' => [
                'ar' => 'ليه بنسيب النظام في الأسبوع التالت؟',
                'en' => 'Why we quit in week three',
            ],

            'excerpt' => [
                'ar' => '«زهقت» مش كسل، ودي مش كلمة نهاية. الأسبوع التالت له شكل معروف — فسيولوجي ونفسي — وفهمه بيغيّر رد الفعل.',
                'en' => '"I got bored" is not laziness, and it is not the end of the sentence. Week three has a shape — physiological and psychological — and knowing it changes what you do next.',
            ],

            'body' => [
                'ar' => <<<'AR'
أكتر جملة بتتقال في العيادة مش «مش قادرة»، ولا «الأكل وحش». الجملة هي: «زهقت».

وبتتقال غالبًا في نفس التوقيت. مش الأسبوع الأول، ولا الشهر التالت. الأسبوع التالت.

المقال ده مش عن إزاي تتحمسي تاني. هو عن إن الأسبوع التالت له شكل معروف ومتكرر، وله أسباب مفهومة — نصها في الجسم ونصها في الدماغ — وإن معرفة الشكل ده قبل ما يوصل بتغيّر رد فعلك لما يوصل.

## الأسبوعين الأولانيين بيشتغلوا بحاجة تانية خالص

في البداية بتشتغلي بحاجة اسمها الجدة. كل حاجة جديدة: الأكل جديد، والروتين جديد، والفكرة نفسها إن في خطة أصلًا جديدة. الجدة دي بتدي طاقة حقيقية — بس هي طاقة ليها تاريخ صلاحية، ومش مبنية على أي حاجة في الخطة نفسها.

وفي حاجة تانية بتشتغل في الأول وبتخدع أكتر: النتيجة السريعة.

## ليه الميزان بيتحرك بسرعة في أول أسبوعين — والسبب مش شحم

دي أكتر نقطة بتتقري غلط، والغلط ده بالذات هو اللي بيحضّر للأسبوع التالت.

الجسم بيخزن الكربوهيدرات في الكبد والعضلات على شكل جليكوجين. والجليكوجين مش بيتخزن جاف — بيتخزن ومعاه مية، بكمية أكبر من وزنه هو نفسه. أول ما كمية الأكل تقل، وبالذات لو النشويات قلّت، الجسم بيبدأ يسحب من مخزون الجليكوجين ده، والمية اللي كانت مربوطة بيه بتخرج معاه.

وفي حاجة تانية بتحصل في نفس الوقت: تغيير الأكل بيغيّر كمية الصوديوم اللي داخلة، والصوديوم بيأثر على المية اللي الجسم بيمسكها. الأكل المطبوخ في البيت بيبقى فيه ملح أقل من الأكل الجاهز وأكل بره، فتحوّل بسيط في نوع الأكل بيغيّر رقم الميزان من غير ما يغيّر حاجة في تركيب الجسم.

وفي عامل تالت أبسط وبيتنسى: كمية الأكل اللي في الجهاز الهضمي نفسه في أي لحظة. الشخص اللي بقى بياكل كميات أقل، أو بيوزّعها بشكل مختلف على اليوم، بيبقى حامل جواه أقل — وده بيظهر على الميزان زي أي حاجة تانية.

يعني الرقم اللي نزل في أول عشر أيام كان بيقيس حاجة حقيقية — بس مش الحاجة اللي إنتِ فاكرة إنه بيقيسها. ولما المخزون ده يتظبط ويستقر، النزول بيهدى فجأة، والعين بتقرا الهدوء ده على إنه توقف.

مفيش حاجة اتكسرت. اللي حصل إن الجزء السريع خلص، وفضل الجزء البطيء — وهو الجزء الحقيقي.

لو الرقم على الميزان بقى هو اللي بيحدد مزاجك الصبح، في مقال منفصل عن ده: [[article:what-the-scale-does-not-say|الميزان بيقول إيه — والحاجات اللي مش بيقولها]].

## الأسبوع التالت هو أول أسبوع «عادي»

في الأسبوع التالت الجدة بتخلص، والنزول السريع بيهدى، والخطة بتبقى مجرد… حاجة بتعمليها. من غير حماس، ومن غير مكافأة واضحة.

ودي مش لحظة فشل. دي أول لحظة بتشوفي فيها الخطة على حقيقتها: حاجة هتعمليها لفترة طويلة. اللي بيحصل إن دماغك بتقارن الإحساس ده باللي كان في الأسبوع الأول، والمقارنة دي ظالمة — لأنها بتقارن حالة مستمرة بحالة مؤقتة، ونتيجتها معروفة من قبل ما تبدأ.

«زهقت» في اللحظة دي معناها بالظبط: الحاجة اللي كانت مثيرة بقت عادية. وده كان هيحصل مهما كانت الخطة، ومهما كان اللي كاتبها.

## اللي التوصيات المنشورة بتقوله عن اللحظة دي

المعهد الوطني البريطاني للصحة وجودة الرعاية (NICE) في دليله عن التعامل مع السمنة بيوصي إن برامج إدارة الوزن تبقى ممتدة ومعاها متابعة، مش تدخّل بيحصل مرة واحدة وخلاص. التوصية دي مش تفصيلة إدارية — هي اعتراف بإن الجزء الصعب مش البداية.

ونفس الجهة، في دليل منفصل عن تغيير السلوك، بتوصف اللي بيخلي التغيير يستمر: تخطيط مسبق، أهداف محددة، متابعة ذاتية، ودعم ممتد على مدى زمني. ولاحظي إن «الإرادة» مش في القايمة دي.

والاتحاد الأوروبي لدراسة السمنة (EASO) في إرشاداته لإدارة السمنة عند البالغين بيشدد على نفس النقطة من ناحية تانية: إن الهدف الواقعي والمتابعة طويلة المدى بيفرقوا أكتر من شدة التدخل في أوله.

ومنظمة الصحة العالمية بتصنّف السمنة كحالة مزمنة. وده مش وصف قاسي — ده وصف بيحدد شكل التعامل الصحيح: الحالة المزمنة بتتدار، ومش بتتحل في ثلاثين يوم.

الخلاصة العملية من التوصيات دي إن استمرار الخطة مش نتيجة لقوة البداية. المتابعة والتعديل جزء من العلاج نفسه، مش اعتراف بإن حاجة اتكسرت.

## خرافة: «لو كنتي عايزة بجد، مكنتيش هتزهقي»

دي الجملة اللي بتتقال في البيت وفي الشغل وأحيانًا لنفسك في المراية. وهي غلط بطريقة معينة تستاهل التوضيح.

الخرافة دي بتفترض إن الالتزام صفة في الشخص: إما عندك إرادة أو مش عندك. والفكرة دي منتشرة لأنها بسيطة، ولأنها بتفسّر كل حاجة من غير ما تحتاج تعرف أي تفاصيل عن حياة الشخص.

اللي التوصيات المبنية على الأدلة بتشتغل عليه مختلف تمامًا: بتشتغل على الظروف اللي بتخلي السلوك أسهل أو أصعب — إيه الموجود في المطبخ، الأكل بيتحضر إمتى، مين بيطبخ، والخطة أصلًا اتبنت على أكل البيت ولا على أكل مالوش وجود في السوق. دي حاجات بتتغير. «الإرادة» مش حاجة بتتغير، وعشان كده هي تفسير مريح ومش مفيد.

الزهق مش عيب في الشخصية. الزهق هو الاستجابة الطبيعية والمتوقعة لتكرار أي حاجة. المشكلة مش إنه حصل — المشكلة في اللي بيتقري منه.

## الأسبوع التالت في مطبخ مصري

الأسبوع التالت بيوصل هنا بشكل خاص، لأسباب ليها علاقة بشكل الأكل نفسه.

الخطة اللي مفيهاش عيش بلدي، ولا فول، ولا رز، ولا طعمية، هي خطة بتطلب من الشخص يعيش جنب أكل بيته من غير ما ياكل منه. ده ينفع أسبوع، وأحيانًا اتنين. في التالت بيبقى في وجبة عيلة، أو عزومة، أو يوم شغل طويل، والخطة اللي مالهاش علاقة بالمطبخ ده بتقع في أول اختبار حقيقي ليها.

الأكل المصري نفسه مش هو المشكلة. الفول والعدس والحمص بقوليات، والجبنة القريش والزبادي مصادر بروتين متاحة ورخيصة، والخضار الموسمي في السوق أرخص وأحسن من أي حاجة مستوردة. المسقعة والملوخية والخضار المطبوخ في البيت أكل حقيقي. الفرق مش بين «أكل مصري» و«أكل صحي» — الفرق في الكميات، وفي طريقة التحضير، وفي إيه اللي بيتاكل مع إيه.

وفي نقطة الميزانية، وهي مش تفصيلة. أي خطة بتفترض سلمون وأفوكادو ومنتجات مستوردة هي خطة ليها تاريخ انتهاء معروف مسبقًا، مش لأن الشخص مش ملتزم، لكن لأنها بتطلب مصروف مش موجود. البقوليات والبيض والجبنة القريش والزبادي والخضار الموسمي والسمك البلدي مصادر حقيقية ومتاحة، والخطة المبنية عليها بتعيش أطول لأنها بتشتري من نفس السوق اللي البيت بيشتري منه.

وفي حاجات مصرية بحتة بتحصل في نفس التوقيت ده كل مرة:

عزومة الجمعة، اللي بتيجي بعد أسبوعين من الالتزام وبتتقري على إنها «كسر النظام» — مع إنها يوم واحد في خطة المفروض إنها شهور.

الأكل بره، اللي بيبقى غالبًا كشري أو ساندوتشات، ومش لازم يكون خروج عن الخطة لو الخطة أصلًا اتبنت وهي عارفة إنه هيحصل.

ورمضان، اللي بيقلب شكل اليوم كله. لو الخطة اتعملت في شعبان من غير ما حد يفكر في رمضان، فالأسبوع التالت هيوصل في أول أسبوع صيام، والاتنين هيتقروا على إنهم حاجة واحدة.

## الفرق بين إنك تقفي وإنك تعدّلي

المشكلة مش إن حد بيزهق. المشكلة إن الزهق بيتقري على إنه حكم: «أنا مش بنفع»، «الخطة دي مش ليا»، «خلاص كل مرة بيحصل كده».

وبين الوقوف والتعديل مسافة كبيرة. الخطة اللي بتتعدل في الأسبوع التالت بتفضل شغالة؛ الخطة اللي بتتساب بترجع من الأول بعد شهرين، ومعاها الإحساس إن المحاولة دي كمان فشلت — وده الجزء اللي بيتراكم.

PRACTITIONER_VOICE: لما مريضة توصل للأسبوع التالت وتقولك «زهقت» — إنتِ بتقوليلها إيه بالظبط؟ الجملة اللي بتستخدميها فعلًا، مش الكلام النظري.

## إيه اللي بيتغيّر في المتابعة

المتابعة مش موجودة عشان حد يراجع عليكي. هي موجودة عشان اللحظة دي بالذات: عشان يبقى في حد شايف إن ده الأسبوع التالت، ويعرف إن ده شكله المتوقع، ومش يقرا الكلام على إنه فشل.

وده كمان اللي بيخلي التعديل ممكن. الشخص اللي بيتابع لوحده بيبقى قدامه خيارين: يكمّل زي ما هو، أو يبطل. الشخص اللي بيتابع مع حد شايف حالته بيبقى قدامه خيار تالت.

CLINICAL_INPUT: أول حاجة بتغيّريها في الخطة لما حد يوصل للنقطة دي إيه؟ لو في تعديل محدد بتبدأي بيه — اكتبيه.

CLINICAL_INPUT: في حاجة بتنصحي بيها قبل ما الأسبوع التالت يوصل، يعني من الأسبوع الأول، عشان اللحظة دي تبقى أسهل؟

## اللي يستاهل تفتكريه

الأسبوع التالت مش علامة إن الخطة غلط. هو علامة إن الخطة بقت جزء من اليوم العادي، ودي بالظبط الحاجة اللي كنتي عايزاها من الأول.

النزول السريع في الأول كان حقيقي بس مش كله شحم، والهدوء بعده مش توقف. والزهق ليه حل، بس الحل مش «حماس أكتر» — الحل إن الخطة تتعدل عشان تنفع في أسبوع عادي، مش في أسبوع فيه حماس.

لو وصلتي للنقطة دي دلوقتي، دي مش لحظة إنك تبطلي — دي بالظبط اللحظة اللي فيها الكلام مع حد بيفرق. تقدري تشوفي شكل [[specialty:weight-management|متابعة الوزن]] هنا، أو [[booking|تحجزي موعد]] وتتكلمي عن الأسبوع التالت بتاعك إنتِ.
AR,

                'en' => <<<'EN'
The most common sentence in the clinic is not "I cannot do it" or "the food is bad". It is: "I got bored."

And it tends to arrive at the same moment. Not the first week, not the third month. Week three.

This article is not about how to get motivated again. It is about the fact that week three has a recognisable, repeating shape, with understandable causes — half of them in the body and half in the mind — and that knowing the shape before it arrives changes what you do when it does.

## The first two weeks run on something else entirely

At the start you are running on novelty. Everything is new: the food, the routine, the very fact that there is a plan at all. Novelty gives real energy — but it has an expiry date, and it is not based on anything in the plan itself.

Something else is working early on, and it deceives more: the fast result.

## Why the scale moves quickly in the first fortnight — and why it is not fat

This is the most misread point of all, and misreading it is exactly what sets week three up.

The body stores carbohydrate in the liver and muscles as glycogen. Glycogen is not stored dry — it is stored with water, at more than its own weight. As soon as intake falls, and particularly if starch falls, the body starts drawing on that glycogen store, and the water bound to it leaves with it.

Something else happens at the same time: changing what you eat changes how much sodium you take in, and sodium affects how much water the body holds. Food cooked at home carries less salt than prepared food and food eaten out, so a modest shift in the kind of food you eat moves the number on the scale without changing anything about body composition.

There is a third factor, simpler and more easily forgotten: how much food is in the digestive tract at any given moment. Somebody eating smaller amounts, or spreading them differently across the day, is carrying less inside her — and that shows up on a scale like anything else.

So the number that fell in the first ten days was measuring something real — just not the thing you thought it was measuring. And when that store settles, the fall slows abruptly, and the eye reads the slowing as a stop.

Nothing has broken. The fast part has finished and the slow part remains — and the slow part is the real one.

If the number on the scale has become the thing that sets your mood in the morning, there is a separate article about that: [[article:what-the-scale-does-not-say|What the scale says, and what it does not]].

## Week three is the first ordinary week

By week three the novelty has gone, the fast fall has settled, and the plan has become simply… something you do. No excitement, no obvious reward.

That is not the moment it fails. It is the first moment you see the plan as it really is: something you are going to do for a long time. What happens is that your mind compares this feeling to week one, and the comparison is unfair — it measures a sustained state against a temporary one, and its result was decided before it began.

"I got bored", at that moment, means precisely: the thing that was exciting has become ordinary. That was always going to happen, whatever the plan was and whoever wrote it.

## What published guidance says about this moment

The National Institute for Health and Care Excellence (NICE), in its guidance on identifying and managing obesity, recommends that weight management programmes run over time and include follow-up, rather than being a single intervention. That recommendation is not an administrative detail — it is an acknowledgement that the hard part is not the beginning.

The same body, in separate guidance on individual behaviour change, describes what makes a change hold: planning in advance, defined goals, self-monitoring, and support sustained over time. Note what is not on that list: willpower.

The European Association for the Study of Obesity (EASO), in its guidelines for obesity management in adults, makes the same point from another direction: realistic goals and long-term follow-up matter more than the intensity of the intervention at its start.

And the World Health Organization classifies obesity as a chronic condition. That is not a harsh description — it is a description that determines the right shape of care. A chronic condition is managed. It is not resolved in thirty days.

The practical conclusion from all of this is that a plan continuing has little to do with how strongly it began. Follow-up and adjustment are part of the treatment itself, not an admission that something broke.

## The myth: "if you really wanted it, you would not have got bored"

This is the sentence said at home, said at work, and sometimes said to yourself in the mirror. It is wrong in a particular way that is worth spelling out.

The myth assumes that sticking to something is a trait of the person: either you have willpower or you do not. The idea spreads because it is simple, and because it explains everything without requiring you to know anything at all about the person's life.

What evidence-based guidance actually works on is entirely different: the conditions that make a behaviour easier or harder — what is in the kitchen, when food gets prepared, who cooks, and whether the plan was built around the food of this house or around food that is not sold in this market. Those things can be changed. "Willpower" cannot, which is precisely why it is a comfortable explanation and a useless one.

Boredom is not a defect of character. Boredom is the normal, predictable response to repeating anything. The problem is not that it happened — the problem is what gets read into it.

## Week three in an Egyptian kitchen

Week three arrives here in a particular way, for reasons that have to do with the shape of the food itself.

A plan with no baladi bread in it, no foul, no rice and no taameya asks somebody to live beside her own household's food without eating any of it. That works for a week, sometimes two. By the third there is a family meal, or an invitation, or a long day at work, and a plan with no relationship to that kitchen meets its first real test and loses it.

Egyptian food is not the problem. Foul, lentils and chickpeas are legumes; areesh cheese and yoghurt are available, affordable sources of protein; seasonal vegetables in the market are cheaper and better than anything imported. Moussaka, molokhia and home-cooked vegetables are real food. The distinction is not between "Egyptian food" and "healthy food" — it is in quantities, in how things are prepared, and in what gets eaten alongside what.

Then there is budget, which is not a detail. Any plan that assumes salmon, avocado and imported products has a known expiry date, not because the person is uncommitted but because it requires money that is not there. Legumes, eggs, areesh cheese, yoghurt, seasonal vegetables and local fish are real, available sources, and a plan built on them lasts longer because it shops in the same market the household already shops in.

And some things happen here at exactly this point, every time:

The Friday family lunch, which arrives after a fortnight of sticking to something and gets read as "breaking the plan" — when it is one day inside a plan that is supposed to last months.

Eating out, which usually means koshari or sandwiches, and which does not have to be a departure from the plan if the plan was built knowing it was going to happen.

And Ramadan, which turns the shape of the whole day over. If a plan was made in Shaaban without anybody thinking about Ramadan, week three lands in the first week of fasting, and the two get read as one thing.

## The distance between stopping and adjusting

The problem is not that people get bored. It is that boredom gets read as a verdict: I am not capable of this, this plan is not for me, it happens every time.

There is a great deal of room between stopping and adjusting. A plan adjusted in week three carries on working; a plan abandoned in week three starts again from zero two months later, and brings with it the sense that this attempt failed too — and that is the part that accumulates.

PRACTITIONER_VOICE: When a patient reaches week three and tells you she is bored, what do you actually say? The sentence you really use, not the theory.

## What follow-up is for

Follow-up does not exist so that somebody can check up on you. It exists for this moment in particular: so that there is somebody who can see that this is week three, who knows what that looks like, and who does not read it as failure.

It is also what makes adjustment possible at all. Somebody following a plan alone has two options: carry on exactly as before, or stop. Somebody following a plan with a clinician who has seen her case has a third.

CLINICAL_INPUT: What is the first thing you change in a plan when somebody reaches this point? If there is a specific adjustment you start with, write it.

CLINICAL_INPUT: Is there something you advise before week three arrives — from week one — to make this moment easier?

## Worth remembering

Week three is not a sign that the plan is wrong. It is a sign that the plan has become part of an ordinary day, which is exactly the thing you wanted from the beginning.

The fast fall at the start was real but was not all fat, and the slowing after it is not a stop. Boredom has an answer, but the answer is not more enthusiasm — it is adjusting the plan so that it works in an ordinary week rather than an excited one.

If you are at that point now, this is not the moment to stop. It is exactly the moment where talking to somebody makes a difference. You can see what [[specialty:weight-management|weight management follow-up]] looks like here, or [[booking|book an appointment]] and talk about your own week three.
EN,
            ],

            'citations' => [
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
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports "What published guidance says about this moment": that weight '
                        .'management programmes should run over time and include follow-up rather than being '
                        .'a one-off intervention. The general shape of the recommendation is cited, not a '
                        .'specific programme duration — the duration figure is the detail most likely to be '
                        .'misremembered. Confirm the guideline is still current (it has been updated since '
                        .'2014) and that the follow-up recommendation survives in the current edition.',
                ],
                [
                    'organisation' => [
                        'ar' => 'المعهد الوطني للصحة وجودة الرعاية (NICE)',
                        'en' => 'National Institute for Health and Care Excellence (NICE)',
                    ],
                    'title' => [
                        'ar' => 'تغيير السلوك: المداخل الفردية',
                        'en' => 'Behaviour change: individual approaches',
                    ],
                    'year' => 2014,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the list of what makes a change hold — planning, defined goals, '
                        .'self-monitoring, sustained support — and the claim that willpower is not among the '
                        .'components these interventions target. Confident the guidance exists; confirm the '
                        .'year and that these four components are the ones it names.',
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
                    'note' => 'Supports the sentence on realistic goals and long-term follow-up mattering '
                        .'more than initial intensity. Confident such a document exists under EASO; confirm '
                        .'the exact title and year, and that this emphasis is stated rather than inferred.',
                ],
                [
                    'organisation' => [
                        'ar' => 'منظمة الصحة العالمية',
                        'en' => 'World Health Organization',
                    ],
                    'title' => [
                        'ar' => 'صحيفة وقائع: السمنة وزيادة الوزن',
                        'en' => 'Fact sheet: Obesity and overweight',
                    ],
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports "the WHO classifies obesity as a chronic condition", used to justify '
                        .'the article\'s framing that it is managed rather than resolved. The fact sheet is '
                        .'revised periodically and carries no fixed year, so none is given — record the '
                        .'revision date current at verification.',
                ],
            ],
        ];
    }
}
