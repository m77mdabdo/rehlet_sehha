<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * The article most likely to be read at eleven at night by somebody who has
 * just lost an argument with a four-year-old.
 *
 * Two things do most of the work. The first is physiological: growth velocity
 * slows sharply after the first year, so a toddler eating less than she did at
 * eleven months is behaving normally, and almost nobody has been told this.
 * The second is the milk section — excess cow's milk is the commonest reversible
 * contributor to iron deficiency in this age group here, and it is being given
 * precisely BECAUSE the child will not eat, which makes it the one piece of
 * information in the article that changes behaviour immediately.
 *
 * The red-flags section is not optional. An article that reassures without
 * saying what would not be reassuring is a liability.
 *
 * ---------------------------------------------------------------------------
 *
 * HER RED FLAGS ARE IN A CALLOUT, AND THAT IS THE POINT OF THE SECTION. This
 * article reassures for most of its length — that is what it is for — and the
 * risk that creates is specific: a frightened parent skims a reassuring piece,
 * finds the reassurance, and stops. A list set in the same type as the eleven
 * calming paragraphs above it is a list that gets skimmed past, and the items
 * on it are the ones where skimming past has a cost.
 *
 * The article's own additions — chronic diarrhoea, recurrent abdominal pain,
 * blood in the stool, persistent pallor — follow the callout as prose rather
 * than being merged into it, so that what is inside the box is hers and is
 * exactly what she said.
 *
 * All three prompts on this article were answered.
 */
