<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * Article 14, and the largest genuine gap in Arabic-language material on this
 * subject: everything written about diabetes is written for a generic patient,
 * and the generic patient does not have a menstrual cycle, a pregnancy, a
 * postpartum period or a menopause.
 *
 * It maps onto two of the clinic's specialties at once — medical nutrition and
 * the hormonal work — and it is filed under medical nutrition because that is
 * what a reader with a diagnosis is looking for, while linking to both.
 *
 * Two claims here matter more than the rest. The first is the postpartum
 * screening after gestational diabetes, repeated deliberately from the
 * postpartum article because it is the single most consequential forgotten
 * test in this whole set of articles. The second is the cardiovascular finding:
 * diabetes appears to raise cardiovascular risk proportionally more in women
 * than in men. It is well documented, it is almost unknown outside specialist
 * circles, and it is exactly the kind of claim that must not be published on a
 * half-remembered reference — which is why its citation says so in terms.
 *
 * ---------------------------------------------------------------------------
 *
 * "THE NUMBER IS INFORMATION, NOT A MARK IN AN EXAM."
 *
 * Her closing line is the same rule the application enforces in code two floors
 * down, and it is worth knowing that the agreement is not a coincidence.
 *
 * PlateFeedbackHasNoNumbersTest refuses to let the plate builder show a calorie
 * and refuses to let the hero case card show an adherence percentage, on the
 * grounds that a figure attached to a patient's own behaviour is a grade she
 * can fail against. Her sentence is that argument, from the clinical side, and
 * about the one number this article cannot remove — a glucose reading is
 * genuinely necessary information, so the only thing left to get right is what
 * it MEANS to the person holding the meter.
 *
 * If a future editor softens that line, they are not editing a nicety: they are
 * breaking the agreement between what this clinic says and what its own
 * software refuses to do.
 *
 * DO NOT MOVE OR SOFTEN THE DOSE SENTENCE. "Do not change your doses yourself
 * just because you have noticed your glucose is higher for a few days" sits
 * immediately beside the passage that encourages a woman to track readings
 * against her cycle, and it belongs there and nowhere else. The tracking advice
 * is what creates the risk the sentence closes: somebody who has just found a
 * pattern is exactly the person who might act on it alone. Separating them
 * leaves the encouragement without the guard.
 */
