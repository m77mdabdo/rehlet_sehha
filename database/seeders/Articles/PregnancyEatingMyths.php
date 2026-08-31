<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * The article with the highest ratio of folk belief to published evidence, and
 * therefore the one where naming the source matters most.
 *
 * Two sections here are worth more than the rest put together because they are
 * specifically Egyptian and specifically consequential: liver, which is eaten
 * constantly here and which guidance advises against in pregnancy because of
 * preformed vitamin A, and fesikh, which is eaten by whole families on one
 * particular day of the year. Neither appears in a translated foreign article,
 * and both are the kind of thing somebody's mother-in-law has an opinion about.
 */
class PregnancyEatingMyths extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'pregnancy-eating-myths',
            'category' => 'pregnancy-nutrition',
            'tags' => ['myths', 'family'],
            'cover' => 'pregnancy-bump',

            'title' => [
                'ar' => 'خرافات الأكل في الحمل — والكلام اللي ليه مصدر',
                'en' => 'Pregnancy eating myths — and the advice that has a source',
            ],

            'excerpt' => [
                'ar' => 'الحامل بيوصلها كلام من كل حد. ده اللي التوصيات المنشورة بتقوله فعلًا، واللي مالوش أصل.',
                'en' => 'Everybody has advice for a pregnant woman. Here is what published guidance actually says, and what has no basis.',
            ],

            'body' => [
                'ar' => <<<'AR'
من أول ما الخبر يتقال، بيبدأ الكلام. الجيران والعيلة والصحاب وست في الأتوبيس. كلي كذا، امنعي كذا، ده بيعمل كذا.

معظم الكلام ده بيتقال بحب حقيقي. وبعضه صح، وبعضه مالوش أي أساس، وبعضه — وده الجزء اللي المقال ده مهتم بيه — عكس اللي التوصيات المنشورة بتقوله.

## «كلي لاتنين»

الجملة الأشهر، والأكثر تضليلًا.

الاحتياج من الطاقة في الحمل بيزيد فعلًا، بس الزيادة متواضعة، ومعظمها في التلت التاني والتالت مش من أول يوم. الجنين في الشهور الأولى صغير جدًا، والاحتياج الإضافي في المرحلة دي بسيط.

اللي بيزيد بشكل أكبر نسبيًا مش الطاقة — هو الاحتياج من عناصر معيّنة زي الحديد وحمض الفوليك. يعني الصورة الصح مش «ضعف الكمية»، هي «نفس الكمية تقريبًا بجودة أعلى، مع عناصر محددة بتتابع».

الخرافة دي منتشرة لأنها منطقية على السطح — في اتنين، يبقى الأكل مرتين — ولأن المجتمع بيشجعها. النتيجة زيادة وزن أكبر من اللازم في الحمل، وده موضوع ليه متابعة إكلينيكية مش نصيحة عامة.

## حمض الفوليك: الحاجة اللي التوقيت فيها هو كل الحكاية

دي مش خرافة، دي العكس: معلومة مهمة بتوصل متأخر.

حمض الفوليك بيشارك في تكوين الأنبوب العصبي للجنين، والأنبوب ده بيتقفل في مرحلة مبكرة جدًا من الحمل — في وقت كتير من الستات بيبقوا لسه مش عارفين إنهم حوامل أصلًا.

عشان كده التوصيات المنشورة بتتكلم عن تناول حمض الفوليك قبل الحمل ومش بعد تأكيده. منظمة الصحة العالمية بتوصي بمكملات الحديد وحمض الفوليك في الحمل، والتوصيات الخاصة بمنع عيوب الأنبوب العصبي بتحط التوقيت ده كنقطة أساسية.

يعني اللي بتقول «هبدأ الفيتامينات لما أروح للدكتور» ممكن تكون فاتت المرحلة اللي المكمل ده أهم فيها.

CLINICAL_INPUT: إيه اللي بتقوليه لست بتخطط للحمل عن التوقيت ده؟

## «الحديد بيتعب المعدة، يبقى بلاش»

الجزء الأول من الجملة صح غالبًا. الأعراض الهضمية من مكملات الحديد معروفة وشائعة، والإمساك منها.

الجزء التاني هو الغلط. منظمة الصحة العالمية بتوصي بمكملات الحديد وحمض الفوليك اليومية في الحمل، والحمل نفسه بيزوّد الاحتياج للحديد بشكل كبير عشان زيادة حجم الدم وتكوين المشيمة والجنين.

والحل مش الوقف — الحل إن الأعراض تتقال للطبيب، لأن في تعديلات ممكنة في التوقيت أو الشكل أو الجرعة. الوقف من غير كلام هو أسوأ الاختيارات، لأنه بيسيب النقص من غير ما يحل العرض.

CLINICAL_INPUT: لما مريضة حامل تشتكي من أعراض مكمل الحديد — إيه اللي بتقترحيه؟

## ليه الاحتياج من الحديد بيزيد في الحمل بالذات

ده مش مجرد «الحامل محتاجة حديد أكتر». الميكانيكية بتوضح ليه الموضوع ده بيتابع بجدية.

في الحمل حجم الدم بيزيد زيادة كبيرة، والزيادة دي مش متساوية: كمية البلازما بتزيد بنسبة أكبر من كمية كرات الدم الحمرا. النتيجة إن تركيز الهيموجلوبين بيقل بشكل طبيعي في فترة من الحمل، وده مش بالضرورة أنيميا — ده تخفيف فسيولوجي.

وفي نفس الوقت في احتياج حقيقي جديد: تكوين كرات دم إضافية، وبناء المشيمة، والجنين نفسه بيخزن حديد في الشهور الأخيرة عشان يستخدمه بعد الولادة.

يعني في احتياج بيزيد ومخزون بيتسحب منه، وده اللي بيخلي مخزون الحديد قبل الحمل مهم بقد الحمل نفسه.

## «امنعي كل السمك»

خرافة بتنتشر لأنها بتبان الاختيار الآمن.

التوصيات في الحمل مش بتمنع السمك — بالعكس، السمك مصدر بروتين وأحماض دهنية مهمة. اللي بيتقال هو التمييز: تجنب الأنواع المعروفة بارتفاع الزئبق فيها، وتجنب السمك النيء أو غير مكتمل الطهي أو المملح المخمر.

السمك البلدي المطهي كويس مش هو المشكلة. المنع الشامل بيشيل مصدر كويس من الأكل ومش بيحل أي حاجة.

## الكبدة: أشهر أكلة في مصر ليها تحذير في الحمل

دي النقطة اللي أغلب الكلام المتداول بيقول عكسها.

الكبدة مصدر غني جدًا بالحديد، وده صح، وعشان كده بتتنصح للحامل في بيوت كتير. بس الكبدة كمان غنية جدًا بفيتامين أ في صورته الجاهزة (الريتينول)، والكميات العالية من الصورة دي في الحمل مرتبطة بمخاطر على الجنين.

عشان كده الإرشادات الصحية في الحمل بتنصح بتجنب الكبدة ومنتجاتها، ومكملات فيتامين أ العالية، خلال الحمل.

الفرق ده مش تفصيلة: فيتامين أ من الخضار (زي الجزر والبطاطا) في صورة مختلفة (بيتا كاروتين) والجسم بيحوّلها حسب احتياجه، فهي مش نفس القلق.

الخرافة هنا مش خرافة أصلًا — هي معلومة صح (الكبدة فيها حديد) بتؤدي لاستنتاج غلط.

## الجبنة القديمة والمش والفسيخ

أكلات مصرية أصيلة، وكلها ليها نفس الملاحظة في الحمل.

منتجات الألبان غير المبسترة، والجبن الطري المصنوع من لبن غير مبستر، بيبقى فيها خطر تلوث بكتيري — ومنها بكتيريا الليستيريا، اللي عدواها في الحمل ليها مخاطر معروفة على الجنين حتى لو الأم أعراضها بسيطة. عشان كده الإرشادات الغذائية للحمل بتنصح بمنتجات الألبان المبسترة.

والفسيخ والرنجة أكل مملح ومخمر، والفسيخ بالذات بيرتبط في مصر بحالات تسمم موسمية. شم النسيم بيجي كل سنة، والعيلة كلها بتاكل، والضغط الاجتماعي حقيقي. الحاجة اللي تتعرف: ده مش وقت التجربة.

واللحوم والدواجن والبيض غير مكتملة الطهي ليها نفس المنطق: طهي كامل بيقلل خطر عدوى منقولة بالغذاء، ومنها التوكسوبلازما.

## «الوحم معناه إن جسمك ناقصه الحاجة دي»

فكرة جميلة ومنتشرة، وليها تفسير أبسط.

الوحم ظاهرة حقيقية وشائعة، وبيتغير مع تغيّر الشهية والحاسة والغثيان في الحمل. بس فكرة إن الاشتهاء إشارة دقيقة على نقص عنصر معيّن مش مدعومة — واللي بيتوحم عليه غالبًا بيكون أكل حلو أو مالح أو دسم، مش أكل غني بالحديد أو الكالسيوم.

الخرافة دي بتنتشر لأنها بتدي معنى لحاجة محيّرة، ولأنها بتخلي الاستجابة للرغبة مبررة. مش مشكلة إن حد يستجيب لرغبة أحيانًا — المشكلة إن الاستجابة تتحول لتشخيص.

## «امنعي الملح خالص عشان التورم»

التورم في الحمل شائع وله أسباب فسيولوجية مرتبطة بزيادة حجم السوائل وضغط الرحم على الأوردة.

بس المنع الكامل للملح مش توصية موجودة في إرشادات الرعاية قبل الولادة. والأهم من ده كله: التورم المفاجئ أو الشديد، وخصوصًا في الوش والإيدين، ومعاه صداع أو تغيّر في الرؤية، ده عرض بيتقال للطبيب فورًا مش بيتعالج بتعديل في الملح.

يعني الخرافة دي مش بس مش دقيقة — هي ممكن تأخر حاجة محتاجة تتشاف.

## «التمر بيسهّل الولادة»

دي واحدة من الحاجات اللي الأمانة فيها إننا نقول إن الصورة مش قاطعة.

في دراسات بصت على تناول التمر في أواخر الحمل ونتايج الولادة، وبعضها أشار لنتايج إيجابية. لكن الدراسات دي صغيرة نسبيًا والصورة العامة لسه مش مستقرة كفاية عشان تتقال كتوصية.

يعني: التمر أكل كويس، وأكله في الحمل مش مشكلة عند أغلب الناس، والقول إنه «بيسهّل الولادة» أكبر من الأدلة الموجودة. والفرق بين الجملتين دول هو الفرق بين الوصف الأمين والوعد.

## «الغثيان معناه إن الحمل تمام» و«الأكل مش هيستقر فمتاكليش»

الجملتين بيتقالوا مع بعض وبيتناقضوا.

الغثيان في الشهور الأولى شائع جدًا ومزعج، والاستنتاج إن الأكل «مالوش لازمة» في الفترة دي هو اللي بيعمل مشكلة: الشخص بيبطل ياكل، والجوع نفسه بيزوّد الغثيان عند كتير من الستات، فالحلقة بتقفل على نفسها.

واللي يستاهل يتقال: الغثيان الشديد المستمر اللي بيمنع الأكل والشرب ومعاه نقصان في الوزن أو علامات جفاف مش مرحلة بتعدي — ده موقف بيتشاف عند الطبيب، لأن ليه إدارة إكلينيكية.

CLINICAL_INPUT: إيه اللي بتقترحيه عمليًا لست عندها غثيان بيمنعها من الأكل في التلت الأول؟

## في السياق المصري: الأعشاب والمشروبات

الحلبة واليانسون والقرفة والكركديه بتتشرب في البيوت المصرية بانتظام، وبتتقدم للحامل على إنها «طبيعية».

«طبيعي» مش نفس «آمن في الحمل». بعض الأعشاب ليها تأثيرات دوائية حقيقية، وبعضها ليه تحفظات في الحمل، والمعلومات عن كتير منها محدودة. والكافيين — في الشاي والقهوة والمشروبات الغازية — التوصيات بتتكلم عن تقليله في الحمل.

الحاجة العملية: أي حاجة بتتاخد بانتظام تتقال للطبيب، حتى لو مشروب.

PRACTITIONER_VOICE: إيه أكتر خرافة عن أكل الحمل بتوصلك من العيلة مش من المريضة نفسها؟

وفي حاجة أخيرة عن العيلة نفسها. الضغط في الحمل بيجي من ناس بتحب فعلًا، وده اللي بيخليه صعب. الجملة اللي بتنفع مش «ده كلام غلط» — الجملة اللي بتنفع إن في دكتور بيتابع الحمل ده، وإن الكلام ده هيتسأل فيه. ده بيقفل النقاش من غير ما يجرح حد.

## اللي يستاهل تفتكريه

مش كل كلام بيتقال بحب بيبقى صح، ومش كل حاجة «طبيعية» بتبقى آمنة في الحمل.

الاحتياج بيزيد في الجودة أكتر من الكمية. حمض الفوليك توقيته قبل الحمل. الحديد ليه أعراض بتتعالج مش بتوقف المكمل. الكبدة معلومتها الصح بتؤدي لاستنتاج غلط. والألبان غير المبسترة والفسيخ واللحوم غير مكتملة الطهي ليها ملاحظات معروفة.

والتورم المفاجئ مش موضوع ملح — ده موضوع دكتور.

لو حامل وعايزة خطة مبنية على حالتك ومرحلتك: [[specialty:pregnancy-nutrition|تغذية الحمل والرضاعة]] أو [[booking|احجزي موعد]].

وفي مقال عن أسئلة الرضاعة والتغذية بعد الولادة: [[article:feeding-and-eating-recurring-questions|أسئلة بتتكرر عن الرضاعة والأكل]].
AR,

                'en' => <<<'EN'
From the moment the news is announced, the advice begins. Neighbours, family, friends, and a woman on the bus. Eat this, avoid that, this does that.

Most of it is said with genuine affection. Some of it is correct, some has no basis at all, and some — the part this article is concerned with — is the opposite of what published guidance says.

## "Eat for two"

The most famous sentence, and the most misleading.

Energy requirement in pregnancy does increase, but the increase is modest, and most of it belongs to the second and third trimesters rather than the first day. The fetus in the early months is very small, and the additional requirement at that stage is slight.

What increases proportionally more is not energy but the requirement for particular nutrients, iron and folic acid among them. So the accurate picture is not "twice as much" — it is "roughly the same amount at higher quality, with specific nutrients being followed".

The myth spreads because it is superficially logical — there are two of you, so eat twice — and because society encourages it. The consequence is greater weight gain in pregnancy than intended, which is a matter for clinical follow-up rather than general advice.

## Folic acid: the thing where the timing is the whole point

This is not a myth but its opposite: important information that arrives late.

Folic acid is involved in forming the fetal neural tube, and that tube closes at a very early stage of pregnancy — at a point when many women do not yet know they are pregnant at all.

Which is why published guidance discusses folic acid before conception rather than after it is confirmed. The World Health Organization recommends iron and folic acid supplementation in pregnancy, and the guidance specifically concerned with preventing neural tube defects treats that timing as fundamental.

So somebody saying "I will start the vitamins when I see the doctor" may have passed the stage at which this supplement mattered most.

CLINICAL_INPUT: What do you tell a woman planning a pregnancy about that timing?

## "Iron upsets my stomach, so I stopped it"

The first half of that sentence is usually true. Digestive symptoms from iron supplements are well known and common, constipation among them.

The second half is the error. The WHO recommends daily iron and folic acid supplementation in pregnancy, and pregnancy itself substantially increases the requirement for iron, because of the increase in blood volume and the formation of the placenta and the fetus.

And the answer is not stopping — it is telling the doctor about the symptoms, because there are possible adjustments in timing, form or dose. Stopping without saying anything is the worst of the options, because it leaves the deficiency in place without resolving the symptom.

CLINICAL_INPUT: When a pregnant patient reports symptoms from an iron supplement, what do you suggest?

## Why the iron requirement rises in pregnancy specifically

This is more than "a pregnant woman needs more iron". The mechanism explains why it is followed so seriously.

In pregnancy the blood volume increases substantially, and the increase is not even: plasma volume rises proportionally more than red cell volume. The result is that haemoglobin concentration falls naturally during part of pregnancy, and that is not necessarily anaemia — it is physiological dilution.

At the same time there is a genuine new requirement: forming additional red cells, building the placenta, and the fetus itself storing iron in the later months to use after birth.

So there is a rising requirement and a store being drawn on, which is what makes iron status before pregnancy matter as much as during it.

## "Avoid all fish"

A myth that spreads because it looks like the safe option.

Guidance in pregnancy does not exclude fish — on the contrary, fish is a source of protein and important fatty acids. What is said is a distinction: avoiding species known to be high in mercury, and avoiding raw, undercooked or salt-fermented fish.

Well-cooked local fish is not the problem. A blanket exclusion removes a good source of food and solves nothing.

## Liver: the most Egyptian food with a pregnancy warning attached

This is the point where most circulating advice says the opposite.

Liver is a very rich source of iron — true, which is exactly why it gets recommended to pregnant women in a great many households. But liver is also very rich in vitamin A in its preformed state (retinol), and high amounts of that form in pregnancy are associated with risk to the fetus.

Which is why dietary guidance in pregnancy advises avoiding liver and its products, and high-dose vitamin A supplements, during pregnancy.

The distinction matters: vitamin A from vegetables such as carrots and sweet potato is in a different form (beta-carotene) that the body converts according to need, so it does not carry the same concern.

The myth here is not really a myth — it is a correct piece of information leading to a wrong conclusion.

## Aged cheese, mish and fesikh

Genuinely Egyptian foods, and all carrying the same note in pregnancy.

Unpasteurised dairy, and soft cheese made from unpasteurised milk, carry a risk of bacterial contamination — listeria among them, whose infection in pregnancy has recognised risks to the fetus even when the mother's own symptoms are mild. Which is why dietary guidance in pregnancy advises pasteurised dairy.

Fesikh and renga are salted, fermented fish, and fesikh in particular is associated in Egypt with seasonal poisoning cases. Sham El-Nessim comes round every year, the whole family eats, and the social pressure is real. What is worth knowing is that this is not the year to try it.

Meat, poultry and eggs that are not thoroughly cooked follow the same logic: full cooking reduces the risk of foodborne infection, toxoplasmosis among them.

## "A craving means your body needs that thing"

A lovely idea, widely held, with a simpler explanation.

Cravings are a real and common phenomenon, and they shift along with appetite, taste and nausea in pregnancy. But the idea that a craving is a precise signal of a specific nutrient deficiency is not supported — and what gets craved is usually something sweet, salty or rich, rather than something rich in iron or calcium.

The myth spreads because it gives meaning to something puzzling, and because it makes acting on the desire feel justified. There is no problem with acting on a desire sometimes — the problem is when the response turns into a diagnosis.

## "Cut out salt completely because of the swelling"

Swelling in pregnancy is common and has physiological causes related to increased fluid volume and the pressure of the uterus on the veins.

But complete salt restriction is not a recommendation in antenatal care guidance. And more important than any of that: sudden or severe swelling, particularly in the face and hands, accompanied by headache or a change in vision, is a symptom to report to a doctor immediately rather than something to treat by adjusting salt.

So this myth is not merely inaccurate — it can delay something that needs to be seen.

## "Dates make labour easier"

This is one where honesty means saying the picture is not settled.

There have been studies looking at eating dates in late pregnancy and labour outcomes, and some have reported positive findings. But those studies are relatively small and the overall picture is not yet stable enough to be stated as a recommendation.

So: dates are good food, eating them in pregnancy is not a problem for most people, and saying they "make labour easier" is larger than the evidence available. The difference between those two sentences is the difference between an honest description and a promise.

## "Nausea means the pregnancy is going well" and "food will not stay down so do not eat"

Those two sentences get said together and contradict each other.

Nausea in the early months is very common and thoroughly unpleasant, and the conclusion that food is "pointless" during it is what causes trouble: somebody stops eating, and hunger itself worsens nausea in many women, so the loop closes on itself.

And what is worth saying: severe persistent nausea that prevents eating and drinking, accompanied by weight loss or signs of dehydration, is not a phase to be got through — it is a situation to be seen by a doctor, because it has clinical management.

CLINICAL_INPUT: What do you practically suggest for a woman whose first-trimester nausea is preventing her from eating?

## In the Egyptian context: herbs and drinks

Fenugreek, aniseed, cinnamon and hibiscus are drunk regularly in Egyptian households and offered to pregnant women as "natural".

"Natural" is not the same as "safe in pregnancy". Some herbs have genuine pharmacological effects, some carry cautions in pregnancy, and information on many of them is limited. And caffeine — in tea, coffee and fizzy drinks — is something guidance discusses reducing during pregnancy.

The practical point: anything taken regularly should be mentioned to the doctor, even if it is a drink.

PRACTITIONER_VOICE: What is the pregnancy eating myth that reaches you most often from the family rather than from the patient herself?

One last thing about the family itself. The pressure in pregnancy comes from people who genuinely care, which is what makes it hard. The sentence that works is not "that is wrong" — it is that there is a doctor following this pregnancy, and that this will be asked about. That closes the conversation without wounding anybody.

## Worth remembering

Not everything said with affection is correct, and not everything "natural" is safe in pregnancy.

The requirement rises in quality more than in quantity. Folic acid belongs before conception. Iron has symptoms that are managed rather than a supplement that is stopped. Liver is a correct fact leading to a wrong conclusion. And unpasteurised dairy, fesikh and undercooked meat carry recognised notes.

And sudden swelling is not a question about salt — it is a question for a doctor.

If you are pregnant and want a plan built on your case and your stage: [[specialty:pregnancy-nutrition|pregnancy and breastfeeding nutrition]] or [[booking|book an appointment]].

And there is an article on breastfeeding and feeding questions: [[article:feeding-and-eating-recurring-questions|questions that keep coming up about feeding]].
EN,
            ],

            'citations' => [
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => [
                        'ar' => 'توصيات منظمة الصحة العالمية بشأن الرعاية السابقة للولادة من أجل تجربة حمل إيجابية',
                        'en' => 'WHO recommendations on antenatal care for a positive pregnancy experience',
                    ],
                    'year' => 2016,
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports several claims: daily iron and folic acid supplementation in '
                        .'pregnancy; that complete salt restriction is not a recommendation; and that '
                        .'caffeine reduction is discussed. Confirm each separately — the salt point is a '
                        .'claim about ABSENCE from the guidance, which is harder to verify than a positive '
                        .'recommendation and should be checked carefully or softened.',
                ],
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => [
                        'ar' => 'مكملات حمض الفوليك للوقاية من عيوب الأنبوب العصبي',
                        'en' => 'Folic acid supplementation for the prevention of neural tube defects',
                    ],
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the section on folic acid timing — that the neural tube closes very '
                        .'early and guidance therefore addresses periconceptional supplementation. Confident '
                        .'the physiological claim and the general recommendation; confirm the exact WHO '
                        .'document that carries it and add its year. NO DOSE is stated in the article.',
                ],
                [
                    'organisation' => [
                        'ar' => 'المعهد الوطني للصحة وجودة الرعاية (NICE)',
                        'en' => 'National Institute for Health and Care Excellence (NICE)',
                    ],
                    'title' => ['ar' => 'الرعاية السابقة للولادة', 'en' => 'Antenatal care'],
                    'year' => 2021,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the food-safety material: pasteurised dairy, thorough cooking, and '
                        .'avoiding liver because of preformed vitamin A. The liver recommendation is the '
                        .'most consequential single statement in this article — it contradicts advice given '
                        .'routinely in Egyptian households — so confirm which document carries it and cite '
                        .'that document specifically rather than this one if it sits elsewhere.',
                ],
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => ['ar' => 'صحيفة وقائع: داء الليستريات', 'en' => 'Fact sheet: Listeriosis'],
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "listeria infection in pregnancy has recognised risks to the fetus '
                        .'even when the mother\'s own symptoms are mild". Confident of the clinical claim; '
                        .'confirm which WHO document states it and record its revision date.',
                ],
            ],
        ];
    }
}
