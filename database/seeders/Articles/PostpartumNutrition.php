<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * Article 13. The practitioner's own pick, and it fills a real gap: everything
 * written for this period is written about the baby.
 *
 * Two sections justify the whole piece. The first is the iron section — the
 * store was drawn on through pregnancy and then again at delivery, and the
 * exhaustion that follows gets attributed to the baby and never investigated.
 * The second is the postpartum glucose screening after gestational diabetes,
 * which the ADA recommends, which almost nobody in Egypt is offered, and which
 * is the difference between finding type 2 diabetes at forty and at fifty-five.
 *
 * Postpartum thyroiditis is included for the same reason: it presents as
 * exhaustion in the first year, it is common enough to matter, and it is read
 * as "she has a new baby" by everybody including the mother.
 *
 * ---------------------------------------------------------------------------
 *
 * THE DANGER-SIGNS CALLOUT IS THE MOST CONSEQUENTIAL PASSAGE ON THIS SITE.
 *
 * Everything around it reassures — that is what the article is for, and it is
 * right to reassure, because most postpartum exhaustion is postpartum
 * exhaustion. The risk that creates is precise: a mother reading at two in the
 * morning finds the reassurance she came for and stops. Several items on that
 * list are recognised postnatal danger signs, and the cost of scrolling past
 * them is not a worse article.
 *
 * So it is in a box, under its own heading, immediately after the section on
 * what is expected — and her sentence about not using lack of sleep as a
 * ready-made answer follows it rather than opening it, so the list is read
 * first.
 *
 * NOTHING WAS ADDED TO HER LIST. Not one sign, however tempting. A list of
 * danger signs under a practitioner's name that contains items she did not put
 * there is a worse failure than a short list.
 *
 * THE LAST ITEM IS HANDLED THE WAY SHE FRAMED IT. "Feeling that you are not
 * well in yourself and cannot cope" points to asking for help. It does not
 * name a condition, does not diagnose, and does not tell the reader what she
 * has — because that is not this article's to do, and because a mother who
 * does not recognise a label in herself will decide the paragraph is not about
 * her.
 */
