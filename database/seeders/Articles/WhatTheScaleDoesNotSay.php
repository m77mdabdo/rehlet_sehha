<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * THE ONE ARTICLE ON THIS SITE THAT PRINTS NUMBERS, AND WHY THAT IS ALLOWED.
 *
 * The daily-weighing section shows three readings — 70, 70.7, 69.8 — as a
 * worked example of what ordinary fluctuation looks like written down.
 *
 * The site's no-numbers rule is not a ban on digits. It bans a QUANTITY AIMED
 * AT THE READER: a target she can measure herself against and fail, set by
 * somebody who has not seen her. That is why the plate builder refuses to show
 * a calorie, why no article states grams per kilogram, and why the postpartum
 * screening interval is left for the clinician.
 *
 * These three are the opposite of a target. They belong to nobody, they are
 * not an instruction, and the entire point of printing them is to show that a
 * number moving by this much means nothing — which is an argument that cannot
 * be made without showing the number moving. Removing them would leave the
 * section asserting the very thing it exists to demonstrate.
 *
 * ArticleStandardTest enforces the real rule mechanically: a digit adjacent to
 * a unit. These are deliberately written without one, in both languages, so
 * the check stays strict rather than being widened to accommodate them.
 *
 * ---------------------------------------------------------------------------
 *
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

وفي جملة لد. رنا بتحدد المسألة كلها: «ممكن المريضة تخس كيلو، لكن تفضل متوترة طول الوقت وخايفة من الأكل». الرقم ممكن يتحرك في الاتجاه الصح والحاجة اللي إنتِ عايزاها فعلًا تكون بعيدة.

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

د. رنا بتجاوب على ده كده:

«لو الميزان كل يوم هيخلي مزاجك متعلق بالرقم، يبقى الأفضل نقلل عدد مرات الوزن. الوزن الطبيعي بيطلع وينزل من يوم للتاني، والفرق ممكن يوصل لكيلو أو أكتر — وده مش معناه إنك خسرتي أو زدتي دهون في يوم واحد.

والمشكلة إن المريضة لما تشوف الرقم طلع شوية، ممكن تقلل أكلها زيادة، أو تحبط، أو تقول (أنا بعمل كل ده على الفاضي).

عشان كده بفضّل الوزن مرة واحدة أسبوعيًا، في نفس الظروف تقريبًا: الصبح، بعد دخول الحمام، وقبل الأكل.

ولو حد بيحب يوزن يوميًا ومش بيتأثر نفسيًا بالأرقام، بنبص وقتها على متوسط الأسبوع، مش على رقم كل يوم».

والشكل ده بيبان بسرعة لما تكتبي القرايات جنب بعض: ٧٠ النهارده، ٧٠٫٧ بكرة، ٦٩٫٨ بعده. التلات أرقام دول مش تلات نتايج مختلفة — ده رقم واحد بيتحرك حوالين نفسه.

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

د. رنا بتقول: «الوزن عندي مؤشر واحد بس، مش هو التقييم كله».

واللي بيتابع معاه:

المقاسات، وخصوصًا محيط البطن والوسط، والهدوم بقت عاملة إزاي.

الجوع والشبع: بقت تقدر تتحكم في أكلها أكتر، ولا لسه في نوبات جوع شديدة؟

الطاقة خلال اليوم، والنوم، والهضم، والحركة، ومدى سهولة الالتزام.

وحاجة مهمة جدًا: علاقتها بالأكل نفسها. «هل بقت تعرف تاكل وجبة بتحبها من غير إحساس بالذنب؟ لو خرجت عن الخطة بتعرف ترجع تاني؟ اختياراتها بقت أهدى وأوعى؟»

والسبب اللي بيخلي البند الأخير ده مهم بالذات: «ممكن المريضة تخس كيلو، لكن تفضل متوترة طول الوقت وخايفة من الأكل — وده مش النجاح اللي إحنا عايزينه. التحسن الحقيقي إن جسمها يتحسن، وفي نفس الوقت حياتها وعلاقتها بالأكل تتحسن».

وده اللي بتقوله د. رنا لمريضة ملتزمة والرقم مش بيتحرك:

«الميزان مش دايمًا بيحكي كل اللي بيحصل في جسمك. ممكن تكوني ملتزمة جدًا وفعلًا جسمك بيتغيّر، بس الرقم ما اتحركش الأسبوع ده.

الوزن بيتأثر بحاجات كتير غير الدهون: احتباس السوائل، أكل فيه ملح أكتر من المعتاد، قلة النوم، التوتر، الإمساك، أو وقت الدورة الشهرية. يعني لو وزنك ثابت أسبوع، ده مش معناه إنك ما خسرتيش دهون، ومش معناه إن الخطة مش شغالة.

أنا ببص على الصورة كاملة، مش على رقم أسبوع واحد. لو التزامك كويس، ومقاساتك بتتحسن، وهدومك أريح، ونمط أكلك أحسن — فإحنا ماشيين في الاتجاه الصح.