class TheChildWhoWillNotEat extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'the-child-who-will-not-eat',
            'category' => 'child-nutrition',
            'tags' => ['family', 'myths'],
            'cover' => 'child-hands-untouched-plate',

            'title' => [
                'ar' => 'الطفل اللي مش بياكل',
                'en' => 'The child who will not eat',
            ],

            'excerpt' => [
                'ar' => 'الأكل بقى معركة يومية. المقال ده عن اللي طبيعي في السن ده، واللي مش طبيعي، والفرق بينهم.',
                'en' => 'Mealtimes have become a daily battle. This is about what is normal at this age, what is not, and how to tell them apart.',
            ],

            'body' => [
                'ar' => <<<'AR'
الطبق قدامه من ساعة. الأم بتلاحقه في الأوضة بالمعلقة. الأب بيقول «سيبيه هياكل لما يجوع». الجدة بتقول «ده ضعيف، شوفوله فاتح شهية».

الموقف ده بيتكرر في بيوت كتير، وبيسبب قلق حقيقي. المقال ده عن الفرق بين اللي طبيعي في السن ده واللي محتاج يتشاف.

## أول سؤال: «مش بياكل» ولا «مش بياكل اللي إحنا عايزينه»؟

في فرق كبير بين الاتنين، والفرق ده بيغيّر كل حاجة.

ود. رنا بتقول إنها مش بتبدأ بحل: «لما أم تدخل وتقولي (ابني مش بياكل خالص)، أول حاجة مش بقولها (جربي تديه كذا). بسألها الأول».

والأسئلة اللي بتبدأ بيها:

يعني إيه مش بياكل؟ بياكل كام وجبة؟

بياكل إيه فعلًا خلال اليوم؟

بيشرب لبن أو عصير بكميات كبيرة؟

بياكل سناكس بين الوجبات؟

بيرفض كل الأكل ولا أصناف معينة؟

المشكلة بدأت إمتى؟

ووزنه وطوله ونموه ماشيين إزاي؟

والسبب إن الإجابات دي بتودّي لمكانين مختلفين تمامًا: «أوقات الطفل فعلًا عنده مشكلة محتاجة تقييم، وأوقات تانية بنكتشف إنه بياكل، لكن الكمية أقل من توقعات الأسرة».

الطفل اللي بياكل خمس أصناف بس وبيرفض الباقي مختلف عن الطفل اللي كمية أكله كلها قليلة. والأول ده اسمه انتقائية في الأكل، وهو شائع جدًا وغالبًا مرحلة. والتاني موضوع تاني بيتقيّم إكلينيكيًا.

خطوة أولى مفيدة: اكتبي فعلًا كل حاجة الطفل بياكلها خلال يومين — بما فيها اللبن والعصير والبسكوت واللقمة اللي أخدها وهو ماشي. الصورة اللي بتطلع غالبًا بتبقى مختلفة عن الانطباع.

## المؤشر هو النمو، مش الطبق

دي أهم نقطة في المقال.

منظمة الصحة العالمية عندها معايير نمو للأطفال بتتستخدم في العالم كله، والفكرة الأساسية فيها إن الطفل بيتابع على منحنى — مش على نقطة واحدة. الطفل اللي ماشي على منحناه بشكل ثابت بيكبر كويس حتى لو الأكل شكله قليل في نظر أهله.

والمعهد الوطني البريطاني (NICE) عنده دليل مخصص لتعثّر النمو عند الأطفال، وبيتعامل مع الموضوع من زاوية المنحنى والتغيّر عبر الوقت، مش من زاوية كمية الأكل في وجبة.

يعني السؤال الصح مش «أكل قد إيه النهاردة؟» — السؤال «هو ماشي على منحناه ولا لأ؟». والإجابة دي عند طبيب الأطفال مع كارت المتابعة، مش على السفرة.

## الفسيولوجي: ليه الشهية بتقل بعد السنة

دي المعلومة اللي بتحل نص القلق، وقليل جدًا اللي بيعرفها.

معدل النمو في السنة الأولى سريع جدًا — الطفل بيزيد وزنه زيادة كبيرة نسبيًا في وقت قصير. وبعد السنة الأولى معدل النمو بيقل بشكل واضح ويستمر أبطأ لسنين.

والاحتياج من الطاقة بيتبع معدل النمو. يعني الطفل اللي كان بياكل باستمتاع في الشهر العاشر وبقى بيرفض في الشهر الثامن عشر مش «اتغيّر» ولا «اتدلع» — احتياجه الفعلي قلّ نسبيًا.

الأهل بيقارنوا باللي كان، والمقارنة دي بتخلي حاجة طبيعية تبان مشكلة.

## رهاب الجديد: مرحلة ليها اسم

في مرحلة بيرفض فيها الأطفال الأكل الجديد بشكل شبه تلقائي، وبتظهر غالبًا بعد السنة وبتكمل لسنين في مرحلة ما قبل المدرسة.

دي مرحلة نمائية معروفة، ومفهومة كسلوك وقائي: الطفل اللي بقى بيتحرك لوحده بيبقى أكثر حذرًا تجاه أي حاجة جديدة بتتحط في بقه.

الحاجة المهمة: الرفض في السن ده مش رأي نهائي في الصنف. هو رد فعل أولي، وبيتغير مع التعرض.

## اللي الأبحاث بتقوله عن التعرض المتكرر

في ملاحظة متكررة في أبحاث سلوك الأكل عند الأطفال: قبول الصنف الجديد بيزيد مع التعرض المتكرر ليه بشكل محايد — يعني يتقدّم على السفرة من غير ضغط ومن غير مكافأة ومن غير تعليق.

وعدد مرات التعرض اللي بتحتاجه غالبًا أكبر بكتير من اللي الأهل بيتوقعوه. معظم الأهل بيجربوا الصنف مرتين أو تلاتة وبيستنتجوا إن الطفل «مش بيحبه»، والاستنتاج ده بيوقف التعرض، فبيثبّت الرفض.

والضغط بيشتغل عكسي. الإجبار على الأكل بيربط الصنف ده بتجربة سلبية، وده بيقلل قبوله على المدى الطويل بدل ما يزوّده.

ود. رنا بتلخص دور الأم في الموقف ده كله في جملتين: «دورك مش إنك تجبريه يخلص الطبق. دورك إنك توفري أكل مناسب ومتوازن في مواعيد منتظمة وبيئة هادية، وتستمري في عرض أطعمة متنوعة من غير ضغط».

والنتيجة مش فورية وده جزء من التصميم: «والطفل تدريجيًا يتعلم يسمع إشارات الجوع والشبع عنده».

## تقسيم المسؤولية: مين بيقرر إيه

في مبدأ في تغذية الأطفال بيتقال في إرشادات كتير، ومنها ما بتنشره الأكاديمية الأمريكية لطب الأطفال، وبيتلخص كالآتي:

الأهل بيقرروا الأكل إيه، ويتقدّم إمتى، وفين. والطفل بيقرر ياكل ولا لأ، وقد إيه.

المبدأ ده بيبان بسيط وهو بيحل معظم صراع السفرة، لأنه بيوقف المطاردة بالمعلقة من ناحية، وبيوقف تقديم بدائل حسب الطلب من الناحية التانية. اللي بيحصل غالبًا إن الحدود دي بتتبدل: الأهل بيحاولوا يتحكموا في الكمية، والطفل بيتحكم في نوع الأكل عن طريق الرفض.

## اللبن: الحاجة اللي بتبان حل وهي جزء من المشكلة

دي نقطة عملية بتغيّر حاجات كتير بسرعة.

لما الطفل يرفض الأكل، رد الفعل الطبيعي إن الأهل يعوّضوا باللبن — «على الأقل شرب لبن». والمشكلة إن اللبن بكميات كبيرة بيعمل حاجتين مع بعض: بيملا معدة صغيرة فبيقلل الأكل الصلب، واللبن البقري فقير في الحديد.

يعني الحل المؤقت بيغذي المشكلة: أكل صلب أقل معناه حديد أقل، والحديد المنخفض بيقلل الشهية بنفسه.

الأكاديمية الأمريكية لطب الأطفال بتتكلم عن نقص الحديد عند الأطفال الصغيرين وعن علاقته باستهلاك كميات كبيرة من اللبن البقري في السن ده.

والشاي بيعمل نفس الحاجة من ناحية تانية: بيقلل امتصاص الحديد، وبيتقدم للأطفال في بيوت مصرية كتير.

## خرافتين متعاكستين

«سيبيه لما يجوع هياكل». دي فيها جزء صح — الضغط مش بينفع — بس الاستنتاج غلط. سحب الأكل كعقاب أو استخدام الجوع كأداة بيحوّل الأكل لساحة صراع، والطفل اللي بيتعلم إن الأكل موضوع سلطة بيقاوم أكتر.

«طاردي وراه وحطي في بقه». دي بتشتري لقمة النهاردة بتكلفة طويلة المدى: الأكل بيرتبط بالإجبار، وبيقل قبوله.

الاتنين بيشتركوا في حاجة: الاتنين بيخلوا الأكل موضوع تفاوض بين طرفين. والحل مش في وسط بينهم — الحل إن الأكل يرجع يبقى روتين عادي.

ود. رنا بتقول إن ده أكتر خطأ بتشوفه: «إن وقت الأكل يتحول لمعركة». والأشكال اللي بتاخدها المعركة دي مألوفة: «كُل معلقة كمان»، «لو خلصت الطبق هديك حلو»، نجري وراه بالمعلقة، نشغل الشاشة عشان ياكل من غير ما يحس، أو نفضل نقارن بينه وبين أخوه.

والتكلفة مش في الوجبة دي: «كل الضغط ده ممكن يخلي الطفل يربط الأكل بالتوتر بدل الجوع والشبع».

## «فاتح الشهية» — الحاجة اللي بتتشترى من الصيدلية

دي نقطة عملية لأنها بتحصل كتير: الأهل بيروحوا الصيدلية ويطلبوا حاجة «تفتح نفسه».

المنتجات دي بتختلف — بعضها فيتامينات، وبعضها فيه مواد ليها تأثير دوائي حقيقي. والمشكلة مش إنها بتنفع ولا لأ؛ المشكلة إنها بتتاخد قبل ما حد يسأل السؤال الأول: هل في سبب للشهية القليلة أصلًا؟

لأن الشهية القليلة ممكن تكون طبيعية في السن ده، وممكن تكون عرض — لنقص حديد مثلًا، أو لمشكلة تانية. والمنتج اللي بيزوّد الأكل من غير ما يعالج السبب بيخفي العرض ويأخر التقييم.

وأي حاجة بتتاخد بانتظام تتقال لطبيب الأطفال، حتى لو اتشرت من غير روشتة.

## الروتين نفسه بيعمل شغل

في حاجات بسيطة في شكل الوجبة بتفرق أكتر مما الناس بتتوقع، ومش محتاجة أي منتج:

الأكل في ميعاد تقريبًا ثابت، والوجبة ليها بداية ونهاية بدل ما تفضل مفتوحة ساعة.

الطفل قاعد مع الناس على نفس السفرة وبياكل من نفس الأكل، حتى لو كمية صغيرة.

التليفون والتلفزيون مقفولين وقت الأكل، لأن الانتباه للأكل جزء من الإحساس بالشبع.

وأصناف مألوفة على السفرة جنب أي صنف جديد، عشان الطفل يلاقي حاجة يعرفها.

والأهم: مفيش تعليق على الأكل. لا مدح ولا لوم ولا تفاوض. الوجبة اللي مفيهاش كلام عن الأكل بتعدي أهدى بكتير.

## في المطبخ المصري

الأكل بيتقدم للطفل في طبق منفصل غالبًا، وده بيقلل حاجة بتفرق: إن الطفل يشوف اللي حواليه بياكلوا نفس الأكل. التعرض بيحصل بالمشاهدة زي ما بيحصل بالذوق.

والأكل البيتي فيه اختيارات كويسة للسن ده: العدس والفول المهروس، البيض، الأرز باللبن، الخضار المسلوق، البطاطس، الجبنة القريش، الزبادي، اللحمة المفرومة في المسقعة أو الشوربة، والفاكهة الموسمية.

والنقرشة بين الوجبات هي المشكلة الصامتة: الشيبسي والبسكوت والعصاير المحلّاة بتملا الطفل بين الوجبات فبييجي على السفرة مش جعان، وبعدين الرفض بيتقري على إنه «مش بياكل».

والعزومات والزيارات بيبقى فيها ضغط من الكبار — «كُل يا حبيبي عشان خاطري». الضغط ده بحب، وبيعمل نفس أثر أي ضغط تاني.

## حاجات صغيرة بتقلل الشهية من غير ما ناخد بالنا

في حاجات في اليوم بتشتغل ضد الوجبة من غير ما حد يربط بينهم. د. رنا بتعدّدها كده:

سناكس طول اليوم.

مشروبات قبل الوجبة.

كميات كبيرة جدًا في الطبق.

أو إننا أول ما يرفض الوجبة نجري نعمله الأكلة الوحيدة اللي بيحبها.

والصفة المشتركة بينهم إنهم كلهم بيتعملوا بحسن نية، وكلهم بيوصلوا الطفل للسفرة مش جعان.

## علامات مش المفروض تتطمني عليها

المقال ده بيطمّن في أغلب الحالات، وعشان كده لازم يقول اللي مش بيطمّن.

ودي العلامات اللي د. رنا بتقول عندها إن ده مبقاش مجرد انتقائية:

> لو في:
> - فقدان وزن أو ضعف في النمو
> - صعوبة واضحة في المضغ أو البلع
> - قيء متكرر
> - ألم مع الأكل
> - اختناق أو كحة متكررة أثناء الأكل
> - قائمة الأطعمة المقبولة بتضيق جدًا
> - أو الأهل حاسين إن في تغير كبير ومستمر
> هنا الموضوع محتاج تقييم متخصص، ومش مجرد «هيكبر وياكل».

والقايمة دي مش شاملة كل حاجة. إسهال مزمن، أو ألم بطن متكرر، أو دم في البراز، أو شحوب وخمول مستمر — كلها كمان بتتشاف عند طبيب أطفال، مش بتتعالج بتعديل في السفرة.

## اللي يستاهل تفتكريه

المؤشر هو النمو على المنحنى، مش كمية وجبة. والشهية بتقل بعد السنة لأن معدل النمو بيقل — دي فسيولوجيا مش دلع.

رفض الجديد مرحلة ليها اسم، والقبول بيزيد بالتعرض المحايد المتكرر، والضغط بيقلله. والأهل بيقرروا إيه وإمتى وفين، والطفل بيقرر ياكل ولا لأ وقد إيه.

واللبن بكميات كبيرة بيبان حل وهو بيقلل الأكل الصلب والحديد مع بعض.

ولو في علامة من علامات القايمة اللي فوق، دي مش انتقائية — دي زيارة دكتور.

وزي ما بتقول د. رنا: «الهدف مش إن الطفل (يخلص طبقه). الهدف إننا نبني علاقة صحية مع الأكل، وفي نفس الوقت نتابع نموه ونتأكد إنه بياخد احتياجاته. لأن الطفل اللي مش بياكل محتاج الأول نفهم: هل فعلًا مش بياكل كفاية، ولا إحنا منتظرين منه ياكل أكتر من احتياجه؟».

لو الموضوع مستمر وعايزة تقييم: [[specialty:child-nutrition|تغذية الأطفال]] أو [[booking|احجزي موعد]].

وفي مقال عن إدخال الأكل من الأول: [[article:feeding-and-eating-recurring-questions|أسئلة بتتكرر عن الرضاعة وأكل الطفل]].
AR,

                'en' => <<<'EN'
The plate has been in front of him for an hour. His mother is following him round the room with a spoon. His father says "leave him, he will eat when he is hungry". His grandmother says he looks thin and needs something to open his appetite.

This happens in a great many households and causes genuine anxiety. This article is about the difference between what is normal at this age and what needs to be seen.

## First question: "will not eat" or "will not eat what we want"?

There is a large difference between the two, and it changes everything.

Dr Rana says she does not begin with a solution: "When a mother comes in and tells me 'my son doesn't eat at all', the first thing I say is not 'try giving him this'. I ask her first."

The questions she starts with:

What does "not eating" mean? How many meals does he eat?

What does he actually eat through the day?

Is he drinking milk or juice in large amounts?

Is he eating snacks between meals?

Does he refuse all food, or particular things?

When did the problem start?

And how are his weight, his height and his growth doing?

The reason is that those answers lead to two completely different places: "Sometimes the child genuinely has a problem that needs assessment, and other times we discover that he is eating — but the amount is less than the family expected."

A child who eats five foods and refuses the rest is not the same as a child whose total intake is small. The first is food selectivity, which is very common and usually a phase. The second is a different matter and is assessed clinically.

A useful first step: actually write down everything the child eats over two days — including milk, juice, biscuits, and the mouthful taken in passing. The picture that emerges is usually different from the impression.

## The indicator is growth, not the plate

This is the most important point in the article.

The World Health Organization has child growth standards used worldwide, and their central idea is that a child follows a curve — not a single point. A child tracking steadily along her curve is growing well even if the eating looks sparse to her family.

And NICE has guidance devoted to faltering growth in children, which approaches the question through the curve and change over time rather than through the amount eaten at a meal.

So the right question is not "how much did he eat today?" — it is "is he tracking his curve?". And that answer sits with the paediatrician and the growth chart, not at the dinner table.

## The physiology: why appetite falls after the first year

This is the piece of information that resolves half the anxiety, and very few people have been told it.

The rate of growth in the first year is very fast — a baby gains a proportionally large amount of weight in a short time. After the first year the rate of growth falls markedly and stays slower for years.

And energy requirement follows the rate of growth. So a child who ate with enjoyment at ten months and refuses at eighteen has not "changed" or become spoiled — her actual requirement has fallen relative to what it was.

Parents compare with what used to be, and that comparison makes something normal look like a problem.

## Food neophobia: a phase with a name

There is a stage at which children refuse new food almost automatically, appearing usually after the first year and continuing for years through the pre-school period.

This is a recognised developmental stage, understandable as protective behaviour: a child who has become mobile is more cautious about anything new being put into her mouth.

The important part: refusal at this age is not a final verdict on the food. It is an initial reaction, and it changes with exposure.

## What research says about repeated exposure

There is a consistent observation in research on children's eating behaviour: acceptance of a new food increases with repeated neutral exposure — offered at the table without pressure, without reward, and without comment.

And the number of exposures needed is usually far greater than parents expect. Most parents try a food two or three times and conclude the child does not like it, and that conclusion stops the exposure, which fixes the refusal in place.

Pressure works in reverse. Forcing food associates it with an unpleasant experience, which reduces acceptance over the long run rather than increasing it.

Dr Rana puts the mother's part in all of this into two sentences: "Your job is not to make him finish the plate. Your job is to provide suitable, balanced food at regular times in a calm environment, and to keep offering a variety of foods without pressure."

And the result is not immediate, which is part of the design: "And gradually the child learns to hear his own hunger and fullness cues."

## The division of responsibility: who decides what

There is a principle in child feeding stated in a good deal of guidance, including material published by the American Academy of Pediatrics, and it comes to this:

Parents decide what food is offered, when, and where. The child decides whether to eat, and how much.

The principle looks simple and resolves most of the conflict at the table, because it stops the pursuit with a spoon on one side and stops serving alternatives on demand on the other. What usually happens is that these boundaries get swapped: parents try to control the amount, and the child controls the kind of food by refusing.

## Milk: the thing that looks like a solution and is part of the problem

This is a practical point that changes a great deal quickly.

When a child refuses food, the natural reaction is for parents to compensate with milk — at least he drank some milk. The difficulty is that milk in large amounts does two things at once: it fills a small stomach and so reduces solid food, and cow's milk is poor in iron.

So the temporary solution feeds the problem: less solid food means less iron, and low iron reduces appetite in its own right.

The American Academy of Pediatrics discusses iron deficiency in young children and its relationship to consuming large volumes of cow's milk at this age.

Tea does the same thing from another direction: it reduces iron absorption, and it is given to children in a great many Egyptian households.

## Two opposite myths

"Leave him, he will eat when he is hungry." There is something right in this — pressure does not work — but the conclusion is wrong. Withdrawing food as a punishment, or using hunger as a tool, turns eating into a battlefield, and a child who learns that food is a matter of authority resists harder.

"Chase him and put it in his mouth." This buys today's mouthful at a long-term cost: food becomes associated with coercion, and acceptance falls.

The two share something: both make eating a negotiation between two parties. And the answer is not a midpoint between them — it is for eating to become an ordinary routine again.

Dr Rana says this is the commonest mistake she sees: "mealtimes turning into a battle." The forms that battle takes are familiar: "one more spoonful", "if you finish your plate I'll give you a sweet", chasing him round with the spoon, putting a screen on so that he eats without noticing, or comparing him with his brother.

And the cost is not paid at that meal: "All that pressure can make the child associate food with tension instead of with hunger and fullness."

## "Something to open his appetite" — the thing bought at the pharmacy

A practical point, because it happens constantly: parents go to the pharmacy and ask for something to open the child's appetite.

These products vary — some are vitamins, some contain substances with genuine pharmacological effects. And the problem is not whether they work; it is that they get taken before anybody asks the first question: is there a reason for the poor appetite at all?

Because poor appetite may be normal at this age, and it may be a symptom — of iron deficiency, or of something else. A product that increases eating without addressing the cause conceals the symptom and delays assessment.

And anything taken regularly should be mentioned to the paediatrician, even if it was bought without a prescription.

## The routine itself does work

There are simple things about the shape of a meal that matter more than people expect, and require no product at all:

Eating at roughly consistent times, with the meal having a beginning and an end rather than staying open for an hour.

The child sitting with everybody at the same table, eating the same food, even a small amount of it.

Phones and television off during the meal, because attention to food is part of registering fullness.

Familiar foods on the table alongside anything new, so the child finds something she recognises.

And most importantly: no commentary on the eating. No praise, no blame, no negotiation. A meal with no talk about food goes far more calmly.

## In an Egyptian kitchen

Food is usually served to the child on a separate plate, which removes something that matters: the child seeing everybody around her eating the same food. Exposure happens by watching as much as by tasting.

Home cooking offers good options for this age: mashed lentils and foul, eggs, rice pudding, boiled vegetables, potato, areesh cheese, yoghurt, minced meat in moussaka or soup, and seasonal fruit.

Snacking between meals is the silent problem: crisps, biscuits and sweetened juices fill a child between meals so she arrives at the table not hungry, and the refusal then gets read as "he does not eat".

And gatherings and visits bring pressure from adults — eat, darling, for my sake. That pressure comes from affection, and has the same effect as any other pressure.

## Small things that reduce appetite without anybody noticing

There are things in a day that work against the meal without anybody connecting the two. Dr Rana lists them:

Snacks throughout the day.

Drinks before the meal.

Very large amounts on the plate.

Or the moment he refuses the meal, rushing to make him the one dish he likes.

What they have in common is that all of them are done with the best of intentions, and all of them bring the child to the table not hungry.

## Signs that should not be reassured away

This article is reassuring in most situations, which is exactly why it has to say what is not reassuring.

These are the signs at which Dr Rana says this is no longer simple selectivity:

> If there is:
> - weight loss or faltering growth
> - clear difficulty chewing or swallowing
> - repeated vomiting
> - pain with eating
> - choking or repeated coughing while eating
> - a very sharp narrowing of the list of accepted foods
> - or a sense in the family of a large and continuing change
> then this needs specialist assessment, and not "he'll grow out of it".

And that list is not exhaustive. Chronic diarrhoea, recurrent abdominal pain, blood in the stool, or persistent pallor and lethargy are also seen by a paediatrician, not managed by adjusting the dinner table.

## Worth remembering

The indicator is growth along the curve, not the size of a meal. And appetite falls after the first year because the rate of growth falls — that is physiology, not spoiling.

Refusing new things is a phase with a name; acceptance increases with repeated neutral exposure and decreases with pressure. Parents decide what, when and where; the child decides whether and how much.

And milk in large volumes looks like a solution while reducing both solid food and iron at once.

If any of the signs on the list above is present, this is not selectivity — it is a doctor's appointment.

As Dr Rana puts it: "The goal is not for the child to 'finish his plate'. The goal is to build a healthy relationship with food, and at the same time to follow his growth and make sure he is getting what he needs. Because with a child who is not eating, the first thing we need to understand is this: is he genuinely not eating enough, or are we expecting him to eat more than he needs?"

If it is continuing and you want an assessment: [[specialty:child-nutrition|child nutrition]] or [[booking|book an appointment]].

And there is an article on introducing food in the first place: [[article:feeding-and-eating-recurring-questions|questions that keep coming up about feeding]].
EN,
            ],

            'citations' => [
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => ['ar' => 'معايير نمو الطفل', 'en' => 'Child Growth Standards'],
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports "a child follows a curve, not a single point" as the basis for '
                        .'assessing growth. No year given — the standards are a maintained resource. Confirm '
                        .'the current reference at verification.',
                ],
                [
                    'organisation' => [
                        'ar' => 'المعهد الوطني للصحة وجودة الرعاية (NICE)',
                        'en' => 'National Institute for Health and Care Excellence (NICE)',
                    ],
                    'title' => [
                        'ar' => 'تعثّر النمو: التعرّف والتعامل',
                        'en' => 'Faltering growth: recognition and management',
                    ],
                    'year' => 2017,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "approaches the question through the curve and change over time '
                        .'rather than the amount eaten at a meal", and underpins the red-flag list. Confirm '
                        .'the year, and check the guideline\'s own list of concerning features against the '
                        .'article\'s — the red-flag section is the part where an omission would matter most.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الأكاديمية الأمريكية لطب الأطفال (AAP)',
                        'en' => 'American Academy of Pediatrics (AAP)',
                    ],
                    'title' => [
                        'ar' => 'إرشادات تغذية الأطفال — تقسيم المسؤولية في الإطعام',
                        'en' => 'Child feeding guidance — the division of responsibility in feeding',
                    ],
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "parents decide what, when and where; the child decides whether and '
                        .'how much". The principle is widely attributed to Ellyn Satter rather than '
                        .'originating with the AAP — the article says only that it appears in guidance '
                        .'including AAP material, which should be confirmed. If the AAP does not state it, '
                        .'attribute it to its actual source rather than to a body that merely echoes it.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الأكاديمية الأمريكية لطب الأطفال (AAP)',
                        'en' => 'American Academy of Pediatrics (AAP)',
                    ],
                    'title' => [
                        'ar' => 'نقص الحديد عند صغار الأطفال واستهلاك اللبن البقري',
                        'en' => 'Iron deficiency in young children and cow\'s milk intake',
                    ],
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the milk section — the most immediately actionable claim in the '
                        .'article. Confident the relationship between high cow\'s milk intake and iron '
                        .'deficiency in toddlers is established and that the AAP addresses it; confirm which '
                        .'document, and add a volume limit only if the source states one. The article '
                        .'deliberately gives no quantity.',
                ],
            ],
        ];
    }
}