class PostpartumNutrition extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'postpartum-nutrition',
            'category' => 'pregnancy-nutrition',
            'tags' => ['family', 'questions-to-ask'],
            'cover' => 'postpartum-kitchen-simple-meal',

            'title' => [
                'ar' => 'التغذية بعد الولادة — للأم في أول ستة شهور',
                'en' => 'Postpartum nutrition — the mother in the first six months',
            ],

            'excerpt' => [
                'ar' => 'كل الكلام بعد الولادة بيبقى عن الطفل. المقال ده عن الأم: الاحتياج اللي بيرتفع، والمخزون اللي اتسحب منه، والتعب اللي مش كله طبيعي.',
                'en' => 'After a birth, everything said is about the baby. This is about the mother: the requirement that rises, the stores that were drawn on, and the exhaustion that is not all normal.',
            ],

            'body' => [
                'ar' => <<<'AR'
بعد الولادة الأسئلة كلها بتبقى عن الطفل. بيرضع كويس؟ بينام؟ وزنه زاد؟ نام كام ساعة؟

والأم بتتسأل سؤال واحد: «إنتِ كويسة؟» — وبتجاوب «الحمد لله» وبتكمّل.

المقال ده عن الجزء اللي مش بيتسأل عنه: الجسم اللي خرج من تسعة شهور حمل، وولادة، وبقى بيرضّع، وبينام متقطع، وبياكل آخر واحد في البيت.

## أول سؤال في الكشف مش عن الوزن

د. رنا بتقول: «مش أول سؤال بسألها (وزنك كام وعايزة توصلي لكام؟) — أنا الأول عايزة أعرف إنتِ عاملة إيه».

والحاجات اللي بتسأل عنها الأول:

الولادة كانت طبيعية ولا قيصرية؟

التعافي ماشي إزاي؟

الرضاعة شغالة إزاي؟

بتنامي قد إيه؟

نفسك للأكل عاملة إزاي؟

في أنيميا؟

وبتاخدي أدوية إيه؟

سبع حاجات، ومفيش فيهم رقم على ميزان. لأن الإجابات دي هي اللي بتحدد إيه اللي ينفع يتعمل دلوقتي وإيه اللي بدري عليه.

## الاحتياج بيرتفع في الوقت اللي الوقت فيه بيقل

دي المفارقة اللي بتلخص الفترة كلها.

إنتاج اللبن بيستهلك طاقة، والاحتياج من الطاقة في الرضاعة الكاملة بيبقى أعلى منه في الحمل نفسه. ودي معلومة بتفاجئ ناس كتير، لأن كل الاهتمام والتوصيات والاحتياطات بتتركز في التسعة شهور اللي فاتت وبتقف عند الولادة.

وفي نفس الوقت بالظبط، القدرة على تجهيز أكل بتنهار: نوم متقطع، ووقت مش موجود، وأولوية مطلقة للطفل، وزيارات. النتيجة النمطية إن الأم بتاكل أقل، وأسرع، وأبسط، في الفترة اللي احتياجها فيها هو الأعلى في حياتها كلها تقريبًا.

منظمة الصحة العالمية عندها توصيات مخصصة لرعاية الأم والمولود في فترة ما بعد الولادة، وبتتعامل مع الفترة دي كفترة رعاية ليها متطلباتها — مش كفترة بتعدي.

## الحديد: المخزون اتسحب منه مرتين

دي أهم نقطة في المقال، وميكانيكيتها بتشرح تعب كتير بيتنسب للطفل.

في الحمل، احتياج الحديد بيرتفع بشكل كبير: حجم الدم بيزيد، والمشيمة بتتبنى، والجنين بيخزن حديد لنفسه في الشهور الأخيرة. المخزون بيتسحب منه على مدى تسعة شهور.

وفي الولادة بيحصل فقد دم — وده طبيعي، وبيختلف في مقداره حسب نوع الولادة وظروفها. يعني نفس المخزون بيتسحب منه مرة تانية، دفعة واحدة.

والنتيجة إن كتير من الستات بيخرجوا من الولادة بمخزون حديد منخفض، والأعراض بتبقى إرهاق وضيق نفس مع المجهود وشحوب وصعوبة تركيز — وكل دي أعراض بتتقري على إنها «طبيعي، عندها بيبي».

منظمة الصحة العالمية عندها إرشادات عن مكملات الحديد في فترة ما بعد الولادة، يعني الموضوع ده متعامل معاه كمسألة قايمة بذاتها مش كتفصيلة.

والمهم إن ده بيتقاس. مخزون الحديد بيقل قبل ما الهيموجلوبين ينزل، فصورة الدم لوحدها ممكن تبقى مطمنة والمخزون فاضي. في مقال منفصل عن النقطة دي: [[article:normal-results-still-tired|التحاليل سليمة وأنا لسه تعبانة]].

## التعب: إيه اللي متوقع وإيه اللي محتاج يتشاف

الجزء ده لازم يتقال بوضوح، لأن «كله تعب طبيعي» بيخفي حاجات ليها علاج.

التعب من النوم المتقطع حقيقي ومتوقع، وبيتحسن مع تحسن نوم الطفل.

لكن في حاجات تانية بتظهر بنفس الشكل:

نقص الحديد، زي ما فات.

والتهاب الغدة الدرقية بعد الولادة — دي حالة بتحصل في السنة الأولى بعد الولادة، وبتمر بمراحل، وأعراضها بتشمل إرهاق وتغيّر في الوزن وتغيّر في المزاج. وهي بالظبط النوع اللي بيتقري على إنه «طبيعي بعد الولادة»، وفي إرشادات متخصصة للتعامل مع أمراض الغدة الدرقية في الحمل وبعد الولادة.

واكتئاب ما بعد الولادة، اللي أعراضه مش دايمًا حزن ظاهر — ممكن تبقى إرهاق، وفقدان اهتمام، وتغيّر في الأكل والنوم. والإرشادات المتخصصة في رعاية ما بعد الولادة بتوصي بالسؤال عن الحالة النفسية كجزء من المتابعة، مش كموضوع منفصل.

يعني «تعبانة» بعد الولادة جملة ليها أكتر من سبب محتمل، وبعضها ليه علاج.

## علامات متتفسّرش بقلة النوم

دي أهم فقرة في المقال، ولازم تتقرا حتى لو مفيش وقت لقراية حاجة تانية.

ودي العلامات اللي د. رنا بتقول إنها بتستدعي تقييم طبي عاجل:

> لو في:
> - إرهاق شديد أو بيزيد
> - دوخة
> - خفقان
> - ضيق نفس
> - سخونية
> - نزيف غير معتاد
> - ألم شديد
> - صداع شديد
> - أو إحساس إن حالتك النفسية مش كويسة ومش قادرة تتعاملي
> الحاجات دي بتتشاف عند طبيب، ومش بتتأجل لحد ما النوم يتحسن.

والجملة اللي بتقولها بعدها: «مهم جدًا منستخدمش قلة النوم كإجابة جاهزة لكل عرض بعد الولادة».

ومنظمة الصحة العالمية، في توصياتها لرعاية الأم والمولود في فترة ما بعد الولادة، بتتعامل مع علامات الخطر بعد الولادة على إنها حاجة الأم لازم تعرفها وتعرف تتصرف تجاهها، مش حاجة تستنى الزيارة الجاية.

والبند الأخير في القايمة دي زي أي بند تاني فيها. الإحساس إن حالتك النفسية مش كويسة ومش قادرة تتعاملي سبب كافي تمامًا إنك تكلمي حد وتطلبي مساعدة — من غير ما تستني الموضوع يبقى «كفاية».

## سكري الحمل: الفحص اللي بيتنسى

النقطة دي ممكن تغيّر مسار صحي كامل، ونادرًا ما بتتقال.

الست اللي كان عندها سكري حمل بتبقى في خطر أعلى للإصابة بالسكري من النوع التاني بعدين. والجمعية الأمريكية للسكري في معاييرها السنوية للرعاية بتوصي بفحص سكر بعد الولادة بفترة محددة للستات اللي كان عندهن سكري حمل، وبمتابعة دورية بعد كده مدى الحياة.

اللي بيحصل عمليًا إن سكري الحمل بيتعامل معاه كحاجة انتهت بالولادة. الست بتخرج من المستشفى والموضوع مقفول في ذهن الجميع.

فالسؤال اللي يتسأل: كان عندي سكري حمل — الفحص المفروض يتعمل إمتى؟ وبعدها كل قد إيه؟

## الكالسيوم والعظام في الرضاعة

في تغيّر معروف بيحصل في كتلة العظام أثناء الرضاعة، والجسم بيعوّضه بعد الفطام في الظروف الطبيعية. يعني ده تغيّر مؤقت ومتوقع، مش ضرر دايم.

بس ده بيفترض إن المدخول من الكالسيوم وفيتامين د كافي. وفيتامين د بالذات نقطة تستاهل الانتباه في مصر: بلد شمسها قوية والنقص موجود، والسبب مش الشمس — السبب التعرض. والأم في الشهور الأولى تحديدًا بتقضي وقت أطول جوه البيت.

## بعد الولادة القيصرية — ملاحظات إضافية

الولادة القيصرية عملية جراحية كبرى، والتعافي منها له متطلبات مختلفة شوية.

التئام الجرح بيحتاج بروتين كافي، والبروتين هو أول حاجة بتقل لما الأكل يبقى «أي حاجة سريعة». والإمساك شائع بعد الجراحة بسبب قلة الحركة وبعض مسكنات الألم، والألياف والسوائل ليهم دور فيه.

وفقد الدم في القيصرية بيبقى في المتوسط أعلى، وده بيرجّعنا لنقطة الحديد.

والحركة البسيطة المبكرة بتتحدد من الطبيب المتابع حسب الحالة، مش من نصيحة عامة.

## الرضاعة والوزن — الجملة اللي بتتقال غلط

«الرضاعة بتخسّس» جملة بتتقال كتير وبتخلق توقعات مش دقيقة.

إنتاج اللبن فعلًا بيستهلك طاقة، وده حقيقي. لكن اللي بيحصل عند كل ست مختلف: في ستات وزنهم بينزل في الفترة دي، وفي ستات وزنهم بيثبت، وفي ستات بيزيد — والأسباب بتشمل النوم، والحركة، وشكل الأكل، والحالة الهرمونية، وأشياء تانية.

اللي مش مفيد إن الست تقيس نفسها على توقع اتقال لها من غير أساس، وتستنتج إن في حاجة غلط فيها لما التوقع ده ميحصلش.

الوزن بعد الولادة موضوع بيتاخد في وقته، بهدوء، ومع حد شايف الحالة والرضاعة مع بعض.

## الأكل لما مفيش وقت — الجزء العملي

النصيحة اللي بتقول «اطبخي أكل متوازن» في الفترة دي غير مفيدة عمليًا، لأن المشكلة مش المعرفة — المشكلة الوقت والمجهود.

ود. رنا بتختار أكل الفترة دي بأربع صفات، وكلها عملية: يكون بسيط، ويكون متوفر، ويكون فيه بروتين، وينفع يتاكل في يوم صعب. أي حاجة مش مستوفية الأربعة دول مش هتتعمل، مهما كانت صح على الورق.

والحاجات اللي بتشتغل هي اللي محتاجة أقل مجهود ممكن:

أكل جاهز بلا تحضير: بيض مسلوق، جبنة قريش، زبادي، تونة، فاكهة، مكسرات، عيش بلدي.

أكل بيتعمل مرة وياكل مرات: شوربة عدس، فول، أرز، فراخ مسلوقة أو في الفرن. الحلة الكبيرة بتوفر أيام.

والاستفادة من الزيارات: الناس بتيجي وبتسأل «عايزة حاجة؟». الإجابة الصح مش «متشكرة» — الإجابة الصح إن حد يجيب أكل جاهز أو يقعد ساعة مع الطفل عشان الأم تاكل وتنام.

والمية جنبها في المكان اللي بترضّع فيه. العطش في الرضاعة حقيقي، والنسيان أكتر حاجة بتحصل.

## خرافات بتتكرر في الشهور دي

«الرجيم من أول يوم عشان الوزن يرجع». الفترة دي فترة تعافي واحتياج مرتفع ورضاعة. تقييد الأكل بشدة في وقت زي ده بيزوّد الإرهاق وبيأثر على تجهيز الأم لنفسها. الوزن موضوع بيتاخد بعدين ومع حد شايف الحالة.

«الحلبة والمغات والمشروبات بتزوّد اللبن». دي مشروبات تقليدية في مصر وليها مكانة اجتماعية حقيقية. الصورة العلمية عن كونها بتزوّد اللبن لسه محدودة. اللي بيزوّد اللبن فعلًا هو تكرار الرضاعة والتفريغ الجيد. المشروب مش مشكلة في حد ذاته لمعظم الناس — المشكلة لو اتعامل معاه كبديل عن الحاجة اللي بتشتغل.

«متاكليش حاجة ساقعة». مفيش أساس لده. الأكل البارد مش بيأثر على اللبن ولا على التعافي.

«الحزام بيرجّع البطن». الحزام ملهوش علاقة بالأكل ولا بتركيب الجسم، وفي حالات بيسبب إزعاج. أي استخدام له بعد ولادة قيصرية بيتسأل فيه الطبيب.

## في السياق المصري

الفترة دي عندنا اجتماعية بشكل كثيف. الزيارات بتبدأ بدري، والسبوع، والضيوف، والأم غالبًا بتبقى هي اللي بتقدّم وبتضيّف وهي أصلًا مش قادرة.

والضغط بيبقى في اتجاهين متناقضين في نفس الوقت: «كلي عشان اللبن» من ناحية، و«إمتى هترجعي زي الأول؟» من الناحية التانية. الاتنين بيتقالوا بحب، والاتنين بيحطوا حمل على واحدة نايمة ٣ ساعات متقطعة.

وأكل النفاس التقليدي — البلح والمكسرات والمشروبات الساخنة — أكل كويس في معظمه. المشكلة مش فيه؛ المشكلة لما يبقى هو كل الأكل، أو لما يحل محل وجبة فيها بروتين وخضار.

## اللي بيتهمل، وبيتهمل في نفس المكان كل مرة

الأم في الشهور دي بتتابع كل حاجة تخص الطفل بدقة: الرضعات، النوم، الوزن، الحفاضات، المواعيد. والحاجة الوحيدة اللي مش بتتسجل ولا بتتحسب هي أكلها هي.

الشكل النمطي إن اليوم بيعدي على قهوة ولقمتين واقفة، وبعدين تيجي وجبة واحدة بالليل لما الطفل ينام. ومش لأنها مش عارفة إن ده مش كفاية — لأن كل مرة بتيجي فيها فرصة تاكل، بيكون في حاجة أهم منها بخمس دقايق.

والنتيجة إن الأعراض اللي بتظهر بعد كده — الإرهاق، الدوخة، صعوبة التركيز — بتتقري على إنها أمومة، مع إن جزء منها ممكن يكون ببساطة إن الجسم ده مش بياخد اللي محتاجه.

## اللي يستاهل تفتكريه

الاحتياج في الرضاعة أعلى منه في الحمل، في الوقت اللي تجهيز الأكل فيه أصعب.

ومخزون الحديد اتسحب منه في الحمل وفي الولادة، والتعب اللي نتيجته بيتقري على إنه أمومة.

والتعب المستمر بعد الولادة له أكتر من سبب محتمل — منها الحديد والغدة الدرقية والحالة النفسية — وكلها بتتقيّم.

ولو كان في سكري حمل، في فحص بعد الولادة موصى بيه وبيتنسى.

وزي ما بتقول د. رنا: «أول ست شهور مش مطلوب منك فيهم (ترجعي زي الأول) بأسرع وقت. المطلوب إنك تتعافي، تتغذي كويس، وتدي جسمك فرصة، وبعدها نشتغل على هدفك خطوة خطوة. البيبي محتاج رعاية، بس أمه محتاجة رعاية هي كمان».

لو حاسة إن التعب أكبر من المتوقع، أو عايزة خطة تنفع في يوم فعلًا مفيهوش وقت: [[specialty:pregnancy-nutrition|تغذية الحمل والرضاعة]] أو [[booking|احجزي موعد]].

وفي مقال عن أسئلة الرضاعة نفسها: [[article:feeding-and-eating-recurring-questions|أسئلة بتتكرر عن الرضاعة وأكل الطفل]].
AR,

                'en' => <<<'EN'
After a birth, every question is about the baby. Is he feeding well? Is he sleeping? Has he gained weight? How many hours did he sleep?

The mother gets asked one question — "are you all right?" — answers "fine, thank God", and carries on.

This article is about the part nobody asks about: a body that has come out of nine months of pregnancy, then a birth, and is now producing milk, sleeping in fragments, and eating last in the house.

## The first question in the consultation is not about weight

Dr Rana says: "The first question I ask her is not 'what do you weigh and what do you want to get to?' — first I want to know how you are."

The things she asks about first:

Was the birth vaginal or caesarean?

How is the recovery going?

How is the feeding working?

How much are you sleeping?

How is your appetite?

Is there anaemia?

And what medicines are you taking?

Seven things, and not a figure on a scale among them. Because those answers are what decide what can usefully be done now and what is too early.

## The requirement rises exactly when the time disappears

This is the paradox that summarises the whole period.

Producing milk costs energy, and the requirement during full breastfeeding is higher than during pregnancy itself. That surprises a great many people, because all the attention, all the guidance and all the caution are concentrated in the nine months before and stop at the birth.

And at exactly the same moment, the capacity to prepare food collapses: broken sleep, no time, absolute priority to the baby, and visitors. The typical result is that a mother eats less, faster and more simply during the period when her requirement is close to the highest of her life.

The World Health Organization has recommendations devoted to maternal and newborn care in the postnatal period, and treats it as a period of care with its own requirements — not as a period that passes.

## Iron: a store drawn on twice

This is the most important point in the article, and its mechanism explains a great deal of exhaustion that gets attributed to the baby.

In pregnancy, the iron requirement rises substantially: blood volume increases, the placenta is built, and the fetus stores iron for itself in the later months. The store is drawn on across nine months.

Then at delivery there is blood loss — normal, and varying in amount with the kind of birth and its circumstances. So the same store is drawn on again, all at once.

The result is that many women come out of a birth with low iron stores, and the symptoms are exhaustion, breathlessness on exertion, pallor and difficulty concentrating — every one of which is read as "of course, she has a new baby".

The WHO has guidance on iron supplementation in postpartum women, which is to say this is treated as a matter in its own right rather than a detail.

And the important part is that it can be measured. Iron stores fall before haemoglobin does, so a blood count alone can be reassuring while the store is empty. There is a separate article on that point: [[article:normal-results-still-tired|my results are normal and I am still exhausted]].

## Exhaustion: what is expected and what needs to be seen

This part has to be said plainly, because "it is all normal tiredness" conceals things that have treatments.

Exhaustion from broken sleep is real and expected, and improves as the baby's sleep improves.

But other things present in the same way:

Iron deficiency, as above.

Postpartum thyroiditis — a condition occurring in the first year after birth, passing through phases, with symptoms including exhaustion, weight change and mood change. It is precisely the kind of thing read as "normal after a baby", and there is specialist guidance on managing thyroid disease in pregnancy and the postpartum period.

And postnatal depression, whose symptoms are not always visible sadness — they can be exhaustion, loss of interest, and changes in eating and sleeping. Specialist postnatal care guidance recommends asking about mental health as part of follow-up, rather than treating it as a separate subject.

So "I am exhausted" after a birth is a sentence with more than one possible cause, and some of those causes have treatments.

## Signs that are not explained by lack of sleep

This is the most important passage in this article, and it should be read even if nothing else is.

These are the signs Dr Rana says call for urgent medical assessment:

> If there is:
> - severe or worsening exhaustion
> - dizziness
> - palpitations
> - shortness of breath
> - fever
> - unusual bleeding
> - severe pain
> - severe headache
> - or a feeling that you are not well in yourself and cannot cope
> these are seen by a doctor, and not deferred until the sleep improves.

And the sentence she says after it: "It is very important that we don't use lack of sleep as a ready-made answer for every symptom after a birth."

The World Health Organization, in its recommendations on maternal and newborn care in the postnatal period, treats postnatal danger signs as something a mother should know and know how to act on — not something that waits for the next appointment.

And the last item on that list is like any other item on it. Feeling that you are not well in yourself and cannot cope is entirely reason enough to speak to somebody and ask for help — without waiting for it to become "enough".

## Gestational diabetes: the screening that gets forgotten

This point can change an entire health trajectory, and is rarely said.

A woman who had gestational diabetes is at higher risk of developing type 2 diabetes later. The American Diabetes Association, in its annual Standards of Care, recommends glucose testing at a defined interval after delivery for women who had gestational diabetes, and periodic screening thereafter for life.

What happens in practice is that gestational diabetes is treated as something that ended at delivery. The woman leaves hospital and the matter is closed in everybody's mind.

So the question to ask is: I had gestational diabetes — when should the test be done? And how often after that?

## Calcium and bone in breastfeeding

There is a recognised change in bone mass during breastfeeding, which the body recovers after weaning under normal conditions. So this is a temporary and expected change, not permanent harm.

But that assumes calcium and vitamin D intake are adequate. And vitamin D deserves particular attention in Egypt: a country with strong sun where deficiency is nonetheless present, and the reason is not the sun but exposure. A mother in the first months in particular spends longer indoors.

## After a caesarean — some additional notes

A caesarean is major surgery, and recovery from it has slightly different requirements.

Wound healing needs adequate protein, and protein is the first thing to fall when eating becomes "whatever is quick". Constipation is common after surgery because of reduced movement and some pain medication, and fibre and fluids have a part in that.

And blood loss at a caesarean is on average higher, which returns us to the iron point.

Early gentle movement is determined by the doctor following the case, not by general advice.

## Breastfeeding and weight — the sentence that gets said wrongly

"Breastfeeding makes you lose weight" is said constantly and creates inaccurate expectations.

Producing milk does cost energy, and that is true. But what happens to any individual woman varies: some lose weight during this period, some stay the same, and some gain — and the reasons include sleep, movement, the shape of eating, hormonal state and other things.

What is not helpful is a woman measuring herself against an expectation given to her with no basis, and concluding that something is wrong with her when it does not happen.

Weight after a birth is a subject taken up in its own time, calmly, with somebody looking at the case and the breastfeeding together.

## Eating when there is no time — the practical part

Advice that says "cook balanced meals" is practically useless in this period, because the problem is not knowledge. The problem is time and effort.

Dr Rana chooses food for this period by four tests, all of them practical: that it is simple, that it is available, that it contains protein, and that it can actually be eaten on a hard day. Anything failing one of the four will not get made, however correct it looks on paper.

And what works is whatever requires the least possible effort:

Food ready with no preparation: boiled eggs, areesh cheese, yoghurt, tuna, fruit, nuts, baladi bread.

Food cooked once and eaten several times: lentil soup, foul, rice, boiled or roast chicken. A big pot buys days.

And using the visits. People come and ask "do you need anything?". The right answer is not "no thank you" — the right answer is for somebody to bring cooked food, or sit with the baby for an hour so the mother can eat and sleep.

And water beside wherever the feeding happens. Thirst during breastfeeding is real, and forgetting is what actually happens.

## Myths that recur in these months

"Diet from day one so the weight comes off." This is a period of recovery, high requirement and breastfeeding. Severe restriction at such a time increases exhaustion and undermines a mother's capacity to feed herself at all. Weight is a subject for later, and with somebody who has seen the case.

"Fenugreek and moghat and these drinks increase milk." These are traditional in Egypt and carry genuine social meaning. The scientific picture on whether they increase milk remains limited. What does increase milk is frequent feeding and effective emptying. The drink is not a problem in itself for most people — the problem is if it is treated as a substitute for the thing that works.

"Do not eat anything cold." There is no basis for this. Cold food does not affect milk or recovery.

"A binder brings the stomach back." A binder has nothing to do with food or body composition, and in some cases causes discomfort. Any use after a caesarean is a question for the doctor.

## In the Egyptian context

This period here is intensely social. Visits begin early, there is the sebou', there are guests, and the mother is often the one serving and hosting while barely able to stand.

And the pressure runs in two contradictory directions at once: "eat, for the milk" on one side, and "when are you going back to how you were?" on the other. Both are said with affection, and both load something onto somebody sleeping three broken hours.

Traditional postpartum food — dates, nuts, hot drinks — is mostly good food. The problem is not the food; the problem is when it becomes all the food, or replaces a meal containing protein and vegetables.

## What gets neglected, and it is neglected in the same place every time

A mother in these months follows everything about the baby precisely: the feeds, the sleep, the weight, the nappies, the appointments. The one thing that is neither recorded nor counted is her own eating.

The typical shape is a day that goes by on a coffee and two mouthfuls taken standing up, and then one meal at night once the baby is asleep. And not because she does not know that is not enough — because every time an opportunity to eat arrives, there is something five minutes more urgent than it.

The result is that the symptoms which show up afterwards — exhaustion, dizziness, difficulty concentrating — get read as motherhood, when part of it may simply be that this body is not getting what it needs.

## Worth remembering

The requirement during breastfeeding is higher than in pregnancy, at the point when preparing food is hardest.

The iron store was drawn on in pregnancy and again at delivery, and the exhaustion that follows gets read as motherhood.

Persistent exhaustion after a birth has more than one possible cause — iron, thyroid and mental health among them — and all of them can be assessed.

And if there was gestational diabetes, there is a recommended test after delivery that gets forgotten.

As Dr Rana puts it: "In the first six months you are not being asked to 'go back to how you were' as fast as possible. What is being asked is that you recover, that you eat well, and that you give your body a chance — and after that we work on your goal step by step. The baby needs looking after, but his mother needs looking after too."

If the exhaustion feels larger than expected, or you want a plan that works in a day that genuinely has no time in it: [[specialty:pregnancy-nutrition|pregnancy and breastfeeding nutrition]] or [[booking|book an appointment]].

And there is an article on breastfeeding questions themselves: [[article:feeding-and-eating-recurring-questions|questions that keep coming up about feeding]].
EN,
            ],

            'citations' => [
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => [
                        'ar' => 'توصيات منظمة الصحة العالمية بشأن رعاية الأم والمولود لتجربة إيجابية بعد الولادة',
                        'en' => 'WHO recommendations on maternal and newborn care for a positive postnatal experience',
                    ],
                    'year' => 2022,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'THIS ONE CARRIES THE DANGER-SIGNS SENTENCE AND IS THE MOST CONSEQUENTIAL '
                        .'CITATION ON THE BLOG. Three claims rest on it: (1) it treats the postnatal period '
                        .'as a period of care with its own requirements; (2) postnatal guidance recommends '
                        .'asking about mental health as part of follow-up; and (3) — the new one — that it '
                        .'treats postnatal danger signs as something a mother should know and know how to '
                        .'act on rather than something that waits for the next appointment. Claim (3) is '
                        .'load-bearing: it is what tells a reader with a fever or unusual bleeding not to '
                        .'wait. VERIFY IT FIRST AND VERIFY IT PROPERLY — confirm the exact title and year, '
                        .'confirm that danger-sign counselling is a stated recommendation, and confirm that '
                        .'the signs the article lists are among those it names. Confidence is MEDIUM and '
                        .'stays medium until somebody has the document open: the recollection is that WHO '
                        .'issued postnatal care recommendations around 2022 and that danger-sign counselling '
                        .'is in them, which is not the same as having read it. If the document does not '
                        .'support the claim, the sentence goes and the practitioner\'s list stays — her list '
                        .'does not depend on it.',
                ],
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => [
                        'ar' => 'إرشادات: مكملات الحديد للنساء بعد الولادة',
                        'en' => 'Guideline: Iron supplementation in postpartum women',
                    ],
                    'year' => 2016,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "the WHO has guidance on iron supplementation in postpartum women". '
                        .'Cited only for the existence of dedicated guidance, not for any dose or duration — '
                        .'the article gives neither. Confirm the title and year.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الجمعية الأمريكية للسكري (ADA)',
                        'en' => 'American Diabetes Association (ADA)',
                    ],
                    'title' => ['ar' => 'معايير الرعاية في السكري', 'en' => 'Standards of Care in Diabetes'],
                    'year' => 2025,
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports the gestational diabetes section: glucose testing at a defined '
                        .'interval after delivery and periodic lifelong screening thereafter. THE ARTICLE '
                        .'DELIBERATELY GIVES NO INTERVAL — the ADA states one, and it should be added here '
                        .'from the current edition rather than from memory. Reissued annually; update the '
                        .'year. This is the most actionable recommendation in the article.',
                ],
                [
                    'organisation' => [
                        'ar' => 'إرشادات متخصصة في أمراض الغدة الدرقية في الحمل وبعد الولادة',
                        'en' => 'Specialist guidance on thyroid disease in pregnancy and the postpartum period',
                    ],
                    'title' => [
                        'ar' => 'إرشادات تشخيص وإدارة أمراض الغدة الدرقية في الحمل وما بعد الولادة',
                        'en' => 'Guidelines for the diagnosis and management of thyroid disease during pregnancy and the postpartum',
                    ],
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the postpartum thyroiditis paragraph. NO ISSUING BODY IS NAMED '
                        .'because the draft was not confident which — the American Thyroid Association and '
                        .'the European Thyroid Association have both published in this area. Name the actual '
                        .'body and year at verification, or cut the paragraph: a clinical condition '
                        .'described without a traceable source is the weakest thing in this article.',
                ],
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => [
                        'ar' => 'صحيفة وقائع: تغذية الرضع وصغار الأطفال',
                        'en' => 'Fact sheet: Infant and young child feeding',
                    ],
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports the framing of full breastfeeding and, with it, the claim that '
                        .'lactation carries an energy cost above pregnancy. If this fact sheet does not '
                        .'state the energy comparison explicitly, find the source that does — the comparison '
                        .'opens the article and carries its argument.',
                ],
            ],
        ];
    }
}
