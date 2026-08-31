<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * Breastfeeding and first foods, answered as questions rather than as rules.
 *
 * The honey section is the one that could prevent actual harm: giving honey to
 * a small baby is common here, is done affectionately, and infant botulism is
 * the reason guidance sets a hard line at twelve months. It is stated plainly
 * and cited rather than softened.
 *
 * The supply-and-demand mechanism is the other section carrying real weight,
 * because "my milk is not enough" is the sentence behind most early weaning,
 * and understanding why supply follows removal changes what somebody does next.
 */
class FeedingAndEatingRecurringQuestions extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'feeding-and-eating-recurring-questions',
            'category' => 'pregnancy-nutrition',
            'tags' => ['questions-to-ask', 'family'],
            'cover' => 'infant-feeding-hands',

            'title' => [
                'ar' => 'أسئلة بتتكرر عن الرضاعة وأكل الطفل',
                'en' => 'Questions that keep coming up about feeding',
            ],

            'excerpt' => [
                'ar' => 'أكتر الأسئلة اللي بتتكرر في الشهور الأولى، والإجابات اللي ليها مصدر منشور.',
                'en' => 'The questions that come up most in the first months, and the answers that have a published source.',
            ],

            'body' => [
                'ar' => <<<'AR'
الشهور الأولى فيها أسئلة بتتكرر بنفس الشكل تقريبًا عند كل الأمهات، وفيها كلام بيتقال من كل حد.

المقال ده بيمشي على الأسئلة دي واحد واحد، وبيقول اللي التوصيات المنشورة بتقوله، واللي لسه مش واضح، واللي مالوش أساس.

## «لبني مش كفاية» — أشهر جملة، وأكترها بتنتهي بقرار

دي الجملة اللي وراها معظم قرارات الفطام المبكر، وغالبًا بتتقال في وقت اللبن فيه بيبقى بيتظبط.

الميكانيكية تستاهل تتشرح، لأن فهمها بيغيّر القرار.

إنتاج اللبن بيشتغل بالعرض والطلب. اللبن اللي بيتشال من الثدي بيبعت إشارة لإنتاج كمية أكبر؛ اللبن اللي بيفضل مكانه بيبعت الإشارة العكسية. يعني الرضاعة المتكررة والتفريغ الجيد هما اللي بيرفعوا الإنتاج، مش الأكل ولا المشروبات.

وده بيفسّر ليه إضافة رضعة صناعية بسبب القلق من قلة اللبن بتقلل اللبن فعلًا: الطفل بيرضع أقل، فالثدي بيتفرغ أقل، فالإشارة بتقل — ودي حلقة بتأكد الخوف اللي بدأت بيه.

وفي حاجات بتتقري غلط على إنها قلة لبن: إن الطفل بيرضع كتير (ده طبيعي، لبن الأم بيتهضم بسرعة)، أو إن الثدي بقى «مش مليان» (ده بيحصل مع استقرار الإنتاج)، أو إن الطفل بيعيط (العياط له أسباب كتير).

والمؤشر اللي بيتابع فعلًا مش إحساس الأم — هو نمو الطفل وعدد الحفاضات، وده بيتقيّم عند طبيب الأطفال.

CLINICAL_INPUT: إمتى بتحوّلي أم بتشتكي من قلة اللبن، وإمتى بتطمنيها؟

## «أكلي بيأثر على اللبن إزاي؟»

الإجابة الأمينة إن التأثير أقل مما الناس بتتخيل في حاجات، وأكبر في حاجات تانية.

تركيب لبن الأم بيتحافظ عليه إلى حد كبير حتى لو أكل الأم مش مثالي — الجسم بيسحب من مخزون الأم عشان يحافظ على اللبن. ودي معلومة ليها وجهين: اللبن بيفضل مغذي، بس على حساب مخزون الأم، وده اللي بيخلي تغذية الأم في الرضاعة موضوع مهم لصحتها هي.

وفي عناصر معيّنة مستواها في اللبن بيتأثر فعلًا بأكل الأم — منها بعض الفيتامينات. وده بيتابع إكلينيكيًا حسب الحالة.

## «المرضعة محتاجة تاكل أكتر؟»

آه، وده مختلف عن الحمل.

إنتاج اللبن نفسه بيستهلك طاقة، والاحتياج في الرضاعة الكاملة بيبقى أعلى منه في الحمل. ودي معلومة بتتفاجئ بيها ناس كتير، لأن الاهتمام كله بيبقى مركز على فترة الحمل.

المشكلة العملية إن الفترة دي بالذات هي الأصعب في تجهيز الأكل: نوم متقطع، وقت مش موجود، وأولوية للطفل. النتيجة إن كتير من الأمهات بياكلوا أقل وأسرع وأضعف في الوقت اللي احتياجهم فيه أعلى.

في مقال مخصص للنقطة دي: [[article:postpartum-nutrition|التغذية بعد الولادة — للأم في أول ستة شهور]].

## «أشرب مية كتير عشان اللبن يزيد»

الترطيب مهم، والمرضعة بتحس بعطش أكتر وده طبيعي.

بس شرب كميات أكبر من اللي الجسم محتاجه مش بيزوّد إنتاج اللبن. الإنتاج بيتحدد بالطلب زي ما اتقال فوق. شرب المية حسب العطش كافي، والإفراط مش بيضيف.

الخرافة دي غير ضارة في معظم الأحوال، بس هي بتاخد الانتباه من الحاجة اللي فعلًا بتفرق: عدد الرضعات والتفريغ.

## «الحلبة بتزوّد اللبن»

هنا لازم نبقى دقيقين.

الحلبة وأعشاب تانية بتتستخدم من زمان في مصر وفي بلاد كتير كمدرات للبن. في دراسات بصت على بعضها، والصورة العامة لسه محدودة ومش كافية عشان تتقال كتوصية.

يعني: مش خرافة بالمعنى الكامل، ومش حاجة مثبتة. والفرق المهم إن الاعتماد عليها بدل الحاجة اللي بتشتغل فعلًا — الرضاعة المتكررة والتفريغ الجيد — هو اللي ممكن يضر.

وأي عشب بيتاخد بانتظام يتقال للطبيب، لأن «طبيعي» مش نفس «بدون تأثير».

## «الطفل بيمغص، أمنع أكل معيّن؟»

المغص في الشهور الأولى شائع جدًا وله أسباب متعددة، وكتير منها مش ليه علاقة بأكل الأم.

منع الأم لمجموعات أكل كاملة — الألبان، البقوليات، الخضار الكرنبي — بيحصل كتير وبقرار من العيلة، والنتيجة غالبًا إن أكل الأم بيبقى أفقر في وقت احتياجها أعلى، من غير ما المغص يتحسن.

في حالات حقيقية فيها حساسية أو عدم تحمل عند الرضيع، وبتتشخص إكلينيكيًا وبتتابع، ومش بتتحدد بالتجربة والخطأ في البيت.

CLINICAL_INPUT: إمتى بيبقى في داعي فعلًا لاستبعاد صنف من أكل الأم المرضعة؟

## «أبدأ أكل الطفل إمتى؟»

منظمة الصحة العالمية بتوصي بالرضاعة الطبيعية الحصرية في الستة شهور الأولى، وبعدها إدخال أطعمة تكميلية مناسبة مع استمرار الرضاعة الطبيعية لحد سنتين أو أكتر.

والأكاديمية الأمريكية لطب الأطفال (AAP) في بيان سياستها عن الرضاعة الطبيعية بتوصي بنفس الاتجاه، وبتدعم استمرار الرضاعة مع إدخال الأطعمة التكميلية.

يعني الستة شهور مش رقم عشوائي، وإدخال أكل قبلها مش «بيقوّي الطفل» — الجهاز الهضمي والقدرة على البلع بيبقوا لسه بيتطوروا.

## «أبدأ بإيه؟»

منظمة الصحة العالمية عندها إرشادات مخصصة للتغذية التكميلية للرضع والأطفال الصغار، وبتشدد على نقطة عملية: الأطعمة التكميلية لازم تكون غنية بالعناصر اللي احتياج الطفل منها بيزيد في المرحلة دي — والحديد على رأسها، لأن مخزون الطفل من الحديد بيكون بدأ يقل بعد الستة شهور.

والأكل المصري فيه اختيارات كويسة هنا: العدس المسلوق المهروس، صفار البيض، اللحمة أو الفراخ المفرومة ناعم، والخضار المهروس. ودي أكل بيتعمل في البيت، مش منتجات.

## «الرضاعة بتمنع الحمل» و«الدورة رجعت يبقى اللبن بطّل ينفع»

جملتين شائعتين والاتنين محتاجين تدقيق.

الرضاعة بتأخر رجوع الخصوبة عند كتير من الستات، لكن ده بيعتمد على شروط محددة — رضاعة حصرية ومتكررة، وسن معيّن للطفل، وعدم رجوع الدورة — والاعتماد عليها كوسيلة منع حمل من غير الشروط دي بيؤدي لحمل غير متوقع. الموضوع ده بيتناقش مع الطبيب، مش بيتفترض.

ورجوع الدورة مش بيخلي اللبن «مش نافع». اللبن بيفضل مغذي، وفي ستات بيلاحظوا تغيّر مؤقت في الكمية حوالين الدورة، وده بيعدي.

## إدخال الأصناف اللي بتسبب حساسية

ده مجال اتغير فيه الكلام في السنين الأخيرة، ويستاهل يتقال.

الفكرة القديمة كانت تأجيل الأطعمة المرتبطة بالحساسية — البيض والفول السوداني والسمك — لسن متأخر. الأبحاث اللي اتعملت بعدين غيّرت الاتجاه ده، والإرشادات الحديثة بقت بتتكلم عن إدخال الأصناف دي في وقتها مع باقي الأطعمة التكميلية بدل تأجيلها، لأن التأجيل مش بيقلل الحساسية وممكن يكون العكس.

والاستثناء الواضح: الطفل اللي عنده حساسية معروفة أو تاريخ عائلي قوي بيتعامل معاه بشكل فردي عند طبيب الأطفال.

CLINICAL_INPUT: إيه اللي بتقوليه لأم قلقانة من إدخال البيض أو المكسرات للطفل؟

## العسل قبل السنة — القاعدة الوحيدة اللي مالهاش استثناء هنا

دي أهم نقطة في المقال كله.

العسل ممكن يحتوي على جراثيم بكتيريا معيّنة، والرضيع تحت سنة جهازه الهضمي لسه مش قادر يمنعها من النمو، وده ممكن يسبب حالة اسمها تسمم الرضع الوشيقي (البوتيوليزم). عشان كده الإرشادات الصحية بتنص على عدم إعطاء العسل لأي طفل أقل من ١٢ شهر — بأي كمية، وبأي شكل، ومهما كان العسل نقي أو بلدي.

والنقطة دي محتاجة تتقال بوضوح لأن العسل بيتقدم للأطفال الصغيرين في مصر بحب: على اللهاية، مع الأعشاب، أو كـ«حاجة حلوة». النية طيبة والخطر حقيقي.

وحاجة عملية: الأكل بيتقدم مهروس في الأول وبيخشن تدريجيًا مع الوقت. الطفل اللي بيفضل على المهروس فترة طويلة بيتأخر في تعلّم المضغ، وده بيظهر بعدين على شكل رفض للأكل الصلب.

## في السياق المصري: الينسون والكراوية والشاي

الأعشاب بتتقدم للرضع للمغص بشكل شبه روتيني. المعلومة اللي تستاهل تتعرف إن الرضيع تحت ستة شهور مش محتاج أي سوائل غير اللبن — ولا مية ولا أعشاب — والسوائل دي بتاخد من مساحة معدته الصغيرة وبتقلل الرضاعة.

والشاي بالذات نقطة مهمة: بيتقدم للأطفال في بيوت كتير، وفيه مركبات بتقلل امتصاص الحديد. والطفل في السنة الأولى والتانية هو بالظبط في المرحلة اللي احتياجه للحديد فيها عالي ومخزونه بيقل. يعني العادة دي بتشتغل ضد أهم حاجة في المرحلة دي.

وبدل السكر والعسل في المشروبات، الأكل نفسه بيقدم الحلاوة الطبيعية: الموز، البلح المهروس بعد السنة، والفاكهة الموسمية.

PRACTITIONER_VOICE: إيه أكتر نصيحة عن الرضاعة بتوصلك من العيلة وبتضطري تصححيها؟

## اللي يستاهل تفتكريه

إنتاج اللبن بيشتغل بالعرض والطلب، فالرضاعة المتكررة والتفريغ هما اللي بيرفعوا الإنتاج مش الأكل ولا الشرب.

المرضعة احتياجها من الطاقة أعلى من الحامل، في وقت تجهيز الأكل فيه أصعب. والمغص نادرًا ما بيتحل بمنع أصناف من أكل الأم.

الستة شهور رضاعة حصرية توصية من منظمة الصحة العالمية ومن الأكاديمية الأمريكية لطب الأطفال، والأطعمة التكميلية بعدها لازم تكون غنية بالحديد.

والعسل قبل السنة لأ — دي الحاجة الوحيدة في المقال ده اللي مالهاش استثناء.

لو عايزة متابعة في الرضاعة أو في إدخال الأكل: [[specialty:pregnancy-nutrition|تغذية الحمل والرضاعة]] أو [[booking|احجزي موعد]].

وفي مقال عن الطفل اللي بيرفض الأكل بعدين: [[article:the-child-who-will-not-eat|الطفل اللي مش بياكل]].
AR,

                'en' => <<<'EN'
The first months bring questions that recur in almost identical form for every mother, and advice that arrives from everybody.

This article works through those questions one at a time, and says what published guidance states, what remains unclear, and what has no basis.

## "My milk is not enough" — the most common sentence, and the one that usually ends in a decision

This is the sentence behind most early weaning decisions, and it is usually said at exactly the point when supply is still settling.

The mechanism is worth explaining, because understanding it changes the decision.

Milk production works on supply and demand. Milk removed from the breast signals for more to be produced; milk left in place sends the opposite signal. So frequent feeding and effective emptying are what raise production — not food and not drinks.

Which explains why adding a formula feed out of anxiety about supply actually reduces supply: the baby feeds less, the breast empties less, the signal falls — a loop that confirms the fear it started from.

And several things get misread as low supply: that the baby feeds often (normal — breast milk digests quickly), that the breast no longer feels full (this happens as production settles), or that the baby cries (crying has many causes).

The indicator actually followed is not the mother's impression — it is the baby's growth and nappy output, assessed by a paediatrician.

CLINICAL_INPUT: When do you refer a mother worried about low supply, and when do you reassure her?

## "How does what I eat affect my milk?"

The honest answer is that the effect is smaller than people imagine in some respects and larger in others.

The composition of breast milk is largely protected even when the mother's diet is not ideal — the body draws on her stores to maintain it. That has two faces: the milk stays nourishing, but at the expense of the mother's own reserves, which is precisely what makes a breastfeeding mother's own nutrition a matter for her health.

Some specific nutrients do vary in milk according to the mother's intake, certain vitamins among them, and that is followed clinically according to the case.

## "Does a breastfeeding mother need to eat more?"

Yes — and this differs from pregnancy.

Producing milk itself costs energy, and the requirement during full breastfeeding is higher than in pregnancy. That surprises a great many people, because all the attention is concentrated on the pregnancy itself.

The practical problem is that this period is the hardest for preparing food: broken sleep, no time, and the baby first. The result is that many mothers eat less, faster and more poorly at exactly the point when their requirement is highest.

There is an article devoted to this: [[article:postpartum-nutrition|Postpartum nutrition — the mother in the first six months]].

## "Should I drink a lot of water so my milk increases?"

Hydration matters, and a breastfeeding mother feels thirstier, which is normal.

But drinking beyond what the body needs does not increase milk production. Production is determined by demand, as above. Drinking to thirst is sufficient; exceeding it adds nothing.

This myth is mostly harmless, but it draws attention away from the thing that genuinely matters: the number of feeds and effective emptying.

## "Fenugreek increases milk"

Here we need to be precise.

Fenugreek and other herbs have long been used in Egypt and in many countries as galactagogues. Some have been studied, and the overall picture remains limited and not sufficient to be stated as a recommendation.

So: not a myth in the full sense, and not an established fact. The important distinction is that relying on them instead of the thing that does work — frequent feeding and effective emptying — is where harm can come in.

And any herb taken regularly should be mentioned to a doctor, because "natural" is not the same as "without effect".

## "The baby has colic — should I cut something out?"

Colic in the early months is very common, with multiple causes, many of which have nothing to do with the mother's food.

Mothers excluding whole food groups — dairy, legumes, brassicas — happens constantly and often by family decision, and the usual result is that the mother's food becomes poorer at the point when her requirement is highest, without the colic improving.

There are genuine cases of allergy or intolerance in an infant, and those are diagnosed clinically and followed, rather than determined by trial and error at home.

CLINICAL_INPUT: When is there genuinely a reason to exclude something from a breastfeeding mother's diet?

## "When do I start solid food?"

The World Health Organization recommends exclusive breastfeeding for the first six months, and thereafter the introduction of appropriate complementary foods alongside continued breastfeeding to two years or beyond.

The American Academy of Pediatrics, in its policy statement on breastfeeding, recommends in the same direction and supports continued breastfeeding alongside the introduction of complementary foods.

So six months is not an arbitrary number, and introducing food earlier does not "strengthen" a baby — the digestive system and the ability to swallow are still developing.

## "What do I start with?"

The WHO has guidance devoted to complementary feeding for infants and young children, and it stresses a practical point: complementary foods must be rich in the nutrients whose requirement rises at this stage — iron above all, because a baby's own iron store begins to fall after six months.

Egyptian food offers good options here: boiled mashed lentils, egg yolk, finely minced meat or chicken, and mashed vegetables. These are things cooked at home, not products.

## "Breastfeeding prevents pregnancy" and "my period came back so my milk is no good"

Two common sentences, both needing care.

Breastfeeding does delay the return of fertility in many women, but that depends on specific conditions — exclusive and frequent feeding, a particular age of baby, and periods not having returned — and relying on it as contraception without those conditions leads to unexpected pregnancies. This is discussed with a doctor rather than assumed.

And the return of periods does not make milk "no good". The milk remains nourishing, and some women notice a temporary change in volume around a period, which passes.

## Introducing the foods associated with allergy

This is an area where the advice changed in recent years, and that is worth saying.

The older idea was to delay foods associated with allergy — egg, peanut, fish — until later. Research done subsequently changed that direction, and current guidance discusses introducing these foods in their time alongside other complementary foods rather than postponing them, because postponing does not reduce allergy and may do the opposite.

The clear exception: a child with a known allergy or a strong family history is handled individually by a paediatrician.

CLINICAL_INPUT: What do you say to a mother anxious about introducing egg or nuts to her child?

## Honey before one year — the one rule in this article with no exception

This is the most important point in the whole piece.

Honey may contain spores of a particular bacterium, and an infant under one year has a digestive system not yet able to prevent them growing, which can cause a condition called infant botulism. Health guidance therefore states that honey should not be given to any child under twelve months — in any amount, in any form, and however pure or local the honey is.

This needs saying plainly, because honey is offered to small babies in Egypt out of affection: on a dummy, with herbs, or as "something sweet". The intention is kind and the risk is real.

And a practical point: food starts mashed and coarsens gradually over time. A child kept on purées for a long stretch is slower to learn to chew, and that shows up later as a refusal of solid food.

## In the Egyptian context: aniseed, caraway and tea

Herbs are offered to infants for colic almost as a routine. What is worth knowing is that a baby under six months needs no fluids other than milk — not water and not herbal drinks — and those fluids take up space in a very small stomach and reduce feeding.

Tea in particular is an important point: it is given to children in a great many households, and it contains compounds that reduce iron absorption. And a child in the first and second year is precisely at the stage where iron requirement is high and the store is falling. So the habit works against the single most important thing at that stage.

And instead of sugar and honey in drinks, food itself provides natural sweetness: banana, mashed dates after the first year, and seasonal fruit.

PRACTITIONER_VOICE: What is the breastfeeding advice that reaches you most often from the family and that you find yourself correcting?

## Worth remembering

Milk production works on supply and demand, so frequent feeding and emptying are what raise it — not food and not drink.

A breastfeeding mother's energy requirement is higher than a pregnant woman's, at the point when preparing food is hardest. And colic is rarely resolved by excluding things from a mother's diet.

Six months of exclusive breastfeeding is a recommendation of both the WHO and the American Academy of Pediatrics, and complementary foods after it need to be rich in iron.

And no honey before one year — the one thing in this article with no exception.

If you want follow-up on breastfeeding or on introducing food: [[specialty:pregnancy-nutrition|pregnancy and breastfeeding nutrition]] or [[booking|book an appointment]].

And there is an article on the child who refuses food later on: [[article:the-child-who-will-not-eat|the child who will not eat]].
EN,
            ],

            'citations' => [
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => [
                        'ar' => 'صحيفة وقائع: تغذية الرضع وصغار الأطفال',
                        'en' => 'Fact sheet: Infant and young child feeding',
                    ],
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports "exclusive breastfeeding for the first six months, then complementary '
                        .'foods alongside continued breastfeeding to two years or beyond". Long-standing WHO '
                        .'position. Record the revision date current at verification.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الأكاديمية الأمريكية لطب الأطفال (AAP)',
                        'en' => 'American Academy of Pediatrics (AAP)',
                    ],
                    'title' => [
                        'ar' => 'الرضاعة الطبيعية واستخدام لبن الأم — بيان سياسة',
                        'en' => 'Breastfeeding and the Use of Human Milk — policy statement',
                    ],
                    'year' => 2022,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "the AAP recommends in the same direction and supports continued '
                        .'breastfeeding alongside complementary foods". Confident the policy statement '
                        .'exists and was updated around this year; confirm the year and how far its '
                        .'continued-breastfeeding recommendation extends, since the 2022 revision changed '
                        .'that specifically.',
                ],
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => [
                        'ar' => 'إرشادات بشأن التغذية التكميلية للرضع وصغار الأطفال',
                        'en' => 'Guideline for complementary feeding of infants and young children',
                    ],
                    'year' => 2023,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "complementary foods must be rich in the nutrients whose requirement '
                        .'rises at this stage, iron above all". Confident WHO guidance on complementary '
                        .'feeding exists; confirm the title and year, and that iron is named as a priority '
                        .'nutrient rather than being an inference.',
                ],
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => ['ar' => 'صحيفة وقائع: التسمم الوشيقي', 'en' => 'Fact sheet: Botulism'],
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports the honey section — the single most consequential statement in this '
                        .'article, since acting on it could prevent harm and ignoring it could cause some. '
                        .'Confirm that the WHO source states the under-twelve-months line for honey '
                        .'specifically; if it does not, cite whichever national guidance does rather than '
                        .'leaving the strongest claim on the weakest reference.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الأكاديمية الأمريكية لطب الأطفال (AAP)',
                        'en' => 'American Academy of Pediatrics (AAP)',
                    ],
                    'title' => [
                        'ar' => 'إرشادات حول توقيت إدخال الأطعمة المرتبطة بالحساسية',
                        'en' => 'Guidance on the timing of introducing allergenic foods',
                    ],
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "current guidance discusses introducing these foods in their time '
                        .'rather than postponing them, because postponing does not reduce allergy and may do '
                        .'the opposite". Confident the direction of guidance changed and that the AAP '
                        .'reflects it; NO YEAR is given because this has been revised more than once. '
                        .'Confirm the current document — and note that the strongest evidence concerns '
                        .'peanut specifically, so if the guidance is narrower than the article implies, the '
                        .'paragraph must narrow with it.',
                ],
            ],
        ];
    }
}
