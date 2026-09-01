<?php

declare(strict_types=1);

namespace Database\Seeders\Articles;

use App\Enums\CitationConfidence;

/**
 * The most clinically delicate of the fourteen.
 *
 * Somebody arrives at this page tired, having been told she is fine, and she
 * wants to be told she is not. The temptation is to give her that — to imply a
 * deficiency, to name a test she should demand, to hint that her doctor missed
 * something. All three would be a diagnosis from a web page.
 *
 * So the article explains what a reference interval IS — which is definitional
 * rather than clinical, and is the single most useful thing she can be told —
 * reports what named bodies say about specific markers, and hands the
 * case-specific half to the clinician. The section on fatigue not always being
 * a deficiency is there deliberately: an article that only listed things to
 * test for would be recruiting, not informing.
 */
class NormalResultsStillTired extends ArticleDefinition
{
    public function definition(): array
    {
        return [
            'slug' => 'normal-results-still-tired',
            'category' => 'lab-review',
            'tags' => ['lab-results', 'questions-to-ask'],
            'cover' => 'blood-tubes-rack',

            'title' => [
                'ar' => 'التحاليل سليمة وأنا لسه تعبانة — إيه اللي بيحصل؟',
                'en' => 'My results are normal and I am still exhausted',
            ],

            'excerpt' => [
                'ar' => 'كلمة «طبيعي» في ورقة التحليل ليها معنى إحصائي محدد، ومش دايمًا هي نفس معنى «تمام بالنسبالك».',
                'en' => 'The word "normal" on a lab report has a specific statistical meaning, and it is not always the same as "fine for you".',
            ],

            'body' => [
                'ar' => <<<'AR'
موقف بيتكرر كتير: التعب مستمر بقاله شهور، التحاليل اتعملت، والورقة رجعت وكل الأرقام جوه النطاق. الدكتور قال «كله تمام»، والتعب زي ما هو.

المقال ده مش هيقولك إن في حاجة اتلغبطت، ومش هيقولك تطلبي تحليل معيّن. هو عن حاجة أهم وأقل ما بتتشرح: كلمة «طبيعي» في ورقة التحليل دي معناها إيه بالظبط، وإيه اللي بتغطيه وإيه اللي مش بتغطيه.

وأول حاجة بتقولها د. رنا للمريضة في الموقف ده: «التحاليل الطبيعية شيء مطمّن، لكن ده مش معناه إننا نتجاهل إحساسها أو نفترض إن مفيش حاجة محتاجة تتراجع».

## «طبيعي» في ورقة التحليل معناها إيه

النطاق المرجعي اللي بتشوفيه جنب كل نتيجة مش حد فاصل بين الصحة والمرض. هو وصف إحصائي.

بيتبني كالآتي: المعمل بياخد عيّنة كبيرة من ناس معتبرين أصحاء، بيقيس عندهم المؤشر ده، وبيحدد النطاق اللي بيقع فيه معظمهم — والمتعارف عليه هو النطاق اللي بيضم الغالبية العظمى منهم، وبيتساب طرفين صغيرين بره النطاق.

ده معناه حاجتين مهمين.

الأولى: في ناس أصحاء بيقعوا بره النطاق، بحكم التعريف نفسه. الطرفين اللي اتسابوا بره مش مرضى — هم أصحاء بأرقام غير شائعة.

والتانية، وهي الأهم: النطاق ده بيوصف مجموعة، مش بيوصف شخص. الرقم اللي بيقع عند الحافة السفلى للنطاق «طبيعي» بمعنى إنه بيحصل في ناس أصحاء، بس ده مش نفس القول إنه الرقم المناسب ليكي إنتِ بالذات، ولا إنه نفس رقمك وإنتِ كويسة.

وفي حاجة تالتة عملية: النطاقات دي بتختلف من معمل لمعمل حسب الجهاز والطريقة. عشان كده مقارنة نتيجة من معمل بنتيجة من معمل تاني محتاجة انتباه.

## الفرق بين «طبيعي» و«مثالي لحالتك»

ده الفرق اللي بيفسّر معظم الحيرة.

«طبيعي» بيجاوب على سؤال: هل الرقم ده بيحصل في ناس أصحاء؟ 

والسؤال اللي في دماغك مختلف: هل الرقم ده هو اللي بيخليكي تعبانة؟ ودي حاجة بتتحدد بالحالة الإكلينيكية كلها — الأعراض، التاريخ، الأدوية، وباقي النتايج — مش برقم واحد جوه ورقة.

عشان كده الدكتور اللي بيقرا التحليل مع الأعراض بيوصل لحاجة مختلفة عن اللي بيقرا الأرقام لوحدها.

## الأنيميا مش بس رقم الهيموجلوبين

دي نقطة عملية ومهمة، وبتتكرر جدًا عند الستات.

منظمة الصحة العالمية بتعرّف الأنيميا بحدود لتركيز الهيموجلوبين بتختلف حسب السن والجنس والحمل. لكن الهيموجلوبين بيوصف نتيجة نهائية، مش المخزون.

الجسم بيخزن الحديد، والمخزون ده بيقل الأول قبل ما الهيموجلوبين يتأثر. يعني ممكن يكون المخزون منخفض والهيموجلوبين لسه جوه النطاق — لأن الجسم بيحافظ على إنتاج كرات الدم لحد ما المخزون يخلص فعلًا.

ومنظمة الصحة العالمية أصدرت دليل مخصص لاستخدام تركيز الفيريتين في تقييم حالة الحديد، وهو المؤشر اللي بيعبّر عن المخزون ده. ووجود دليل كامل للمؤشر ده لوحده معلومة: تقييم الحديد مش سؤال واحد.

وفي تعقيد إضافي: الفيريتين بيرتفع مع الالتهاب، فقراية عالية مش دايمًا معناها مخزون كويس، وده سبب إن التفسير محتاج حد شايف الصورة كلها.

ود. رنا بتحط الحد هنا بوضوح: «مش معنى إن تحليل معين قريب من الحد الأدنى أو الأعلى إننا نقدر نقول فورًا (أهو ده سبب التعب). التحاليل لازم تتقري مع الأعراض والتاريخ الصحي وتقييم الطبيب، وممكن الطبيب يشوف إن في فحوصات إضافية مطلوبة حسب الحالة».

## نقص الحديد بيحصل على مراحل، مش مرة واحدة

الصورة دي بتوضح ليه ممكن حد يبقى تعبان والهيموجلوبين عنده جوه النطاق.

في المرحلة الأولى المخزون بيقل. الجسم بيسحب من مخزونه عشان يكمّل احتياجه اليومي، والدم بيفضل شكله عادي تمامًا. الشخص هنا ممكن يبدأ يحس بحاجة، وصورة الدم مش هتقول أي حاجة.

في المرحلة اللي بعدها المخزون بيبقى فاضي تقريبًا، والحديد المتاح للنقل بيقل، والجسم بيبدأ يلاقي صعوبة في تكوين كرات دم جديدة بنفس الكفاءة — والهيموجلوبين ممكن يكون لسه عند الحافة السفلى للنطاق، يعني «طبيعي».

وفي المرحلة الأخيرة بس بيظهر اللي بنسميه أنيميا: الهيموجلوبين بينزل تحت الحد.

يعني رقم الهيموجلوبين هو آخر حاجة بتتأثر، مش أولها. وده مش رأي — دي طريقة اشتغال المخزون نفسه.

## والاتجاه بيقول أكتر من القراية

نتيجة واحدة بتوصف لحظة. نتيجتين بينهم ستة شهور بيوصفوا حركة.

رقم عند الحافة السفلى ونازل من رقم أعلى السنة اللي فاتت حاجة، ورقم عند نفس الحافة وثابت من سنين حاجة تانية خالص — وشكلهم واحد في ورقة واحدة.

عشان كده الورق القديم مش زبالة. هو المعلومة الوحيدة اللي بتحوّل النقطة لخط.

## حاجات تانية بتتنسى في الشكوى دي

في مؤشرات تانية ليها علاقة معروفة بالتعب، وكل واحدة فيها ليها أدبيات خاصة:

فيتامين ب١٢. المعهد الوطني البريطاني (NICE) عمل دليل مخصص لنقص فيتامين ب١٢ عند البالغين، وبيتعامل مع التشخيص كحاجة بتجمع بين النتيجة والصورة الإكلينيكية، مش رقم لوحده.

فيتامين د. في إرشادات منشورة عن الفئات الأكثر عرضة للنقص وعن التعامل معاه، ومنها إرشادات NICE عن استخدام مكملات فيتامين د في فئات معيّنة.

الغدة الدرقية. في حالة اسمها قصور الغدة تحت الإكلينيكي، بيكون فيها مؤشر الغدة متغيّر والهرمونات لسه في النطاق، والجمعية الأوروبية للغدة الدرقية عندها إرشادات مخصصة للتعامل معاها — وهي بالظبط نوع الحالة اللي بتتقري «طبيعي» بسرعة.

وحساسية القمح (السيلياك). المعهد الوطني البريطاني بيوصي بالتفكير فيها في حالات منها الأنيميا غير المفسّرة والتعب المزمن، وهي حالة بتتشخص متأخر كتير.

## والتعب مش دايمًا نقص

ده الجزء اللي مقال بيحاول يبيع متابعة مش هيكتبه، وهو الجزء الأهم.

النوم غير الكافي أو المتقطع بيدي تعب مستمر مش هيتحل بأي مكمل. والضغط النفسي المزمن بيدي إرهاق حقيقي جسديًا. وقلة الحركة بتقلل القدرة على الاحتمال بمرور الوقت. وفي أدوية أثرها الجانبي المعروف هو الإرهاق. وفي حالات نفسية زي الاكتئاب أول عرض فيها ممكن يكون تعب جسدي مش مزاج.

يعني قايمة التحاليل مش هي كل الإجابة، ولو الشخص دوّر على النقص بس، ممكن يفضل يدور سنة.

## خرافة: «لو كان في حاجة، كانت التحاليل بيّنت»

الخرافة دي منتشرة لأنها شبه صح.

التحاليل فعلًا أداة قوية، وبتكشف حاجات كتير. بس هي بتكشف اللي اتطلب بس. تحليل صورة دم كاملة مش بيقيس مخزون الحديد، ومش بيقيس الغدة الدرقية، ومش بيقيس فيتامين ب١٢. «التحاليل سليمة» جملة معناها بيعتمد على اتعمل إيه بالظبط.

والوجه التاني للخرافة أخطر: إن كل تعب لازم يكون له سبب في ورقة. الناس بتطلب تحاليل أكتر وأكتر، وكل نتيجة طبيعية بتزوّد الإحباط، والسبب الحقيقي ممكن يكون حاجة مش بتتقاس بالدم أصلًا.

الاتزان الصح: التحاليل المناسبة تتعمل، وتتقري مع الأعراض، ومتتحولش لرحلة بحث بلا نهاية.

## في السياق المصري

نقص الحديد والأنيميا بين الستات في مصر موضوع متابع في المسوح الصحية الوطنية زي المسح الصحي الديموغرافي لمصر، وهو من الحاجات اللي بتتشاف كتير في العيادات.

وفي عادات يومية ليها علاقة مباشرة بالامتصاص. الشاي بعد الأكل عادة شبه ثابتة في بيوت كتير، والشاي فيه مركبات بتقلل امتصاص الحديد النباتي — والحديد النباتي هو الغالب في أكل زي العدس والفول والخضار الورقي. والكالسيوم بيزاحم الحديد، وفيتامين C بيزوّد امتصاصه.

يعني نفس طبق العدس ممكن يدي حديد مختلف حسب اتشرب معاه إيه.

وفيتامين د حالة غريبة هنا: بلد شمسه قوية طول السنة، والنقص موجود. السبب مش الشمس — السبب التعرض. الشغل جوه المباني، والخروج في وقت مش وقت ذروة الأشعة، واللبس اللي بيغطي الجلد، كلها بتقلل التصنيع في الجلد بغض النظر عن الطقس بره.

## اللي بيتسأل قبل أي استنتاج

د. رنا بتبدأ من الصورة كاملة، مش من ورقة التحليل. الأسئلة:

بتنامي كام ساعة؟ ونومك نفسه مريح ولا بتصحي تعبانة؟

بتاكلي كام مرة في اليوم؟ والوجبات فيها بروتين وكربوهيدرات ودهون بشكل متوازن؟

بتشربي مياه كفاية؟

بتعتمدي على القهوة عشان تكمّلي اليوم؟

في فترات طويلة جدًا من غير أكل؟

نشاطك وحركتك عاملين إزاي؟

وهل الإرهاق جديد ولا موجود من فترة؟

وجنب ده كله، مراجعة للتحاليل اللي اتعملت فعلًا: «لأن جملة (تحاليلي كلها طبيعية) مش معناها بالضرورة إن كل حاجة ممكن تكون مرتبطة بالإرهاق اتفحصت».

وفي عادة تانية بتحصل في رمضان: كل الأكل بيتركز في ساعات قليلة، والشاي بيبقى بعد الفطار مباشرة، والوجبة اللي فيها مصدر الحديد بتبقى هي نفسها اللي بعدها الشاي. ده مش سبب لوحده لأي حاجة، بس هو عامل حقيقي بيستاهل يتقال.

## الأسئلة اللي تسأليها لما تسمعي «التحاليل سليمة»

«اتعمل إيه بالظبط؟» — اطلبي قايمة التحاليل نفسها، مش الخلاصة.

«الأرقام إيه؟» — «سليم» مش نتيجة. الرقم والنطاق مع بعض هما النتيجة.

«في رقم قريب من الحافة؟» — ده سؤال مشروع تمامًا.

«الأعراض دي ممكن يكون سببها حاجة مش في التحاليل دي؟»

واحتفظي بالورق. النتايج القديمة بتوري اتجاه، والاتجاه بيقول حاجات النتيجة الواحدة مش بتقولها. في مقال عن تجهيز ده كله: [[article:what-to-bring-to-a-first-appointment|إيه اللي تجيبيه معاكي في أول زيارة]].

## اللي بيطلع غالبًا من الناحية الغذائية

«أوقات المشكلة مش نقص عنصر واحد أصلًا. ممكن الجسم ببساطة مش بياخد طاقة كفاية على مدار اليوم، أو الوجبات غير متوازنة، أو المريضة عاملة دايت قاسي، أو بتعدّي ساعات طويلة من غير أكل وبعدها تعتمد على السكريات والكافيين عشان تستعيد طاقتها».

## «حاسّة إن محدش لاقي عندي حاجة»

الجملة دي بتتقال كتير، وده الرد عليها:

«إحساسك بالتعب حقيقي ويستحق إننا نفهمه، لكن مش هخمن له سبب لمجرد إن التحاليل الأساسية طبيعية.

هنراجع أكلك، نومك، نشاطك، أعراضك، التحاليل اللي اتعملت، وأي أدوية أو عوامل ممكن تكون مؤثرة. ولو في أعراض مستمرة أو حاجة محتاجة تقييم طبي أكتر، بنرجع للطبيب بدل ما نحاول نفسّر كل حاجة بالتغذية.

الهدف مش إننا نلاقي نقص في تحليل وخلاص. الهدف إننا نفهم جسمك ويومك كصورة كاملة، ونحدد إيه اللي نقدر نحسّنه بالتغذية ونمط الحياة، وإيه اللي محتاج متابعة طبية».

## اللي يستاهل تفتكريه

«طبيعي» في ورقة التحليل وصف إحصائي لمجموعة، مش حكم على شخص. وهي بتجاوب على سؤال مختلف عن السؤال اللي في دماغك.

الأنيميا مش بس الهيموجلوبين، والمخزون بيقل قبله. وفي مؤشرات تانية ليها علاقة بالتعب وليها أدبيات خاصة. وفي نفس الوقت، مش كل تعب سببه نقص، والبحث عن رقم بس ممكن يضيّع شهور.

اللي بيفرق هو إن حد يقرا الأرقام مع الأعراض والتاريخ والأدوية مع بعض. لو عندك ورق تحاليل ومحدش قعد يشرحه، ده اللي بتعمله [[specialty:lab-review|مراجعة التحاليل]] — وتقدري [[booking|تحجزي موعد]] وتجيبي الورق كله معاكي.

ولو حابة تفهمي الأول ليه «ظبّطي أكلك» مش إجابة كافية: [[article:what-fix-your-diet-actually-means|الجملة دي معناها إيه بالظبط]].

و«التحاليل طبيعية» خبر كويس، لكنه مش معناه إننا نقول للمريضة «يبقى إنتِ كويسة ومفيش حاجة». وفي نفس الوقت، التعب مش معناه تلقائيًا إن عندك نقص فيتامينات.

المهم إننا ما نهملش الأعراض، وما نفسرهاش من غير دليل.
AR,

                'en' => <<<'EN'
A situation that recurs constantly: the exhaustion has lasted months, the tests were done, the report came back with every number inside its range. The doctor said everything is fine, and the exhaustion is exactly where it was.

This article will not tell you that something was missed, and it will not tell you to demand a particular test. It is about something more useful and less often explained: what the word "normal" on that report actually means, and what it does and does not cover.

And the first thing Dr Rana says to a patient in this position: "Normal results are reassuring. But that does not mean we ignore how she feels, or assume there is nothing that needs looking at." 

## What "normal" means on a lab report

The reference range printed beside each result is not a boundary between health and disease. It is a statistical description.

It is built like this: the laboratory takes a large sample of people considered healthy, measures the marker in them, and defines the range within which most of them fall — conventionally the range containing the great majority, with two small tails left outside it.

That means two important things.

First: there are healthy people outside the range, by the definition itself. The tails left outside are not ill — they are healthy people with uncommon numbers.

Second, and more importantly: the range describes a group, not a person. A result sitting at the low edge is "normal" in the sense that it occurs in healthy people, but that is not the same as saying it is the right number for you, or that it is your own number when you are well.

And a third, practical point: these ranges differ between laboratories according to the instrument and the method. Comparing a result from one laboratory with a result from another needs care.

## The difference between "normal" and "right for you"

This is the distinction that explains most of the confusion.

"Normal" answers the question: does this number occur in healthy people?

The question in your mind is a different one: is this number why I am exhausted? And that is settled by the whole clinical picture — symptoms, history, medications, and the other results — not by one figure on a page.

Which is why a doctor reading the results alongside the symptoms arrives somewhere different from one reading the numbers alone.

## Anaemia is not only the haemoglobin number

This is a practical and important point, and it comes up constantly in women.

The World Health Organization defines anaemia by haemoglobin thresholds that vary with age, sex and pregnancy. But haemoglobin describes an end result, not a store.

The body stores iron, and that store falls first, before haemoglobin is affected. So the store can be low while haemoglobin is still inside its range — because the body protects red cell production until the store is genuinely exhausted.

The WHO has issued guidance devoted specifically to using ferritin concentrations to assess iron status, ferritin being the marker that reflects that store. The existence of a whole guideline for one marker is itself informative: assessing iron is not a single question.

And there is an added complication: ferritin rises with inflammation, so a high reading does not always mean a healthy store — which is another reason interpretation needs somebody looking at the whole picture.

Dr Rana draws the line here plainly: "A result sitting near the lower or upper limit does not mean we can turn round and say 'there, that's the cause of the tiredness'. Results have to be read alongside the symptoms, the medical history and the doctor's assessment — and the doctor may decide further tests are needed depending on the case." 

## Iron depletion happens in stages, not all at once

This picture explains how somebody can be exhausted with a haemoglobin inside the range.

In the first stage the store falls. The body draws on its reserve to meet daily needs, and the blood looks entirely ordinary. A person may already be feeling something here, and a blood count will say nothing.

In the next stage the store is close to empty, the iron available for transport falls, and the body starts to have difficulty forming new red cells as efficiently — while haemoglobin may still be sitting at the low edge of the range, which is to say "normal".

Only in the last stage does what we call anaemia appear: haemoglobin drops below the threshold.

So the haemoglobin figure is the last thing to be affected, not the first. That is not an opinion — it is how the store itself works.

## And the direction says more than the reading

One result describes a moment. Two results six months apart describe a movement.

A number at the low edge that has fallen from a higher one last year is one thing; a number at the same edge that has been steady for years is something else entirely — and the two look identical on a single page.

Which is why old paperwork is not rubbish. It is the only thing that turns a point into a line.

## Other things that get overlooked in this complaint

There are other markers with a recognised relationship to fatigue, each with its own literature:

Vitamin B12. NICE has produced guidance devoted to B12 deficiency in adults, and treats diagnosis as something combining the result with the clinical picture rather than a number on its own.

Vitamin D. There is published guidance on which groups are more likely to be deficient and how deficiency is handled, including NICE guidance on supplement use in specific population groups.

The thyroid. There is a state called subclinical hypothyroidism, in which the thyroid marker is altered while the hormones themselves are still in range, and the European Thyroid Association has guidance devoted to managing it — which is exactly the kind of situation most easily read as "normal".

And coeliac disease. NICE recommends considering it in situations including unexplained anaemia and chronic fatigue, and it is a condition frequently diagnosed late.

## And exhaustion is not always a deficiency

This is the part an article trying to sell follow-up would not write, and it is the most important part.

Insufficient or broken sleep produces persistent exhaustion that no supplement will resolve. Chronic psychological stress produces genuine physical fatigue. Inactivity reduces capacity over time. Some medications have exhaustion as a well-known side effect. And in some psychological conditions, including depression, the first symptom can be physical tiredness rather than mood.

So a list of tests is not the whole answer, and somebody looking only for a deficiency can spend a year looking.

## The myth: "if there were something wrong, the tests would have shown it"

This myth spreads because it is nearly true.

Tests are genuinely powerful and reveal a great deal. But they reveal what was requested. A full blood count does not measure iron stores, does not measure the thyroid, and does not measure vitamin B12. "The tests are normal" is a sentence whose meaning depends entirely on which tests were done.

The other face of the myth is more dangerous: the assumption that every exhaustion must have a cause visible on a page. People request more and more tests, each normal result adds to the frustration, and the real cause may be something not measured in blood at all.

The right balance: appropriate tests done, read alongside symptoms, and not turned into an endless search.

## In the Egyptian context

Iron deficiency and anaemia among women in Egypt are tracked in national health surveys such as the Egypt Demographic and Health Survey, and they are among the things seen most often in clinic.

And there are daily habits with a direct bearing on absorption. Tea after meals is close to a fixture in many households, and tea contains compounds that reduce the absorption of plant iron — and plant iron is the dominant kind in food like lentils, foul and leafy greens. Calcium competes with iron, and vitamin C increases its absorption.

So the same plate of lentils can deliver different amounts of usable iron depending on what was drunk alongside it.

Vitamin D is a strange case here: a country with strong sun all year, and deficiency is present anyway. The reason is not the sun — it is exposure. Working indoors, going out outside the hours of peak ultraviolet, and clothing that covers the skin all reduce synthesis in the skin regardless of the weather outside.

## What gets asked before any conclusion

Dr Rana starts from the whole picture, not from the report. The questions:

How many hours are you sleeping? And is the sleep itself restful, or do you wake up tired?

How many times a day do you eat? And are the meals balanced between protein, carbohydrate and fat?

Are you drinking enough water?

Are you relying on coffee to get through the day?

Are there very long stretches with no food?

How are your activity and movement?

And is the exhaustion new, or has it been there a while?

Alongside all of that, a review of which tests were actually done: "Because 'all my results are normal' does not necessarily mean that everything which might be connected to the exhaustion was tested." 

There is another pattern in Ramadan: all the food is concentrated into a few hours, tea comes straight after iftar, and the meal containing the iron source is the same meal the tea follows. That is not a cause of anything on its own, but it is a real factor worth naming.

## Questions to ask when you hear "your results are normal"

"What exactly was tested?" — ask for the list of tests, not the summary.

"What were the numbers?" — "normal" is not a result. The figure and its range together are the result.

"Is anything sitting near an edge?" — an entirely legitimate question.

"Could these symptoms be caused by something these tests do not cover?"

And keep the paper. Old results show a direction, and a direction says things a single result cannot. There is an article on preparing all of this: [[article:what-to-bring-to-a-first-appointment|What to bring to a first appointment]].

## What usually turns out to be true, nutritionally

"Sometimes the problem is not a single nutrient at all. The body may simply not be getting enough energy across the day, or the meals are unbalanced, or the patient is on a severe diet, or she goes many hours without food and then relies on sugar and caffeine to get her energy back."

## "I feel like nobody has found anything wrong with me"

This gets said often, and this is the answer to it:

"Your exhaustion is real and it deserves to be understood. But I am not going to guess at a cause for it just because the basic tests came back normal.

We will go through your eating, your sleep, your activity, your symptoms, the tests that were done, and any medication or other factors that might be affecting it. And if there are persistent symptoms, or anything that needs more medical assessment, we go back to the doctor rather than trying to explain everything through nutrition.

The aim is not to find a deficiency on a report and stop there. The aim is to understand your body and your day as a whole picture, and to work out what we can improve through nutrition and lifestyle — and what needs medical follow-up." 

## Worth remembering

"Normal" on a lab report is a statistical description of a group, not a verdict on a person. And it answers a different question from the one in your mind.

Anaemia is not only haemoglobin, and the store falls before it. There are other markers with a relationship to fatigue and their own literature. And at the same time, not every exhaustion is a deficiency, and looking only for a number can cost months.

What makes the difference is somebody reading the numbers alongside the symptoms, the history and the medications together. If you have results and nobody has sat down and explained them, that is what a [[specialty:lab-review|lab review]] is — and you can [[booking|book an appointment]] and bring all of the paper with you.

And if you would first like to understand why "sort your diet out" is not a sufficient answer: [[article:what-fix-your-diet-actually-means|what that sentence actually means]].

"Your results are normal" is good news, but it does not mean telling a patient "so you're fine and there's nothing wrong". And at the same time, being tired does not automatically mean you have a vitamin deficiency.

What matters is that we do not dismiss the symptoms, and we do not explain them without evidence.
EN,
            ],

            'citations' => [
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => [
                        'ar' => 'دليل منظمة الصحة العالمية بشأن استخدام تركيزات الفيريتين لتقييم حالة الحديد',
                        'en' => 'WHO guideline on use of ferritin concentrations to assess iron status',
                    ],
                    'year' => 2020,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "the WHO has issued guidance devoted specifically to using ferritin '
                        .'concentrations to assess iron status". Confident such a guideline exists; confirm '
                        .'the exact title and year, and whether it covers both individuals and populations.',
                ],
                [
                    'organisation' => ['ar' => 'منظمة الصحة العالمية', 'en' => 'World Health Organization'],
                    'title' => ['ar' => 'صحيفة وقائع: فقر الدم', 'en' => 'Fact sheet: Anaemia'],
                    'confidence' => CitationConfidence::High,
                    'note' => 'Supports "the WHO defines anaemia by haemoglobin thresholds that vary with '
                        .'age, sex and pregnancy". No specific threshold is quoted in the article — the '
                        .'thresholds were revised recently and quoting one from memory is exactly the error '
                        .'this article warns about. Record the revision date current at verification.',
                ],
                [
                    'organisation' => [
                        'ar' => 'المعهد الوطني للصحة وجودة الرعاية (NICE)',
                        'en' => 'National Institute for Health and Care Excellence (NICE)',
                    ],
                    'title' => [
                        'ar' => 'نقص فيتامين ب١٢ عند من هم فوق ١٦ سنة: التشخيص والتعامل',
                        'en' => 'Vitamin B12 deficiency in over 16s: diagnosis and management',
                    ],
                    'year' => 2024,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "NICE has produced guidance devoted to B12 deficiency in adults, and '
                        .'treats diagnosis as combining the result with the clinical picture". This is a '
                        .'recent guideline and the year is the detail most likely to be wrong — confirm both '
                        .'that it exists and its publication year.',
                ],
                [
                    'organisation' => [
                        'ar' => 'المعهد الوطني للصحة وجودة الرعاية (NICE)',
                        'en' => 'National Institute for Health and Care Excellence (NICE)',
                    ],
                    'title' => [
                        'ar' => 'فيتامين د: استخدام المكملات في فئات سكانية محددة',
                        'en' => 'Vitamin D: supplement use in specific population groups',
                    ],
                    'year' => 2014,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports the existence of published guidance on groups more likely to be '
                        .'vitamin D deficient. No dose or threshold is quoted. Confirm title and year.',
                ],
                [
                    'organisation' => [
                        'ar' => 'الجمعية الأوروبية للغدة الدرقية (ETA)',
                        'en' => 'European Thyroid Association (ETA)',
                    ],
                    'title' => [
                        'ar' => 'إرشادات التعامل مع قصور الغدة الدرقية تحت الإكلينيكي',
                        'en' => 'Guidelines for the Management of Subclinical Hypothyroidism',
                    ],
                    'year' => 2013,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports "the European Thyroid Association has guidance devoted to managing '
                        .'subclinical hypothyroidism". Confident such guidance exists; confirm the year and '
                        .'whether a newer edition supersedes it.',
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
                    'note' => 'Supports "NICE recommends considering coeliac disease in situations including '
                        .'unexplained anaemia and chronic fatigue". Confirm that both of those appear in the '
                        .'guideline\'s list of situations prompting testing.',
                ],
                [
                    'organisation' => [
                        'ar' => 'المسح الصحي الديموغرافي لمصر',
                        'en' => 'Egypt Demographic and Health Survey',
                    ],
                    'title' => [
                        'ar' => 'المسح الصحي الديموغرافي لمصر',
                        'en' => 'Egypt Demographic and Health Survey',
                    ],
                    'year' => 2014,
                    'confidence' => CitationConfidence::Medium,
                    'note' => 'Supports only the weak claim that iron deficiency and anaemia among Egyptian '
                        .'women are tracked in national health surveys. NO PREVALENCE FIGURE IS QUOTED, '
                        .'deliberately — the figure is what a draft written from memory would get wrong. If '
                        .'a number is wanted here, take it from the survey itself and add it with the '
                        .'edition year.',
                ],
            ],
        ];
    }
}
