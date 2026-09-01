<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * The article where every claim has a supplement being sold behind it.
 *
 * NO PROTEIN FIGURE APPEARS ANYWHERE, deliberately. Grams per kilogram is the
 * single number every reader wants and is exactly what the brief defines as
 * CLINICAL_INPUT: a specific quantity aimed at the person reading. It is also
 * the number a draft written from memory is most likely to get wrong by a
 * factor that matters. So the article explains why the requirement is higher
 * and hands the figure to the clinician.
 *
 * The contamination section earns its place on local grounds: supplements here
 * arrive through informal channels with no way to check what is in the tub, and
 * the documented finding of undeclared substances in sports supplements is the
 * most useful thing this article can tell an Egyptian reader.
 */
class EatingAroundTraining extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'eating-around-training',
            'category' => 'sports-nutrition',
            'tags' => ['supplements', 'myths'],
            'cover' => 'tuna-eggs-chickpea-bowl',

            'title' => [
                'ar' => 'الأكل حوالين التمرين — إيه اللي ليه دليل',
                'en' => 'Eating around training — what has evidence behind it',
            ],

            'excerpt' => [
                'ar' => 'الجيم مليان نصايح واثقة عن الأكل والمكملات. ده اللي التوصيات المتخصصة بتقوله، واللي مالوش أساس.',
                'en' => 'A gym is full of confident advice about food and supplements. Here is what specialist guidance says, and what has no basis.',
            ],

            'body' => [
                'ar' => <<<'AR'
أول أسبوع في الجيم بيوصلك كلام أكتر من التمرين نفسه. لازم بروتين بعد التمرين على طول. الكارب بالليل بيتخزن دهون. لازم واي بروتين. الكرياتين بيمسك مية. البروتين بيتعب الكلى.

الكلام ده بيتقال بثقة، وبعضه له أساس وبعضه مالوش. المقال ده بيفصل بينهم.

## الأول: إيه اللي بيحصل في العضلة أصلًا

العضلة مش حاجة ثابتة. بروتين العضلة بيتبني وبيتكسر باستمرار طول اليوم، والفرق بين المعدلين هو اللي بيحدد الاتجاه.

التمرين — وبالذات تمرين المقاومة — بيعمل حاجتين: بيحفّز إشارات البناء لفترة بعد التمرين، وبيزوّد حساسية العضلة للبروتين اللي بياكله الشخص. ووجود أحماض أمينية متاحة في الفترة دي بيدعم البناء.

يعني التمرين بيفتح الباب، والأكل هو اللي بيدخل منه. أي واحد فيهم لوحده بيدي نتيجة أقل بكتير.

والتكيّف ده بياخد وقت. التغيّر في العضلة بيتراكم على مدى أسابيع وشهور، مش على مدى وجبة.

## البروتين: الاحتياج بيزيد فعلًا، وده مش تسويق

في بيان موقف مشترك عن التغذية والأداء الرياضي، صادر عن الأكاديمية الأمريكية للتغذية وأخصائيي التغذية في كندا والكلية الأمريكية للطب الرياضي، بيتعامل مع احتياج الرياضيين من البروتين على إنه أعلى من الاحتياج العام للبالغين غير النشطين.

والجمعية الدولية للتغذية الرياضية (ISSN) عندها بيان موقف مخصص للبروتين والتمرين بيمشي في نفس الاتجاه.

فالجزء ده من كلام الجيم صح: اللي بيتمرن بجدية احتياجه من البروتين أعلى.

الجزء اللي بيتحرّف هو الكمية والمصدر. الاحتياج الأعلى مش معناه إن الرقم مفتوح، ولا إن المصدر لازم يكون مكمل.

CLINICAL_INPUT: احتياج البروتين بيتحسب إزاي عندك، وإيه الرقم اللي بتشتغلي بيه لحد بيتمرن مقاومة بانتظام؟

## خرافة: «لازم البروتين خلال نص ساعة بعد التمرين»

دي أشهر خرافة في الجيم، واسمها «النافذة الأنابوليكية».

الفكرة إن في نافذة ضيقة بعد التمرين لو فاتت، التمرين «راح». والصورة اللي طلعت من الأبحاث بعدين مختلفة: حساسية العضلة للبروتين بتفضل مرتفعة لفترة أطول بكتير من نص ساعة، وإجمالي البروتين على مدار اليوم بيبقى أهم من التوقيت الدقيق للوجبة اللي بعد التمرين.

الخرافة دي منتشرة لسبب واضح: هي بتبيع منتج بيتشرب بسرعة في غرفة الملابس. الشيكر بعد التمرين مباشرة صورة كويسة للتسويق.

ده مش معناه إن التوقيت ملوش أي أهمية — معناه إن الشخص اللي بياكل وجبة كاملة فيها بروتين بعد التمرين بساعة مش «ضيّع» حاجة.

## خرافة: «البروتين بيتعب الكلى»

الجملة دي بتخوّف ناس كتير، وهي محتاجة تفصيل.

في مرضى عندهم قصور في وظائف الكلى بيتحطلهم قيود على البروتين، وده قرار إكلينيكي بيتاخد بناءً على الحالة وبيتابع.

لكن نقل القاعدة دي على شخص كليته سليمة مش مدعوم. المعلومة اتعممت من سياق مرضي لسياق عام، وده شكل شائع لانتشار الخرافات: قاعدة صح في مكانها بتتنقل لمكان تاني.

اللي يستاهل يتقال: أي حد عنده حالة كلوية معروفة، أو عنده سكري أو ضغط، لازم يتكلم قبل ما يزوّد البروتين. ودي مش تفصيلة إجرائية.

## خرافة: «الكارب بالليل بيتخزن دهون»

من أكتر الجمل تكرارًا في الجيم، ومبنية على سوء فهم لطريقة اشتغال الجسم.

الجسم مش بيقلب على «وضع تخزين» عند ساعة معينة. اللي بيحدد الاتجاه على المدى الطويل هو إجمالي الطاقة الداخلة والخارجة عبر الأيام، مش ساعة الوجبة على الحيطة.

وفي الواقع العملي في مصر، الوجبة الأساسية للعيلة بتكون بالليل في بيوت كتير. الخطة اللي بتمنع النشويات بالليل بتطلب من واحد ياكل لوحده وقت العيلة بتتجمع، ودي خطة بتقف بسرعة لأسباب مالهاش علاقة بالأيض.

اللي ليه أساس فعلًا هو موضوع تاني: الوجبة الدسمة جدًا قبل النوم مباشرة ممكن تسبب حموضة أو نوم متقطع عند بعض الناس. ودي شكوى حقيقية، بس هي مش «تخزين دهون».

## خرافة: «مفيش نتيجة من غير مكمل»

الجملة دي بتتقال بشكل غير مباشر أكتر من المباشر: صور قبل وبعد، وحساب بيبيع، وإحساس إن اللي بيتمرن من غير منتج ناقصه حاجة.

اللي بيحصل فعلًا إن المتغيرات اللي بتفرق في النتيجة مترتبة كالآتي تقريبًا: انتظام التمرين نفسه، وشكل الأكل على مدى اليوم والأسبوع، والنوم، وبعد كل ده المكملات.

والمكمل بيتحط في آخر القايمة مش لأنه ملوش قيمة، لكن لأن قيمته بتظهر لما اللي فوقه يكون متظبط. الشخص اللي بينام خمس ساعات وبياكل وجبة واحدة في اليوم وبياخد مكمل بيحل آخر واحد في الترتيب.

## المكملات: فرز سريع

أهم فكرة قبل أي كلام عن منتج بعينه: المكمل اسمه مكمل لأنه بيكمّل أكل موجود. المكمل اللي بيتاخد فوق أكل ناقص بيحل جزء صغير من مشكلة كبيرة.

الواي بروتين مصدر بروتين مركّز وسريع التحضير. هو مش مادة سحرية — هو لبن معالج. فايدته العملية إنه سهل، وده مهم لحد وقته ضيق. والمصادر الطبيعية بتعمل نفس الشغل.

والكرياتين من أكتر المكملات اللي اتدرست في الرياضة، وله بيان موقف مخصص من الجمعية الدولية للتغذية الرياضية. وهو مش هرمون ومش منشط، والكلام عن «إنه بيمسك مية» ليه أساس جزئي مرتبط بالمية داخل الخلايا العضلية.

وباقي رفوف المحل — حارقات الدهون، مكملات «الطاقة»، الخلطات اللي فيها عشرين مكون — الأدلة عليها بتتراوح بين ضعيفة ومعدومة، وبعضها فيه منشطات بكميات عالية.

CLINICAL_INPUT: في مكمل بتوافقي عليه فعلًا لحد بيتمرن؟ وإيه الشرط قبله؟

## نقطة أهم من كل اللي فوق: إيه اللي جوه العلبة

دي الحاجة اللي المفروض تتقال في مصر أكتر من أي حاجة تانية في المقال ده.

في أبحاث فحصت مكملات رياضية معروضة في السوق ولقيت في نسبة منها مواد مش مذكورة على العبوة — منها منشطات ومنها مشتقات ستيرويدية. يعني الشخص ممكن يكون بياخد حاجة مش عارف إنها موجودة أصلًا.

والخطر ده بيزيد لما المنتج بييجي من مصدر غير رسمي: حد بيبيع في الجيم، صفحة على فيسبوك، شحنة من بره من غير أوراق. المنتج اللي مالوش مصدر معروف مالوش محتوى معروف.

ودي مش نظرية: ناس اتعملت لهم تحاليل ولقوا فيها حاجات ملهاش تفسير غير المكمل، وناس عندهم حالات مزمنة اتأثروا بمواد ماكانوش يعرفوا إنهم بياخدوها.

الحاجة العملية: أي مكمل بيتاخد يتقال في الكشف، ويفضّل صورة للعبوة والمكونات.

## قبل التمرين وبعده — في مطبخ مصري

الكلام ده بيبان معقد وهو مش كده.

الأكل قبل التمرين هدفه إن الشخص يبقى عنده طاقة متاحة ومعدة مرتاحة. الأكل البيتي بيقدم ده بسهولة: عيش بلدي مع تمر أو موز، أو شوفان بلبن، أو ساندوتش جبنة قريش. الوجبة الدسمة القريبة من التمرين بتبطّئ الهضم وبتعمل ثقل، مش لأنها «غلط» لكن لأن التوقيت مش مناسب.

وبعد التمرين المطلوب وجبة فيها بروتين ونشويات. والمطبخ المصري مليان: فول وبيض، عدس، تونة مع رز، فراخ أو سمك بلدي مع خضار، زبادي مع فاكهة، جبنة قريش مع عيش. البقوليات مصدر بروتين حقيقي ورخيص، والبيض من أرخص وأعلى المصادر جودة.

الفكرة إن اللي محتاج يتظبط هو شكل اليوم كله، مش الوجبتين اللي حوالين التمرين.

ورمضان بيقلب ده كله. التمرين بيتنقل لتوقيت تاني، والأكل والمية بيتركزوا في ساعات محدودة، والترطيب بيبقى نقطة حقيقية. اللي بيتمرن في رمضان محتاج خطة اتعملت لرمضان، مش نفس الخطة بتوقيت مختلف.

## والحركة نفسها — قبل أي كلام عن مكمل

منظمة الصحة العالمية عندها إرشادات للنشاط البدني والسلوك الخامل بتحدد اللي المفروض يتعمل أسبوعيًا للبالغين، وبتشمل نشاط هوائي وتمارين تقوية العضلات.

والنقطة العملية إن معظم الناس اللي بيسألوا عن مكملات لسه مش بيعملوا الحد الأدنى من النشاط ده. ترتيب الأولويات ده مش تفصيلة: الفرق بين اللي بيتمرن بانتظام واللي لأ أكبر بكتير من الفرق بين اللي بياخد مكمل واللي لأ.

PRACTITIONER_VOICE: إيه أكتر حاجة بتشوفيها عند حد بدأ يتمرن وبدأ ياخد مكملات في نفس الوقت؟

وحاجة أخيرة عن الستات اللي بيتمرنوا: في قلق شائع إن تمرين المقاومة «هيعمل عضلات ضخمة». ده مش اللي بيحصل عمليًا، والفروق الهرمونية بتخلي شكل التكيف مختلف. والخوف ده بيمنع ناس كتير من نوع التمرين اللي له أكبر أثر على قوة العظام والعضلات مع التقدم في السن.

## اللي يستاهل تفتكريه

التمرين بيفتح الباب والأكل بيدخل منه، والتكيّف بيتراكم على مدى شهور مش وجبات.

احتياج البروتين بيزيد فعلًا مع التمرين المنتظم، والنافذة الضيقة بعد التمرين خرافة تسويقية، وإجمالي اليوم أهم من التوقيت الدقيق. و«البروتين بيتعب الكلى» قاعدة اتنقلت من سياق مرضي لسياق عام.

المكملات فيها حاجة أو اتنين ليهم أدلة معقولة، والباقي رفوف. والأخطر إن اللي مكتوب على العلبة مش دايمًا اللي جواها.

ولو الحركة نفسها لسه مش منتظمة، ده المكان اللي الفرق بيتعمل فيه.

لو عايز خطة مبنية على تمرينك وحالتك: [[specialty:sports-nutrition|التغذية الرياضية]] أو [[booking|احجز موعد]].

ولو بتقرا كلام عن مكمل معيّن وعايز طريقة تحكم بيها عليه، نفس الطريقة في مقال تاني: [[article:pcos-and-food-judging-a-claim|إزاي تحكمي على أي ادعاء]].
AR,

                'en' => <<<'EN'
The first week in a gym brings more advice than training. Protein immediately after the session. Carbohydrate at night is stored as fat. You need whey. Creatine holds water. Protein damages your kidneys.

All of it said with confidence, some of it with a basis and some without. This article separates them.

## First: what actually happens in a muscle

A muscle is not a fixed thing. Muscle protein is continuously built and broken down throughout the day, and the difference between those two rates determines the direction.

Training — resistance training in particular — does two things: it stimulates building signals for a period afterwards, and it increases the muscle's sensitivity to the protein a person eats. Having amino acids available during that period supports the building.

So training opens the door, and food is what walks through it. Either one alone gives a far smaller result.

And this adaptation takes time. Change in muscle accumulates over weeks and months, not over a meal.

## Protein: the requirement genuinely rises, and that is not marketing

A joint position statement on nutrition and athletic performance, issued by the Academy of Nutrition and Dietetics, Dietitians of Canada and the American College of Sports Medicine, treats athletes' protein requirement as higher than the general requirement for inactive adults.

And the International Society of Sports Nutrition (ISSN) has a position stand devoted to protein and exercise that runs in the same direction.

So that part of the gym advice is correct: somebody training seriously has a higher protein requirement.

What gets distorted is the amount and the source. A higher requirement does not mean an unbounded number, and it does not mean the source has to be a supplement.

CLINICAL_INPUT: How do you calculate a protein requirement, and what figure do you work with for somebody doing regular resistance training?

## The myth: "you must have protein within thirty minutes of finishing"

This is the most famous myth in any gym, and it has a name: the anabolic window.

The idea is that there is a narrow window after training and that missing it wastes the session. The picture that emerged from subsequent research is different: the muscle's sensitivity to protein remains elevated for far longer than half an hour, and total protein across the day matters more than the precise timing of the meal after training.

The myth spreads for an obvious reason: it sells a product that can be drunk quickly in a changing room. A shaker immediately after training is good marketing imagery.

This does not mean timing is irrelevant — it means somebody who eats a full meal containing protein an hour after training has not lost anything.

## The myth: "protein damages your kidneys"

This sentence frightens a great many people and needs unpacking.

There are patients with impaired kidney function for whom protein restrictions are set, and that is a clinical decision made on the case and followed up.

But transferring that rule to somebody with healthy kidneys is not supported. The information was generalised from a disease context into a general one, and that is a common shape for myths: a rule that is correct in its place gets moved somewhere else.

What is worth saying: anybody with a known kidney condition, or with diabetes or high blood pressure, should speak to somebody before increasing protein. That is not a procedural detail.

## The myth: "carbohydrate at night is stored as fat"

One of the most repeated sentences in any gym, and built on a misunderstanding of how the body works.

The body does not switch into a "storage mode" at a particular hour. What determines the direction over the long run is total energy in and out across days, not the position of a meal on a clock.

And in practical Egyptian reality, the main family meal is in the evening in a great many households. A plan that forbids starch at night asks somebody to eat alone while the family gathers, and such a plan stops quickly for reasons that have nothing to do with metabolism.

What does have a basis is a different matter: a very heavy meal immediately before sleeping can cause reflux or broken sleep in some people. That is a real complaint — but it is not "fat storage".

## The myth: "there is no result without a supplement"

This gets said indirectly far more often than directly: before-and-after photographs, an account that sells, and a sense that somebody training without a product is missing something.

What actually happens is that the variables affecting the result are ordered roughly like this: the regularity of the training itself, the shape of eating across the day and the week, sleep, and after all of that, supplements.

Supplements sit at the end of that list not because they have no value, but because their value appears once the things above them are in order. Somebody sleeping five hours and eating one meal a day and taking a supplement is solving the last item on the list.

## Supplements: a quick sort

The most important idea before discussing any particular product: a supplement is called a supplement because it supplements food that is already there. A supplement taken on top of inadequate eating solves a small part of a large problem.

Whey protein is a concentrated, quickly prepared protein source. It is not a magical substance — it is processed milk. Its practical value is convenience, which matters for somebody short of time. Food sources do the same job.

Creatine is among the most studied supplements in sport and has a position stand devoted to it from the International Society of Sports Nutrition. It is not a hormone and not a stimulant, and the talk about it "holding water" has a partial basis relating to water inside muscle cells.

The rest of the shelf — fat burners, "energy" supplements, blends containing twenty ingredients — carries evidence ranging from weak to absent, and some of it contains stimulants in high amounts.

CLINICAL_INPUT: Is there a supplement you actually approve for somebody training? And what is the condition before it?

## More important than any of the above: what is inside the tub

This is the thing that ought to be said in Egypt more than anything else in this article.

Research examining sports supplements on the market has found that a proportion of them contain substances not declared on the label — stimulants among them, and steroid derivatives. Which means somebody may be taking something she does not know is there.

And the risk rises when the product comes through an informal channel: somebody selling in the gym, a page on Facebook, a shipment from abroad with no paperwork. A product with no known source has no known contents.

This is not theoretical: people have had test results containing things with no explanation other than a supplement, and people with chronic conditions have been affected by substances they did not know they were taking.

The practical point: any supplement being taken should be mentioned in the consultation, preferably with a photograph of the tub and the ingredient list.

## Before and after training — in an Egyptian kitchen

This looks complicated and is not.

Food before training exists to leave somebody with available energy and a comfortable stomach. Home food does that easily: baladi bread with dates or banana, oats with milk, an areesh cheese sandwich. A heavy meal close to training slows digestion and sits heavily — not because it is "wrong" but because the timing does not suit.

After training what is wanted is a meal containing protein and starch. The Egyptian kitchen is full of it: foul and eggs, lentils, tuna with rice, chicken or local fish with vegetables, yoghurt with fruit, areesh cheese with bread. Legumes are a real and inexpensive protein source, and eggs are among the cheapest and highest-quality sources there are.

The point is that what needs sorting out is the shape of the whole day, not the two meals nearest the session.

Ramadan turns all of this over. Training moves to a different time, food and water are compressed into limited hours, and hydration becomes a real issue. Anybody training in Ramadan needs a plan made for Ramadan, not the same plan at a different hour.

## And the activity itself — before any talk of a supplement

The World Health Organization has guidelines on physical activity and sedentary behaviour setting out what adults should be doing weekly, including aerobic activity and muscle-strengthening exercise.

The practical point is that most people asking about supplements are not yet doing that minimum. This ordering of priorities is not a detail: the difference between somebody who trains regularly and somebody who does not is far larger than the difference between somebody taking a supplement and somebody not.

PRACTITIONER_VOICE: What do you see most often in somebody who has started training and started taking supplements at the same time?

One last thing for women who train: there is a common worry that resistance training will produce bulky muscle. That is not what happens in practice, and hormonal differences make the shape of the adaptation different. That fear keeps a great many people away from the kind of training with the largest effect on bone and muscle strength as they get older.

## Worth remembering

Training opens the door and food walks through it, and adaptation accumulates over months rather than meals.

The protein requirement genuinely rises with regular training; the narrow post-workout window is a marketing myth, and the daily total matters more than precise timing. And "protein damages your kidneys" is a rule moved out of a disease context into a general one.

Among supplements there are one or two with reasonable evidence, and the rest are shelves. And more dangerous than any of it: what is printed on the tub is not always what is inside it.

And if the training itself is not yet regular, that is where the difference gets made.

If you want a plan built on your training and your case: [[specialty:sports-nutrition|sports nutrition]] or [[booking|book an appointment]].

And if you are reading about a particular supplement and want a way to judge it, the same method is in another article: [[article:pcos-and-food-judging-a-claim|how to judge a claim]].
EN,
            ],

            'citations' => [
                [
                    'organisation' => [
                        'ar' => 'الأكاديمية الأمريكية للتغذية وأخصائيو التغذية في كندا والكلية الأمريكية للطب الرياضي',
                        'en' => 'Academy of Nutrition and Dietetics, Dietitians of Canada, and American College of Sports Medicine',
                    ],
                    'title' => [
                        'ar' => 'بيان موقف مشترك: التغذية والأداء الرياضي',
                        'en' => 'Joint Position Statement: Nutrition and Athletic Performance',
                    ],
                    'year' => 2016,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "treats athletes\' protein requirement as higher than the general '
                        .'requirement for inactive adults". Confident such a joint statement exists between '
                        .'these three bodies; confirm the year and the exact list of issuing organisations. '
                        .'NO FIGURE is quoted in the article — if a range is added later it must come from '
                        .'this document rather than from memory.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الجمعية الدولية للتغذية الرياضية (ISSN)',
                        'en' => 'International Society of Sports Nutrition (ISSN)',
                    ],
                    'title' => [
                        'ar' => 'بيان موقف: البروتين والتمرين',
                        'en' => 'Position stand: Protein and exercise',
                    ],
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the same claim from a second body, and underpins the anabolic-window '
                        .'section — the ISSN position stand addresses timing directly. No year given because '
                        .'the position stands are periodically updated. Confirm the current version and '
                        .'check specifically that it supports "total daily protein matters more than precise '
                        .'post-exercise timing", which is the article\'s claim.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الجمعية الدولية للتغذية الرياضية (ISSN)',
                        'en' => 'International Society of Sports Nutrition (ISSN)',
                    ],
                    'title' => [
                        'ar' => 'بيان موقف: الكرياتين في التمرين والرياضة',
                        'en' => 'Position stand: Creatine supplementation in exercise and sport',
                    ],
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "creatine is among the most studied supplements in sport and has a '
                        .'position stand devoted to it". Confirm the current version and year.',
                ],
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => [
                        'ar' => 'إرشادات منظمة الصحة العالمية بشأن النشاط البدني والسلوك الخامل',
                        'en' => 'WHO guidelines on physical activity and sedentary behaviour',
                    ],
                    'year' => 2020,
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports "sets out what adults should be doing weekly, including aerobic '
                        .'activity and muscle-strengthening exercise". The article deliberately gives no '
                        .'weekly minutes — add them from the guideline if wanted.',
                ],
                [
                    'organisation' => [
                        'ar' => 'أبحاث منشورة عن تلوث المكملات الرياضية بمواد غير معلنة',
                        'en' => 'Published research on undeclared substances in sports supplements',
                    ],
                    'title' => [
                        'ar' => 'دراسات تحليل محتوى المكملات الرياضية المتاحة تجاريًا',
                        'en' => 'Analytical studies of the contents of commercially available sports supplements',
                    ],
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'THE WEAKEST CITATION IN THE ARTICLE AND THE STRONGEST CLAIM — this needs a '
                        .'specific named source before publication, not a description of a literature. The '
                        .'finding that a proportion of sports supplements contain undeclared stimulants and '
                        .'steroid derivatives is well established and has been documented repeatedly, but '
                        .'this entry names no single document. Replace it with one identifiable study or '
                        .'agency report (an anti-doping body publication would be ideal), or cut the section '
                        .'to what a named source will carry.',
                ],
            ],
        ];
    }
}