اللي يهمني الاتجاه على مدار أسابيع، مش إن الميزان لازم ينزل كل مرة تطلعي عليه».

## اللي يستاهل تفتكريه

الميزان أداة، مش حكم. وأي أداة بتبقى مفيدة لما تتقري صح ومؤذية لما تتقري غلط.

القراية الواحدة فيها مية وملح وأكل ودورة شهرية وتوقيت، وتركيب الجسم مش بيظهر فيها أصلًا. والحاجات اللي بتتقاس بطريقة تانية — محيط الخصر، الطاقة، التحاليل، الالتزام — هي اللي بتديكي حاجة تعمليها.

لو الرقم بقى أول حاجة بتحدد مزاجك الصبح، دي مش مشكلة في إرادتك — دي مشكلة في إن الأداة بقت بتستخدمك. والحل بيبدأ بكلام مع حد شايف حالتك: تقدري تشوفي [[specialty:weight-management|متابعة الوزن]] أو [[booking|تحجزي موعد]].

ولو حابة تفهمي ليه الرقم بيتحرك بسرعة في الأول وبعدين يهدى، ده موضوع مقال [[article:why-we-quit-in-week-three|ليه بنسيب النظام في الأسبوع التالت]].

والجملة اللي تستاهل تفضل معاكي: الميزان أداة نستخدمها، مش حكم على نجاحك أو فشلك.
AR,

                'en' => <<<'EN'
A scale gives one number, and that number gets read as an answer. It is not an answer. It measures one thing — total body mass — and a great deal inside it has nothing to do with the question you are actually asking.

This article is about what is inside that number, and about what gets measured another way.

And there is a sentence of Dr Rana's that settles the whole question: "A patient can lose a kilo and still be anxious all the time and frightened of food." The number can move in the right direction while the thing you actually wanted stays out of reach.

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

Dr Rana answers it like this:

"If weighing every day is going to leave your mood attached to the number, then we weigh less often. Normal weight goes up and down from one day to the next, and the difference can be a kilo or more — and that does not mean you lost or gained fat in a day.

The problem is that when a patient sees the number has gone up a little, she may cut her food back too far, or get discouraged, or decide 'I'm doing all this for nothing.'

So I prefer once a week, in roughly the same conditions: in the morning, after the bathroom, before eating.

And if somebody likes weighing daily and isn't affected by the numbers, then we look at the week's average rather than at each day's figure."

The shape shows itself as soon as you write the readings next to each other: 70 today, 70.7 tomorrow, 69.8 the day after. Those are not three different results — it is one number moving around itself.

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

Dr Rana: "Weight, for me, is one indicator. It is not the whole assessment."

What gets followed alongside it:

Measurements, particularly around the abdomen and waist, and how clothes are fitting.

Hunger and fullness: is she more in control of her eating, or are there still episodes of severe hunger?

Energy through the day, sleep, digestion, movement, and how easy the plan is to keep to.

And one that matters a great deal: her relationship with food itself. "Can she eat a meal she enjoys without guilt? If she goes off the plan, does she know how to come back? Are her choices calmer and more considered?"

And the reason that last one matters so much: "A patient can lose a kilo and still be anxious all the time and frightened of food — and that is not the success we are after. Real improvement is her body improving and, at the same time, her life and her relationship with food improving." 

This is what Dr Rana says to a patient who is sticking to the plan while the number refuses to move:

"The scale doesn't always tell you everything that's happening in your body. You can be doing everything right, and your body can genuinely be changing, and the number still hasn't moved this week.

Weight is affected by a great many things besides fat: fluid retention, a meal with more salt than usual, poor sleep, stress, constipation, or where you are in your cycle. So if your weight is level for a week, that does not mean you have not lost fat, and it does not mean the plan isn't working.

I look at the whole picture, not at one week's number. If you are keeping to it, and your measurements are improving, and your clothes are more comfortable, and the way you eat is better — then we are going in the right direction.

What I care about is the direction over weeks, not that the scale has to fall every time you step on it." 

## Worth remembering

A scale is a tool, not a verdict. Any tool is useful read correctly and harmful read wrongly.

A single reading contains water, salt, food, a menstrual cycle and a time of day — and body composition does not appear in it at all. The things measured another way — waist, energy, lab work, how the plan is going — are the ones that give you something to act on.

If the number has become the first thing that sets your mood in the morning, that is not a problem with your willpower — it is a problem with a tool that has started using you. The answer begins with a conversation with somebody who has seen your case: you can look at [[specialty:weight-management|weight management follow-up]] or [[booking|book an appointment]].

And if you want to understand why the number falls quickly at first and then settles, that is the subject of [[article:why-we-quit-in-week-three|Why we quit in week three]].

And the sentence worth keeping: a scale is a tool we use. It is not a verdict on whether you are succeeding or failing.
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
