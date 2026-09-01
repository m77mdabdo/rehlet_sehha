<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * The scale, read honestly.
 *
 * THE SELF-WEIGHING SECTION IS DELIBERATELY NOT THE OBVIOUS ONE. It would have
 * been easy, and would have fitted this clinic's stance neatly, to write that
 * frequent weighing makes things worse. The evidence does not say that — a
 * good deal of it points the other way — and writing the tidier sentence would
 * have been exactly the failure this whole article is about: preferring a
 * number that supports the story you were already telling.
 *
 * So the section says what is actually known, including the part that cuts
 * against the clinic's instincts, and locates the real risk where the evidence
 * locates it: in what gets read into the number, and in who is reading it.
 */
class WhatTheScaleDoesNotSay extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'what-the-scale-does-not-say',
            'category' => 'weight-management',
            'tags' => ['adherence', 'lab-results'],
            'cover' => 'water-jug-morning-table',

            'title' => [
                'ar' => 'الميزان بيقول إيه — والحاجات اللي مش بيقولها',
                'en' => 'What the scale says, and what it does not',
            ],

            'excerpt' => [
                'ar' => 'قراية واحدة على الميزان فيها حاجات كتير غير اللي بتحاولي تقيسيها. وفي حاجات أهم منها بتتقاس بطريقة تانية.',
                'en' => 'One reading contains a great deal besides the thing you are trying to measure. And the things that matter more are measured another way.',
            ],

            'body' => [
                'ar' => <<<'AR'
الميزان بيدي رقم واحد، والرقم ده بيتقري على إنه إجابة. هو مش إجابة — هو قياس لحاجة واحدة اسمها كتلة الجسم كلها، وجواها حاجات كتير مالهاش أي علاقة بالسؤال اللي إنتِ بتسأليه.

المقال ده عن إيه اللي جوه الرقم ده بالظبط، وإيه اللي بيتقاس بطريقة تانية.

## القراية الواحدة فيها إيه غير اللي بتدوري عليه

وزن الجسم في أي لحظة هو مجموع كل حاجة جواه: العضل والعظم والأعضاء والدهون، وكمان المية، والأكل اللي لسه في الجهاز الهضمي. الحاجات دي مش بتتحرك بنفس السرعة ولا بنفس الاتجاه.

المية هي أكبر مصدر للتذبذب. الجسم بيخزن الكربوهيدرات على شكل جليكوجين، والجليكوجين بيتخزن ومعاه مية بأكتر من وزنه. فأي تغيير في كمية النشويات — يوم فيه رز وعيش أكتر من العادة، أو يوم أقل — بيغيّر مخزون الجليكوجين والمية المربوطة بيه، والميزان بيسجّل الفرق ده كأنه تغيّر حقيقي.

والصوديوم بيعمل نفس الحاجة. الأكل الجاهز والمعلبات والمخللات والجبنة المالحة والوجبات اللي بره بيبقى فيها ملح أكتر من الأكل المطبوخ في البيت، والملح بيخلي الجسم يمسك مية أكتر. عزومة واحدة ممكن تظهر على الميزان لتلات أو أربع أيام من غير ما يكون في أي تغيير في الدهون.

وفي الدورة الشهرية، اللي بتيجي معاها تغيّرات هرمونية بتأثر على احتباس المية بشكل متكرر كل شهر. القراية اللي بتفزّع في أسبوع معيّن من الشهر ممكن تكون بالظبط نفس القراية اللي طمّنت في أسبوع تاني.

وفي أبسط عامل وأكتر واحد بيتنسى: الأكل والشرب اللي لسه جوه. الشخص بيوزن أكتر بعد ما ياكل، وده مش دهون — ده أكل.

## الفرق بين الوزن وتركيب الجسم

الميزان العادي بيقيس الكتلة الكلية. هو مش عارف — ومش ممكن يعرف — الرقم ده اتوزّع إزاي بين العضل والدهون والمية.

ودي مش تفصيلة. شخصين بنفس الطول ونفس الوزن ممكن يكونوا مختلفين تمامًا في التركيب، ومختلفين في الحاجات اللي ليها علاقة بالصحة فعلًا. والشخص اللي بدأ يتحرك ويتمرن ممكن يبني كتلة عضلية وهو بيفقد دهون في نفس الوقت، فالرقم الكلي يقف مكانه بالأسابيع — وهو أصلًا بيتحسّن.

الرقم الواقف في الحالة دي مش «ثبات». هو رقم واحد بيلخّص حاجتين بيتحركوا في اتجاهين مختلفين، وبيطلع صفر.

وفي حاجة عملية قبل أي كلام عن القراية: الميزان نفسه. موازين مختلفة بتدي أرقام مختلفة، ونفس الميزان بيدي أرقام مختلفة حسب مكانه على الأرض — سيراميك مستوي غير سجادة. والقراية بتختلف حسب التوقيت في اليوم، وقبل الأكل ولا بعده، وبالهدوم ولا من غيرها. المقارنة بين قرايتين اتاخدوا بظروف مختلفة مش مقارنة أصلًا، حتى لو الجهاز مظبوط تمامًا.

## طب الأجهزة اللي بتقول نسبة الدهون؟

الموازين المنزلية اللي بتدي «نسبة دهون» بتشتغل بطريقة اسمها المقاومة الحيوية: بتبعت تيار ضعيف جدًا في الجسم وبتحسب الدهون من مقاومة الأنسجة له.

المشكلة إن المقاومة دي بتتأثر بشدة بكمية المية في الجسم — يعني بنفس الحاجة اللي إحنا بنحاول نتخلص من تأثيرها. القراية بتتغير حسب إنتِ شاربة ولا لأ، وأكلتي إمتى، واتمرنتي ولا لأ، وحتى حسب حرارة الجلد. الأجهزة دي مفيدة أكتر لو الشخص بيقارن قراياته هو بنفس الظروف على مدى شهور، ومضللة لو اتقرت على إنها رقم مطلق ودقيق.

وده سبب إن العيادة بتسأل عن حاجات تانية. الحاجات اللي بتحصل في الجسم لما الأكل والحركة يتظبطوا — الطاقة، النوم، الهضم، ومؤشرات التحاليل لما يكون ليها لازمة — بتتحرك على مدى زمني مختلف عن الوزن، وأحيانًا بتتحسن قبل ما الرقم يتحرك خالص.

## اللي التوصيات المنشورة بتوصي بقياسه جنب الوزن

منظمة الصحة العالمية عملت مشاورة خبراء مخصصة لمحيط الخصر ونسبة الخصر للورك، وخرجت بإن توزيع الدهون — مش الكتلة الكلية بس — معلومة ليها قيمة في تقدير المخاطر المرتبطة بالوزن. يعني «فين» مش أقل أهمية من «قد إيه».

والمعهد الوطني البريطاني للصحة وجودة الرعاية (NICE) في دليله عن السمنة بيوصي باستخدام محيط الخصر جنب مؤشر كتلة الجسم في التقييم، مش بدلًا منه. الفكرة إن المؤشرين مع بعض بيقولوا حاجة أكتر من كل واحد لوحده.

ومحيط الخصر ليه ميزة عملية كمان: شريط قياس أرخص من أي ميزان، ومش بيتأثر بمية الجليكوجين بنفس الحدة، وبيتحرك ببطء — وده بالظبط اللي إنتِ عايزاه من مقياس بتتابعي بيه حاجة بتتغير ببطء.

## هل الوزن اليومي فكرة وحشة؟

هنا لازم نبقى أمناء، لأن الإجابة السهلة مش هي الإجابة الصح.

في أدلة بتشير إن القياس المنتظم بيساعد ناس كتير في متابعة الوزن، لأنه بيدي معلومة مستمرة بدل ما الشخص يكتشف تغيّر كبير بعد شهور. القول إن «الوزن المتكرر وحش» على إطلاقه مش دقيق.

المشكلة مش في القياس نفسه — المشكلة في القراية. القراية اليومية فيها ضوضاء يومية طبيعية (مية، ملح، أكل في الجهاز الهضمي)، والعين البشرية سيئة جدًا في فصل الضوضاء عن الاتجاه. فاللي بيحصل إن يوم بيبدأ بحكم على النفس مبني على رقم اتحرك لأسباب مالهاش علاقة بأي حاجة عملتيها.

وفي مجموعة بالذات القياس المتكرر بيبقى مقلق فيها فعلًا: أي حد عنده علاقة مضطربة بالأكل. دليل NICE عن اضطرابات الأكل بيتعامل مع الوزن والتعامل معاه كموضوع حساس إكلينيكيًا يتاخد فيه قرار فردي — مش قاعدة عامة تتقال لكل الناس.

يعني السؤال مش «كل قد إيه». السؤال هو: الرقم بيعمل إيه فيكي لما تشوفيه؟

CLINICAL_INPUT: إنتِ بتنصحي بالقياس كل قد إيه؟ وفي حالات بتنصحي فيها حد إنه يبطل يقيس خالص — إيه هي؟

وفي نقطة أخيرة عن التوقيت. الوزن مش بيتحرك بخط مستقيم لأي حد، حتى في أفضل الظروف. الشكل الطبيعي فيه صعود ونزول حوالين اتجاه عام، والاتجاه ده مش بيبان في أسبوع — بيبان في شهور. اللي بيقارن قراية النهارده بقراية امبارح مش بيقيس الاتجاه؛ هو بيقيس الضوضاء وبس.

## خرافة: «الميزان مش بيكدب»

الجملة دي صح حرفيًا وغلط عمليًا، وده اللي بيخليها منتشرة.

الميزان فعلًا مش بيكدب: هو بيقيس الكتلة الكلية بدقة معقولة. الكدب مش في الجهاز — الكدب في إن الرقم بيتقري على إنه إجابة على سؤال مختلف تمامًا. السؤال اللي في دماغ الشخص هو «هل أنا بتحسن؟»، والجهاز بيجاوب على «كام كيلو كل حاجة جواكي دلوقتي؟».

والخرافة بتنتشر لأن الرقم بسيط، ومجاني، وموجود في الحمام، ومش محتاج حد يفسره. وكل الحاجات اللي بتوصف التحسّن فعلًا — تركيب الجسم، محيط الخصر، الطاقة، النوم، التحاليل، الالتزام — أبطأ وأصعب وبعضها محتاج عيادة.

## في المطبخ المصري: الحاجات اللي بتحرك الرقم من غير ما تحرك حاجة

رمضان مثال واضح. شكل اليوم بيتغير كله — عدد الوجبات، توقيتها، كمية المية والملح، النوم — والوزن بيتحرك في الاتجاهين لأسباب كتير جدًا في نفس الوقت. قراية أول أسبوع في رمضان مقارنة بقراية آخر أسبوع في شعبان مش مقارنة عادلة لأي حاجة.

والعزومات. أكلة واحدة فيها محشي ومكرونة بشاميل وسلطات فيها ملح ممكن تخلي الميزان يطلع كام أوقية لكام يوم، ومعظم ده مية.

والأكل اللي بره: الكشري والوجبات السريعة فيها ملح ونشويات أكتر من الأكل البيتي، والأثر بيبان بسرعة وبيروح بنفس السرعة.

والعكس صحيح كمان. أسبوع أكل فيه خضار وسلطة وشوربة عدس ومية كتير ممكن يوري نزول أسرع من الحقيقة، وبعدين يبان إنه «وقف» لما الجسم يستقر.

## اللي العيادة بتتابعه غير الرقم

في المتابعة الجدية الوزن هو مؤشر واحد جوه مجموعة. اللي بيتسأل عنه كمان: الطاقة خلال اليوم، النوم، الهضم، الالتزام بالخطة وأي جزء منها بقى صعب، والتحاليل لما يكون ليها لازمة إكلينيكية. ومحيط الخصر لما يكون مناسب.

الحاجات دي مع بعض بتوصف الحالة بشكل أقرب للحقيقة بكتير من رقم واحد، وأهم من كده: بتدي حاجة تتعدل. رقم واقف مش بيقول لحد يعمل إيه بعده.

CLINICAL_INPUT: إيه الحاجات اللي إنتِ بتتابعيها فعليًا مع المريضة جنب الوزن؟ اذكري اللي بتستخدميه في العيادة.

PRACTITIONER_VOICE: إيه أكتر موقف بيتكرر قدامك لمريضة اتفزعت من قراية ميزان، وطلع السبب حاجة زي دي؟

## اللي يستاهل تفتكريه

الميزان أداة، مش حكم. وأي أداة بتبقى مفيدة لما تتقري صح ومؤذية لما تتقري غلط.

القراية الواحدة فيها مية وملح وأكل ودورة شهرية وتوقيت، وتركيب الجسم مش بيظهر فيها أصلًا. والحاجات اللي بتتقاس بطريقة تانية — محيط الخصر، الطاقة، التحاليل، الالتزام — هي اللي بتديكي حاجة تعمليها.

لو الرقم بقى أول حاجة بتحدد مزاجك الصبح، دي مش مشكلة في إرادتك — دي مشكلة في إن الأداة بقت بتستخدمك. والحل بيبدأ بكلام مع حد شايف حالتك: تقدري تشوفي [[specialty:weight-management|متابعة الوزن]] أو [[booking|تحجزي موعد]].

ولو حابة تفهمي ليه الرقم بيتحرك بسرعة في الأول وبعدين يهدى، ده موضوع مقال [[article:why-we-quit-in-week-three|ليه بنسيب النظام في الأسبوع التالت]].
AR,

                'en' => <<<'EN'
A scale gives one number, and that number gets read as an answer. It is not an answer. It measures one thing — total body mass — and a great deal inside it has nothing to do with the question you are actually asking.

This article is about what is inside that number, and about what gets measured another way.

## What a single reading contains besides what you are looking for

Body weight at any moment is the sum of everything inside you: muscle, bone, organs, fat, and also water, and also food still moving through the digestive tract. Those things do not move at the same speed or in the same direction.

Water is the largest source of fluctuation. The body stores carbohydrate as glycogen, and glycogen is stored with water at more than its own weight. So any change in starch intake — a day with more rice and bread than usual, or a day with less — changes the glycogen store and the water bound to it, and the scale records that difference as though it were a real change.

Sodium does the same thing. Prepared food, tinned food, pickles, salted cheese and meals eaten out carry more salt than food cooked at home, and salt makes the body hold more water. A single family lunch can show on the scale for three or four days without any change in fat at all.

Then there is the menstrual cycle, which brings hormonal changes affecting water retention on a repeating monthly pattern. The reading that alarms you in one week of the month may be precisely the reading that reassured you in another.

And the simplest factor, the one most often forgotten: the food and drink still inside. A person weighs more after eating. That is not fat. That is food.

## The difference between weight and body composition

An ordinary scale measures total mass. It does not know — and cannot know — how that number is distributed between muscle, fat and water.

This is not a technicality. Two people of the same height and weight can be entirely different in composition, and different in the things that actually relate to health. And somebody who has started moving and training can build muscle while losing fat at the same time, so the total number stands still for weeks — while it is in fact improving.

A number standing still in that situation is not a plateau. It is one figure summarising two things moving in opposite directions, and coming out as zero.

And one practical thing before any talk of interpretation: the scale itself. Different scales give different numbers, and the same scale gives different numbers depending on what it is standing on — level tiles are not a carpet. The reading also changes with the time of day, with whether you have eaten, and with whether you are dressed. A comparison between two readings taken under different conditions is not a comparison at all, however well calibrated the device is.

## What about the devices that report a body fat percentage?

Home scales that give a "body fat percentage" work by bioelectrical impedance: they pass a very small current through the body and estimate fat from how the tissues resist it.

The difficulty is that this resistance is strongly affected by how much water is in the body — which is the very thing whose influence we were trying to remove. The reading changes depending on whether you have had a drink, when you last ate, whether you have trained, and even on skin temperature. These devices are more useful when somebody compares her own readings under the same conditions over months, and misleading when read as an absolute, precise figure.

This is part of why a clinic asks about other things. What happens in the body when food and movement are adjusted — energy, sleep, digestion, and lab markers where there is a reason to look at them — moves on a different timescale from weight, and sometimes improves before the number moves at all.

## What published guidance recommends measuring alongside weight

The World Health Organization convened an expert consultation specifically on waist circumference and waist–hip ratio, and concluded that fat distribution — not total mass alone — carries value in estimating weight-related risk. Where matters, not only how much.

And the National Institute for Health and Care Excellence (NICE), in its obesity guidance, recommends using waist measurement alongside body mass index in assessment, not instead of it. The point is that the two together say more than either says alone.

Waist measurement has a practical advantage as well: a tape measure is cheaper than any scale, it is not affected by glycogen water to the same degree, and it moves slowly — which is exactly what you want from something you are using to follow a change that is itself slow.

## Is weighing daily a bad idea?

Here we have to be honest, because the easy answer is not the correct one.

There is evidence that regular self-weighing helps many people manage their weight, because it gives continuous information rather than letting somebody discover a large change months later. The blanket claim that frequent weighing is harmful is not accurate.

The problem is not the measuring — it is the reading. A daily figure contains normal daily noise (water, salt, food in transit), and the human eye is very poor at separating noise from trend. What happens is that a day begins with a judgement about yourself, based on a number that moved for reasons unconnected to anything you did.

And for one group in particular, frequent weighing genuinely is a concern: anyone with a disordered relationship to eating. NICE guidance on eating disorders treats weighing, and how it is handled, as a clinically sensitive matter decided individually — not a general rule announced to everybody.

So the question is not "how often". The question is: what does the number do to you when you see it?

CLINICAL_INPUT: How often do you advise weighing? And are there cases where you advise somebody to stop weighing altogether — which ones?

One last point, about time. Weight does not move in a straight line for anybody, even under the best conditions. The normal shape is a rise and fall around a general trend, and that trend does not appear in a week — it appears over months. Comparing today's reading with yesterday's is not measuring the trend; it is measuring the noise and nothing else.

## The myth: "the scale does not lie"

This sentence is literally true and practically false, which is exactly why it spreads.

The scale really does not lie: it measures total mass with reasonable accuracy. The lie is not in the device — it is in the number being read as an answer to an entirely different question. The question in the person's mind is "am I getting better?" and the device is answering "how many kilograms is everything inside you right now?".

The myth persists because the number is simple, free, already in the bathroom, and requires nobody to interpret it. Everything that actually describes improvement — body composition, waist measurement, energy, sleep, lab results, how the plan is going — is slower, harder, and some of it needs a clinic.

## In an Egyptian kitchen: what moves the number without moving anything

Ramadan is the clearest example. The shape of the whole day changes — how many meals, when, how much water and salt, how much sleep — and weight moves in both directions for several reasons at once. A reading in the first week of Ramadan compared with one from the last week of Shaaban is not a fair comparison of anything.

Then the family gatherings. A single meal of mahshi, béchamel pasta and salted salads can put an ounce or two on the scale for a few days, and most of that is water.

And eating out: koshari and fast food carry more salt and more starch than home cooking, and the effect appears quickly and leaves at the same speed.

The reverse is true too. A week of vegetables, salad, lentil soup and plenty of water can show a faster fall than is real, and then appear to "stop" once the body settles.

## What the clinic follows besides the number

In serious follow-up, weight is one indicator inside a set. What also gets asked about: energy through the day, sleep, digestion, how the plan is going and which part of it has become hard, and lab results where there is a clinical reason for them. And waist measurement where it is appropriate.

Together those describe the situation far more accurately than a single figure, and — more importantly — they give something that can be adjusted. A number standing still tells nobody what to do next.

CLINICAL_INPUT: What do you actually track alongside weight with a patient? Name what you use in clinic.

PRACTITIONER_VOICE: What is the situation you see most often where a patient was alarmed by a reading, and the cause turned out to be something like this?

## Worth remembering

A scale is a tool, not a verdict. Any tool is useful read correctly and harmful read wrongly.

A single reading contains water, salt, food, a menstrual cycle and a time of day — and body composition does not appear in it at all. The things measured another way — waist, energy, lab work, how the plan is going — are the ones that give you something to act on.

If the number has become the first thing that sets your mood in the morning, that is not a problem with your willpower — it is a problem with a tool that has started using you. The answer begins with a conversation with somebody who has seen your case: you can look at [[specialty:weight-management|weight management follow-up]] or [[booking|book an appointment]].

And if you want to understand why the number falls quickly at first and then settles, that is the subject of [[article:why-we-quit-in-week-three|Why we quit in week three]].
EN,
            ],

            'citations' => [
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => [
                        'ar' => 'محيط الخصر ونسبة الخصر إلى الورك: تقرير مشاورة خبراء',
                        'en' => 'Waist Circumference and Waist–Hip Ratio: Report of a WHO Expert Consultation',
                    ],
                    'year' => 2008,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the claim that fat distribution, not total mass alone, carries value '
                        .'in estimating weight-related risk. Confident the consultation and report exist; the '
                        .'year given is the consultation date and the report was published later — confirm '
                        .'which year should appear.',
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
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports "recommends using waist measurement alongside body mass index in '
                        .'assessment". No threshold value is quoted in the article, deliberately — the '
                        .'specific waist-to-height figure is the detail most likely to be misremembered and '
                        .'has changed between editions. Confirm the current edition still recommends waist '
                        .'measurement alongside BMI.',
                ],
                [
                    'organisation' => [
                        'ar' => 'المعهد الوطني للصحة وجودة الرعاية (NICE)',
                        'en' => 'National Institute for Health and Care Excellence (NICE)',
                    ],
                    'title' => [
                        'ar' => 'اضطرابات الأكل: التعرّف والعلاج',
                        'en' => 'Eating disorders: recognition and treatment',
                    ],
                    'year' => 2017,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the paragraph on frequent weighing being a clinically sensitive '
                        .'matter decided individually in people with disordered eating, rather than a '
                        .'general rule. Confident the guideline exists and covers weighing; confirm that it '
                        .'frames the decision as individual rather than prescribing a frequency.',
                ],
            ],
        ];
    }
}