class DiabetesInWomen extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'diabetes-in-women',
            'category' => 'medical-nutrition',
            'tags' => ['lab-results', 'questions-to-ask'],
            'cover' => 'diabetes-home-glucose-kitchen',

            'title' => [
                'ar' => 'السكري عند الستات — إيه اللي بيختلف',
                'en' => 'Diabetes in women — what is different',
            ],

            'excerpt' => [
                'ar' => 'الدورة والحمل وما بعد الولادة وسن اليأس كلها بتأثر على سكر الدم. الكلام المكتوب عن السكري نادرًا ما بيقول ده.',
                'en' => 'The cycle, pregnancy, the postpartum period and menopause all affect blood glucose. Almost nothing written about diabetes says so.',
            ],

            'body' => [
                'ar' => <<<'AR'
معظم الكلام المكتوب عن السكري مكتوب لمريض عام. والمريض العام ده مالوش دورة شهرية، ولا بيحمل، ولا بيمر بفترة ما بعد ولادة، ولا بيوصل لسن اليأس.

والحاجات الأربعة دي بتأثر على سكر الدم بطرق معروفة وموثقة. المقال ده عن الفروق دي: إيه اللي بيتغير، وليه، وإيه اللي المفروض يتابع.

والنقطة اللي د. رنا بتبدأ منها إن الأساسيات مش بتتغير: مبادئ التعامل مع السكري واحدة. اللي بيتغير إن في عوامل خاصة بالستات لازم تدخل الصورة، ولو مدخلتش بتفضل في فجوة بين اللي مكتوب في الكتب واللي بيحصل فعلًا.

## الأول: مقاومة الإنسولين إيه بالظبط

عشان باقي المقال يبقى مفهوم، لازم الميكانيكية تتقال.

الإنسولين هرمون بيتفرز من البنكرياس بعد الأكل. شغلته إنه يفتح الباب قدام السكر عشان يدخل الخلايا ويتستخدم كطاقة، أو يتخزن.

في مقاومة الإنسولين، الخلايا بتستجيب للهرمون ده بكفاءة أقل. الباب بقى أثقل. فالبنكرياس بيعوّض بإنه يفرز كمية أكبر، والسكر بيفضل في النطاق الطبيعي لفترة طويلة — بس بثمن: مستوى إنسولين عالي في الدم.

الفترة دي ممكن تستمر سنين من غير أي أعراض. ولما قدرة البنكرياس على التعويض تقل، السكر بيبدأ يرتفع — وده الوقت اللي التحاليل بتبان فيه.

يعني الوقت اللي بين بداية المقاومة وبين ظهور التشخيص طويل، وده اللي بيخلي الفحص المبكر ليه معنى.

## الدورة الشهرية وسكر الدم

الهرمونات اللي بتتغير عبر الدورة — وبالذات في النصف التاني منها — بتأثر على حساسية الأنسجة للإنسولين. النتيجة إن نفس الأكل ونفس الجرعة ونفس المجهود ممكن يدّوا قراءات مختلفة في أوقات مختلفة من الشهر.

الظاهرة دي موصوفة بشكل أوضح في أدبيات السكري من النوع الأول، لكن المبدأ نفسه بيتكلم عنه في سياق مقاومة الإنسولين عمومًا.

والأهمية العملية إن الست اللي بتقيس سكرها ولاقية تذبذب مش مفهوم ممكن تكون بتشوف حاجة ليها تفسير — والتفسير ده مش هيظهر لو محدش سأل عن توقيت الدورة.

وفي الاتجاه التاني: تغيّرات الشهية والرغبة في الأكل عبر الدورة حقيقية كمان، وبتأثر على الأكل نفسه.

ود. رنا بتحذر من التعميم هنا بالذات: الأثر ده موجود وموصوف، بس مقداره وشكله بيختلف من ست للتانية، والقاعدة العامة اللي بتتقال لكل الناس مش هتنفع حد. اللي بينفع إنك تعرفي نمطك إنتِ.

والطريقة عملية: سجّلي القراءات مع يوم الدورة، مش القراءات لوحدها. بعد شهرين أو تلاتة النمط بيبان — أو يبان إنه مفيش نمط، وده معلومة كمان.

وحاجة واحدة لازم تتقال بوضوح هنا: متغيريش الجرعات من نفسك لمجرد إنك لاحظتي السكر أعلى كام يوم. النمط ده بيتشاف مع الطبيب المتابع، وهو اللي بيقرر لو في تعديل. الملاحظة شغلك؛ التعديل شغله.

## تكيس المبايض والسكري — أوضح تقاطع

دي مش صدفة ولا ملاحظة عابرة: مقاومة الإنسولين جزء مركزي في جزء كبير من حالات تكيس المبايض، وهي نفسها الآلية اللي ورا السكري من النوع التاني.

عشان كده الدليل الدولي المبني على الأدلة لتقييم وإدارة تكيس المبايض بيتعامل مع البُعد الأيضي كجزء من إدارة الحالة، والجمعية الأمريكية للسكري في معاييرها للرعاية بتحط تكيس المبايض ضمن الحالات اللي بتستدعي الانتباه لخطر السكري.

يعني الست اللي عندها تشخيص تكيس مبايض عندها معلومة مهمة عن خطر مستقبلي، والمعلومة دي قابلة للتصرف فيها — وده بالظبط الفرق بين معرفة مبكرة ومعرفة متأخرة.

ود. رنا بتقول الجملة دي بالشكل ده بالظبط، والشكل مقصود: «مش معنى إن عندك تكيس إن إصابتك بالسكري شيء حتمي… ده معناه إن عندنا سبب أكبر نهتم بدري، مش سبب نخاف».

في مقال عن الحالة دي وعن الادعاءات اللي بتتقال عنها: [[article:pcos-and-food-judging-a-claim|تكيس المبايض والأكل — إزاي تحكمي على أي كلام]].

## الحمل: قبله وأثناءه

الحمل بطبيعته بيرفع مقاومة الإنسولين — ده تغيّر فسيولوجي طبيعي بيخلي السكر متاح للجنين. والبنكرياس بيعوّض. ولما التعويض ميبقاش كافي، بيظهر سكري الحمل.

والفرق المهم إن ده مش «سكري خفيف» — ده حالة بتتابع، لأنها بتأثر على الحمل وعلى الولادة.

والست اللي عندها سكري قبل الحمل عندها موضوع تاني: التخطيط للحمل. المعهد الوطني البريطاني (NICE) عنده دليل مخصص للسكري في الحمل من مرحلة ما قبل الحمل لحد ما بعد الولادة، والجمعية الأمريكية للسكري في معاييرها بتتكلم عن الرعاية قبل الحمل للستات المصابات بالسكري.

النقطة اللي بتتساب: الجزء ده بيبدأ قبل الحمل مش بعده. والقرارات دي بتتاخد مع الطبيب المتابع، مش بعد التأكد من الحمل.

## بعد الولادة: الفحص اللي بيتنسى

دي أهم جملة في المقال كله.

الست اللي كان عندها سكري حمل بتبقى في خطر أعلى للإصابة بالسكري من النوع التاني بعدين. والجمعية الأمريكية للسكري بتوصي بفحص سكر بعد الولادة بفترة محددة للستات دول، وبمتابعة دورية بعدها مدى الحياة.

واللي بيحصل عمليًا إن الموضوع بيتقفل بالولادة. الحمل خلص، السكر رجع، والورق اتحط في درج.

الفحص ده مش إجراء روتيني ملوش لازمة. هو الفرق بين اكتشاف حالة في سن أربعين واكتشافها في سن خمسة وخمسين ومعاها مضاعفات بدأت.

في مقال عن الفترة دي كلها: [[article:postpartum-nutrition|التغذية بعد الولادة — للأم في أول ستة شهور]].

## سن اليأس

في الفترة اللي حوالين انقطاع الطمث بتحصل تغيّرات هرمونية بتصاحبها تغيّرات في تركيب الجسم — وبالذات في توزيع الدهون — وفي حساسية الإنسولين.

النتيجة إن ست كانت مستقرة لسنين ممكن تلاحظ تغيّر في الوزن أو في قراءات السكر من غير ما تكون غيّرت حاجة في أكلها. وده بيتقري غالبًا على إنه تقصير شخصي، مع إنه تغيّر فسيولوجي متوقع.

والمعلومة اللي بتفرق إن ده وقت بتتغير فيه الخطة، مش وقت بيتلام فيه الشخص.

## وحاجة تانية بتتغير في سن اليأس: العظام

في نفس الفترة بيحصل تغيّر في كثافة العظام مرتبط بتراجع الهرمونات، وده بيخلي الكالسيوم وفيتامين د والحركة — وبالذات تمارين المقاومة — جزء من الصورة مش موضوع منفصل.

يعني الخطة في السن ده مش بتبقى عن السكر بس. وده بالظبط اللي بيخلي الخطة الفردية مختلفة عن نصيحة عامة عن السكري.

## نقطة تستاهل تتعرف: الخطر القلبي

في ملاحظة متكررة في المراجعات المنهجية: السكري بيرفع خطر أمراض القلب والأوعية الدموية عند الستات بنسبة أكبر مما بيرفعه عند الرجالة.

يعني وجود السكري بيلغي جزء من الفرق اللي بيكون موجود بين الجنسين في خطر القلب.

ودي معلومة نادرًا ما بتتقال لمريضة، مع إنها بتغيّر أولويات المتابعة: الضغط والدهون والتدخين بتبقى جزء من إدارة السكري، مش مواضيع منفصلة.

وفي نقطة عملية جنبها: الأدوية اللي بتتاخد لحالات تانية ممكن تأثر على السكر. وسائل منع الحمل الهرمونية، والكورتيزون، وبعض أدوية الغدة الدرقية — كلها ليها علاقة بالصورة، وكلها بتتقال في الكشف.

## أعراض بتظهر بشكل مختلف

في أعراض بتيجي عند الستات بشكل ممكن يتأخر تفسيره:

الالتهابات الفطرية المتكررة (الكانديدا) والتهابات المسالك البولية المتكررة ممكن تكون مرتبطة بارتفاع السكر، لأن السكر المرتفع بيوفر بيئة مناسبة لنمو الميكروبات وبيأثر على مقاومة العدوى.

يعني الشكوى اللي بتتعالج كل مرة لوحدها ممكن تكون علامة على حاجة تانية ورا الباب. مش معناها كده دايمًا — معناها إنها تستاهل تتقال.

## خرافات

«السكري بيجي من إنك بتاكلي حلويات كتير». التبسيط ده منتشر وبيخلق ذنب مالوش لازمة. السكري من النوع التاني بيتطور من مقاومة إنسولين، وفيه عوامل وراثية وعوامل مرتبطة بنمط الحياة والوزن والحركة والسن. السكر المضاف جزء من الصورة، مش كل الصورة، وناس بتاكل حلويات ومبتجيلهاش، وناس مبتاكلش وبتجيلها.

«سكري الحمل خلص بالولادة». اتقال فوق ليه ده غلط.

«الفاكهة ممنوعة». الفاكهة فيها سكر طبيعي ومعاه ألياف ومية وعناصر. التعامل معاها بيكون في إطار الوجبة والكمية، مش بالمنع الشامل. والمنع الشامل بيشيل مصدر أكل كويس وبيصعّب الخطة من غير مكسب.

«لو حاسة إني كويسة يبقى السكر مظبوط». الارتفاع المزمن ممكن ميديش أعراض واضحة لفترة طويلة. الإحساس مش مقياس.

## في المطبخ المصري

العيش البلدي والرز والمكرونة والكشري نشويات، والنشويات جزء من الأكل. اللي بيفرق هو الكمية، وشكل الوجبة، وإيه اللي بيتاكل معاهم.

نفس كمية الرز مع سلطة وخضار ومصدر بروتين بتدي استجابة مختلفة عن نفس الكمية لوحدها. والبقوليات — الفول والعدس والحمص — فيها ألياف وبروتين، وهي أكل بيتاكل في كل بيت.

والتمر موضوع بيتسأل عنه كتير في رمضان والمناسبات. التمر أكل حقيقي فيه سكر مركّز، والتعامل معاه بيكون بالكمية والسياق، مش بالمنع ولا بالاعتبار إنه «مسموح لأنه طبيعي».

## والصيام

الصيام في رمضان قرار ليه أبعاد إكلينيكية عند أي حد عنده سكري، خصوصًا لو بياخد أدوية بتقلل السكر.

وفي إرشادات عملية متخصصة في السكري ورمضان بتتعامل مع تصنيف المخاطر وتعديل العلاج والمتابعة أثناء الصيام. يعني ده موضوع ليه أدبيات، مش اجتهاد شخصي.

والقرار ده بيتاخد قبل رمضان مع الطبيب المتابع، مش في أول يوم.

## الأسئلة اللي بتتسأل ومش بتلاقي إجابة

في أسئلة د. رنا بتسمعها من مريضات السكري كل مرة تقريبًا، ومش بيبقى عندهم مكان يسألوها فيه:

السكر بيعلى قبل الدورة — ده طبيعي؟

الحمل هيأثر إزاي؟ وأقدر أخطط له؟

كان عندي سكري حمل — أنا دلوقتي في خطر؟

سن اليأس هيغيّر إيه؟

والأدوية بتاعتي ليها علاقة بالأكل إزاي؟

والأسئلة دي مش هامشية. كل واحدة فيهم بتغيّر حاجة في المتابعة، وكلها ليها إجابات موجودة في الأدبيات — بس نادرًا ما بيبقى في وقت في الكشف عشان تتسأل.

## وإدارة السكري مش «امنعي السكر»

الاختصار ده هو أكتر حاجة بتضيّع وقت.

إدارة السكري فيها شكل الوجبة وتوزيعها على اليوم، ونوع الكربوهيدرات مش وجودها، والبروتين والألياف والدهون معاها، والحركة، والنوم، والتوتر، والأدوية وتوقيتها، والمتابعة اللي بتقول إيه اللي بيشتغل.

والأكل اللي بيتشال من القايمة بيرجع تاني في أول أسبوع صعب. اللي بيفضل هو الشكل اللي الشخص يقدر يعيش بيه.

## اللي يستاهل تفتكريه

مقاومة الإنسولين بتسبق التشخيص بسنين، وده اللي بيدي الفحص المبكر معنى.

والدورة الشهرية بتأثر على قراءات السكر. وتكيس المبايض ليه علاقة مباشرة بنفس الآلية. والحمل بيرفع المقاومة بطبيعته، والتخطيط له بيبدأ قبله. وسكري الحمل له فحص بعد الولادة بيتنسى. وسن اليأس بيغيّر الصورة من غير ما الشخص يغيّر حاجة.

والخطر القلبي عند الستات المصابات بالسكري نقطة تستاهل تتعرف وتغيّر أولويات المتابعة.

وحاجة أخيرة من د. رنا، وهي اللي بتفرق أكتر من أي معلومة فوق: «متلوميش نفسك على كل قراءة عالية. الرقم معلومة، مش درجة في امتحان».

لأن الهدف مش إنك تمسكي في إيدك قايمة أكل تنفذيها — الهدف إنك تفهمي اللي بيحصل في جسمك، عشان تعرفي تتصرفي لما الظروف تتغير، ومش هتفضل تسألي حد كل مرة.

لو عندك تشخيص وعايزة خطة بتاخد كل ده في الحسبان: [[specialty:medical-nutrition|التغذية العلاجية]] أو [[specialty:pcos-hormonal|تغذية الاضطرابات الهرمونية]] أو [[booking|احجزي موعد]].

ولو عندك ورق تحاليل ومحدش شرحه: [[article:normal-results-still-tired|التحاليل سليمة وأنا لسه تعبانة]].
AR,

                'en' => <<<'EN'
Most of what is written about diabetes is written for a generic patient. And that generic patient has no menstrual cycle, does not become pregnant, does not pass through a postpartum period, and does not reach menopause.

Those four things affect blood glucose in ways that are known and documented. This article is about those differences: what changes, why, and what should be followed.

The point Dr Rana starts from is that the fundamentals do not change: the principles of managing diabetes are the same. What changes is that there are factors specific to women which have to enter the picture — and when they do not, a gap stays open between what the books say and what is actually happening.

## First: what insulin resistance actually is

For the rest of the article to make sense, the mechanism has to be stated.

Insulin is a hormone secreted by the pancreas after eating. Its job is to open the door for glucose to enter cells and be used as energy, or stored.

In insulin resistance, cells respond to that hormone less efficiently. The door has become heavier. The pancreas compensates by secreting more, and glucose stays in the normal range for a long time — at a price: a high circulating insulin level.

That period can last years with no symptoms at all. And when the pancreas's capacity to compensate declines, glucose begins to rise — which is when tests show anything.

So the interval between insulin resistance beginning and a diagnosis appearing is long, and that is what gives early screening its meaning.

## The menstrual cycle and blood glucose

The hormones that change across the cycle — particularly in its second half — affect how sensitive tissues are to insulin. The result is that the same food, the same dose and the same activity can produce different readings at different points in the month.

The phenomenon is described most clearly in the type 1 diabetes literature, but the same principle is discussed in the context of insulin resistance more generally.

The practical importance is that a woman measuring her glucose and finding fluctuation she cannot explain may be seeing something that has an explanation — and that explanation will not appear if nobody asks about where she is in her cycle.

And in the other direction: changes in appetite and food preference across the cycle are real too, and affect the eating itself.

Dr Rana cautions against generalising here in particular: the effect is real and described, but its size and its shape differ from one woman to another, and a general rule stated for everybody will not fit anybody. What helps is knowing your own pattern.

And the method is practical: record the readings against the day of your cycle, not the readings on their own. After two or three months a pattern shows — or it shows that there is no pattern, which is information too.

And one thing has to be said plainly here: do not change your doses yourself just because you have noticed your glucose is higher for a few days. That pattern is looked at with the doctor following your case, and it is the doctor who decides whether an adjustment is needed. Noticing is your job; adjusting is theirs.

## PCOS and diabetes — the clearest intersection

This is neither a coincidence nor a passing observation: insulin resistance is central to a large proportion of PCOS, and it is the same mechanism that underlies type 2 diabetes.

Which is why the international evidence-based guideline on PCOS treats the metabolic dimension as part of managing the condition, and why the American Diabetes Association, in its Standards of Care, includes PCOS among the conditions warranting attention to diabetes risk.

So a woman with a PCOS diagnosis holds important information about a future risk — and it is information she can act on, which is exactly the difference between knowing early and knowing late.

Dr Rana puts it in exactly this shape, and the shape is deliberate: "Having PCOS does not mean that getting diabetes is inevitable… it means we have a stronger reason to pay attention early — not a reason to be afraid."

There is an article on that condition and on the claims made about it: [[article:pcos-and-food-judging-a-claim|PCOS and food — how to judge a claim]].

## Pregnancy: before and during

Pregnancy by its nature increases insulin resistance — a normal physiological change that keeps glucose available to the fetus. The pancreas compensates. When that compensation is no longer sufficient, gestational diabetes appears.

And the important distinction is that this is not "mild diabetes" — it is a condition that is followed, because it affects the pregnancy and the birth.

A woman with diabetes before pregnancy has a separate matter to consider: planning. NICE has guidance devoted to diabetes in pregnancy from preconception through to the postnatal period, and the ADA Standards address preconception care for women with diabetes.

The part that gets left out: this begins before pregnancy, not after it. And those decisions are made with the doctor following the case, rather than after a pregnancy is confirmed.

## After birth: the screening that gets forgotten

This is the most important sentence in the whole article.

A woman who had gestational diabetes is at higher risk of developing type 2 diabetes later. The ADA recommends glucose testing at a defined interval after delivery for these women, and periodic screening thereafter for life.

What happens in practice is that the matter closes with the birth. The pregnancy ended, the glucose came back, and the paperwork went into a drawer.

That test is not a routine with no purpose. It is the difference between finding a condition at forty and finding it at fifty-five with complications already beginning.

There is an article on that whole period: [[article:postpartum-nutrition|postpartum nutrition — the mother in the first six months]].

## Menopause

Around the cessation of periods there are hormonal changes accompanied by changes in body composition — particularly in the distribution of fat — and in insulin sensitivity.

The result is that a woman who has been stable for years may notice a change in weight or in glucose readings without having changed anything about her eating. And that usually gets read as a personal failing, when it is an expected physiological change.

The information that makes a difference is that this is a time when the plan changes, not a time when the person is blamed.

## And something else that changes at menopause: bone

At the same time there is a change in bone density related to the decline in hormones, which makes calcium, vitamin D and activity — resistance training in particular — part of the picture rather than a separate subject.

So a plan at this age is not only about glucose. And that is precisely what makes an individual plan different from general advice about diabetes.

## A point worth knowing: cardiovascular risk

There is a consistent finding in systematic reviews: diabetes raises the risk of cardiovascular disease proportionally more in women than in men.

Which is to say that having diabetes erases part of the difference that otherwise exists between the sexes in cardiac risk.

This is rarely said to a patient, although it changes the priorities of follow-up: blood pressure, lipids and smoking become part of managing diabetes rather than separate subjects.

A practical point beside it: medications taken for other conditions can affect glucose. Hormonal contraception, corticosteroids, and some thyroid medications all bear on the picture, and all of them should be mentioned in a consultation.

## Symptoms that present differently

Some symptoms arrive in women in a form whose explanation can be delayed:

Recurrent fungal infections (candida) and recurrent urinary tract infections can be related to raised glucose, because high glucose provides a favourable environment for microbial growth and affects resistance to infection.

So a complaint treated in isolation each time it occurs may be a sign of something else behind the door. That is not always what it means — it means it is worth mentioning.

## Myths

"Diabetes comes from eating too many sweets." This simplification is widespread and creates guilt with no purpose. Type 2 diabetes develops from insulin resistance, with genetic factors and factors related to lifestyle, weight, activity and age. Added sugar is part of the picture, not the whole picture — and people who eat sweets do not get it, and people who do not eat them do.

"Gestational diabetes ended at the birth." Why that is wrong is above.

"Fruit is forbidden." Fruit contains natural sugar and comes with fibre, water and nutrients. It is handled within the shape and size of a meal, not by blanket exclusion. And a blanket exclusion removes a good food source and makes the plan harder with nothing gained.

"If I feel well, my glucose must be fine." Chronic elevation can produce no clear symptoms for a long time. How you feel is not a measurement.

## In an Egyptian kitchen

Baladi bread, rice, pasta and koshari are starches, and starch is part of eating. What makes the difference is the amount, the shape of the meal, and what is eaten alongside.

The same quantity of rice with salad, vegetables and a protein source produces a different response from the same quantity alone. And legumes — foul, lentils, chickpeas — carry fibre and protein, and are eaten in every household.

Dates come up constantly in Ramadan and at occasions. Dates are real food containing concentrated sugar, and they are handled by amount and context — not by prohibition, and not by treating them as permitted because they are natural.

## And fasting

Fasting in Ramadan is a decision with clinical dimensions for anybody with diabetes, particularly on medication that lowers glucose.

There is specialist practical guidance on diabetes and Ramadan covering risk stratification, treatment adjustment and monitoring during fasting. Which is to say this is a subject with a literature behind it, not a matter of personal judgement.

And that decision is made before Ramadan with the doctor following the case, not on the first day.

## The questions that get asked and find no answer

There are questions Dr Rana hears from women with diabetes almost every time, and for which they have nowhere else to ask:

My glucose rises before my period — is that normal?

How will pregnancy affect this? And can I plan for it?

I had gestational diabetes — am I at risk now?

What will menopause change?

And how do my medicines relate to my food?

None of those is marginal. Every one of them changes something in the follow-up, and every one has answers that exist in the literature — but there is rarely time in a consultation for them to be asked.

## And managing diabetes is not "cut out sugar"

That shorthand wastes more time than anything else.

Managing diabetes involves the shape of a meal and how it is spread across the day, the kind of carbohydrate rather than its presence, the protein and fibre and fat alongside it, activity, sleep, stress, the medicines and their timing, and the follow-up that says what is working.

And food taken off a list comes back in the first difficult week. What lasts is the shape somebody can live inside.

## Worth remembering

Insulin resistance precedes a diagnosis by years, which is what gives early screening its meaning.

The menstrual cycle affects glucose readings. PCOS shares the same mechanism directly. Pregnancy raises resistance by its nature, and planning for it begins beforehand. Gestational diabetes has a postpartum test that gets forgotten. And menopause changes the picture without the person having changed anything.

And cardiovascular risk in women with diabetes is a point worth knowing that changes the priorities of follow-up.

And one last thing from Dr Rana, which matters more than any fact above it: "Don't blame yourself for every high reading. The number is information, not a mark in an exam."

Because the goal is not to be handed a list of foods to follow — the goal is that you understand what is happening in your body, so that you know how to act when circumstances change, instead of having to ask somebody every time.

If you have a diagnosis and want a plan that accounts for all of this: [[specialty:medical-nutrition|medical nutrition therapy]], [[specialty:pcos-hormonal|hormonal nutrition]], or [[booking|book an appointment]].

And if you have results nobody has explained: [[article:normal-results-still-tired|my results are normal and I am still exhausted]].
EN,
            ],

            'citations' => [
                [
                    'organisation' => [
                        'ar' => 'الجمعية الأمريكية للسكري (ADA)',
                        'en' => 'American Diabetes Association (ADA)',
                    ],
                    'title' => ['ar' => 'معايير الرعاية في السكري', 'en' => 'Standards of Care in Diabetes'],
                    'year' => 2025,
                    'confidence' => CitationConfidence::High,
                    'note' => 'Carries four separate claims and needs checking against each: (1) PCOS listed '
                        .'among conditions warranting attention to diabetes risk; (2) preconception care for '
                        .'women with diabetes; (3) postpartum glucose testing at a defined interval after '
                        .'gestational diabetes; (4) periodic lifelong screening thereafter. THE ARTICLE '
                        .'GIVES NO INTERVAL for (3) — the ADA states one and it should be added from the '
                        .'current edition. Reissued annually; update the year.',
                ],
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
                    'note' => 'Supports "treats the metabolic dimension as part of managing the condition". '
                        .'Same guideline used in the two PCOS articles — verify once, apply to all three.',
                ],
                [
                    'organisation' => [
                        'ar' => 'المعهد الوطني للصحة وجودة الرعاية (NICE)',
                        'en' => 'National Institute for Health and Care Excellence (NICE)',
                    ],
                    'title' => [
                        'ar' => 'السكري في الحمل: التعامل من مرحلة ما قبل الحمل إلى ما بعد الولادة',
                        'en' => 'Diabetes in pregnancy: management from preconception to the postnatal period',
                    ],
                    'year' => 2015,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "NICE has guidance devoted to diabetes in pregnancy from '
                        .'preconception through to the postnatal period" — cited essentially for its own '
                        .'scope, which the title states. Confirm the year and current status; it has been '
                        .'updated since original publication.',
                ],
                [
                    'organisation' => [
                        'ar' => 'مراجعات منهجية وتحليلات تجميعية عن السكري وخطر أمراض القلب والأوعية الدموية حسب الجنس',
                        'en' => 'Systematic reviews and meta-analyses of diabetes and cardiovascular risk by sex',
                    ],
                    'title' => [
                        'ar' => 'الفروق بين الجنسين في الخطر القلبي الوعائي المرتبط بالسكري',
                        'en' => 'Sex differences in diabetes-associated cardiovascular risk',
                    ],
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'THE CLAIM MOST WORTH CHECKING IN THIS ARTICLE AND THE ONE WITH THE VAGUEST '
                        .'SOURCE. The finding — that diabetes raises cardiovascular risk proportionally more '
                        .'in women than in men — is well established and has been reported in large '
                        .'meta-analyses, but this entry names no single one, because the draft was not '
                        .'confident of authors or year and would not invent them. Before publication this '
                        .'must be replaced with one named meta-analysis, or with a guideline that states the '
                        .'finding. If neither can be produced, cut the section: it is the kind of striking '
                        .'claim a reader will repeat, and it must be traceable when she does.',
                ],
                [
                    'organisation' => [
                        'ar' => 'إرشادات عملية متخصصة في السكري ورمضان',
                        'en' => 'Specialist practical guidance on diabetes and Ramadan',
                    ],
                    'title' => [
                        'ar' => 'إرشادات عملية لإدارة السكري أثناء صيام رمضان',
                        'en' => 'Practical guidelines for the management of diabetes during Ramadan fasting',
                    ],
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "there is specialist practical guidance covering risk '
                        .'stratification, treatment adjustment and monitoring during fasting". Confident '
                        .'such guidance exists and is produced in collaboration between the International '
                        .'Diabetes Federation and a Ramadan-focused alliance; the issuing body is not named '
                        .'here because the draft was not certain of the exact name. Name it and give the '
                        .'edition year at verification — this is the most locally relevant citation in the '
                        .'entire set and deserves a precise reference.',
                ],
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => ['ar' => 'صحيفة وقائع: السكري', 'en' => 'Fact sheet: Diabetes'],
                    'confidence' => CitationConfidence::High,
                    'note' => 'General background for the description of type 2 diabetes developing from '
                        .'insulin resistance with genetic and lifestyle contributors, used in the myths '
                        .'section against "diabetes comes from eating sweets". Record the revision date '
                        .'current at verification.',
                ],
            ],
        ];
    }
}
