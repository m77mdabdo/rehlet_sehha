<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Seeder;

/**
 * THE TWELVE ARTICLES, AS DRAFTS.
 *
 * Every one is complete as a piece of writing and incomplete as a piece of
 * medicine, deliberately. The structure, the framing, the transitions, the
 * headings and the conclusion are written; every place where a specific
 * clinical statement belongs carries a CLINICAL_INPUT marker and a one-line
 * question naming exactly what is needed.
 *
 * The point of that split is that Dr. Rana answers questions instead of
 * writing articles. "What do you tell a patient who has reached week three and
 * wants to stop?" is a thing a busy clinician can answer in a sentence between
 * appointments. "Write me an article about adherence" is not.
 *
 * NOTHING HERE PUBLISHES. Two independent gates hold it:
 *
 *   1. reviewed_by / reviewed_at are null, and Post::booted() refuses to save
 *      a published article without a named clinical reviewer.
 *   2. CLINICAL_INPUT is present, and the same hook refuses to publish while
 *      any prompt is unanswered.
 *
 * BODY FORMAT. Plain text, not HTML, because the column is rendered as text
 * and an editor pasting from Word must not be able to inject markup. Two
 * conventions carry the structure:
 *
 *   ## A heading            → rendered as <h2>
 *   CLINICAL_INPUT: …       → rendered as a marked gap, never published
 *
 * Paragraphs are separated by a blank line, as they already were.
 */
class PostSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * The two categories the old free-text column left behind. They were
         * created by the data migration from values like "تغذية", carry no
         * description and now hold no articles.
         */
        Category::query()->whereIn('slug', ['nutrition', 'general-health'])->delete();

        /*
         * The three placeholder articles this file used to hold.
         *
         * They existed to give the templates something to render and were
         * never clinically reviewed; one of them read vitamin D near the
         * bottom of its range against a complaint of fatigue, which is a
         * clinical statement under a licensed byline. The twelve below replace
         * them entirely, so they are removed rather than left uncategorised in
         * a table somebody might publish from.
         */
        Post::query()->whereIn('slug', [
            'building-a-habit-that-lasts',
            'protein-on-an-egyptian-budget',
            'reading-your-lab-results',
        ])->delete();

        foreach ($this->articles() as $article) {
            $category = Category::query()->where('slug', $article['category'])->first();

            $post = Post::updateOrCreate(
                ['slug' => $article['slug']],
                [
                    'category_id' => $category?->id,
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'body' => $article['body'],
                    'cover_path' => $article['cover'] ?? null,
                    'is_featured' => $article['featured'] ?? false,

                    // Draft. See the class docblock for the two gates.
                    'published_at' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ],
            );

            $post->tags()->sync(
                Tag::query()->whereIn('slug', $article['tags'])->pluck('id')->all()
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function articles(): array
    {
        return array_merge(
            $this->weightManagement(),
            $this->medicalNutrition(),
            $this->labReview(),
            $this->pcos(),
            $this->pregnancy(),
            $this->child(),
            $this->sports(),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function weightManagement(): array
    {
        return [
            [
                'slug' => 'why-we-quit-in-week-three',
                'category' => 'weight-management',
                'tags' => ['adherence'],
                'featured' => true,
                'cover' => 'kitchen-hands-herbs',
                'title' => [
                    'ar' => 'ليه بنسيب النظام في الأسبوع التالت؟',
                    'en' => 'Why we quit in week three',
                ],
                'excerpt' => [
                    'ar' => '«زهقت» مش كسل، ودي مش كلمة نهاية. الأسبوع التالت له شكل معروف، وفهمه بيغيّر رد الفعل.',
                    'en' => '"I got bored" is not laziness, and it is not the end of the sentence. Week three has a shape, and knowing it changes what you do next.',
                ],
                'body' => [
                    'ar' => <<<'AR'
أكتر جملة بتتقال في العيادة مش «مش قادرة» ولا «الأكل وحش». الجملة هي: «زهقت».

وبتتقال غالبًا في نفس التوقيت. مش الأسبوع الأول، ولا الشهر التالت. الأسبوع التالت.

المقال ده مش عن إزاي «تتحمسي تاني». هو عن إن الأسبوع التالت له شكل معروف، وإن الشكل ده متوقع بالدرجة اللي تخليكي تعرفي إنه جاي قبل ما يوصل.

## الأسبوعين الأولانيين بيشتغلوا بحاجة تانية خالص

في البداية بتشتغلي بحاجة اسمها الجدة. كل حاجة جديدة: الأكل جديد، والروتين جديد، والفكرة إن في خطة أصلاً جديدة. الجدة دي بتدي طاقة حقيقية، بس هي طاقة ليها تاريخ صلاحية.

وفي حاجة تانية بتشتغل في الأول: النتيجة السريعة. أول أسبوعين في أي تغيير في الأكل بيحصل فيهم تغييرات مش بالضرورة ليها علاقة بالهدف بعيد المدى، والجسم بيستجيب لأي تغيير مفاجئ بشكل مختلف عن استجابته للتغيير المستمر.

CLINICAL_INPUT: إيه اللي بيحصل في الجسم فعليًا في أول أسبوعين من تغيير في الأكل، بشكل عام ومن غير أرقام؟ جملتين.

## الأسبوع التالت هو أول أسبوع «عادي»

في الأسبوع التالت الجدة بتخلص، والنتيجة السريعة بتهدى، والخطة بتبقى مجرد… حاجة بتعمليها. من غير حماس، ومن غير مكافأة واضحة.

ودي مش لحظة فشل. دي أول لحظة بتشوفي فيها الخطة على حقيقتها: حاجة هتعمليها لفترة طويلة. اللي بيحصل إن دماغك بتقارن الإحساس ده باللي كان في الأسبوع الأول، والمقارنة دي ظالمة — لأنها بتقارن حالة مستمرة بحالة مؤقتة.

«زهقت» في اللحظة دي معناها بالظبط: «الحاجة اللي كانت مثيرة بقت عادية». وده كان هيحصل مهما كانت الخطة.

## الفرق بين إنك تقفي وإنك تعدّلي

المشكلة مش إن حد بيزهق. المشكلة إن الزهق بيتقري على إنه حكم: «أنا مش بنفع»، «الخطة دي مش ليا»، «خلاص كل مرة بيحصل كده».

وبين الوقوف والتعديل مسافة كبيرة. الخطة اللي بتتعدل في الأسبوع التالت بتفضل شغالة؛ الخطة اللي بتتساب بترجع من الأول بعد شهرين.

CLINICAL_INPUT: لما مريضة توصل للأسبوع التالت وتقولك «زهقت» — إنتِ بتقوليلها إيه بالظبط؟ الجملة اللي بتستخدميها فعلاً.

## إيه اللي بيتغيّر في المتابعة

المتابعة مش موجودة عشان حد يراجع عليكي. هي موجودة عشان اللحظة دي بالذات: عشان يبقى في حد يشوف إن ده الأسبوع التالت ومش يقرا الكلام على إنه فشل.

CLINICAL_INPUT: إيه أول حاجة بتغيّريها في الخطة لما حد يوصل للنقطة دي؟ من غير تفاصيل خطة معينة — المبدأ بس.

## اللي يستاهل تفتكريه

الأسبوع التالت مش علامة إن الخطة غلط. هو علامة إن الخطة بقت جزء من اليوم العادي، ودي الحاجة اللي كنتي عايزاها من الأول.

والزهق ليه حل، بس الحل مش «حماس أكتر». الحل إن الخطة تتعدل عشان تنفع أسبوع عادي، مش أسبوع فيه حماس.

لو وصلتي للنقطة دي دلوقتي، دي مش لحظة إنك تبطلي — دي بالظبط اللحظة اللي فيها الكلام مع حد بيفرق.
AR,
                    'en' => <<<'EN'
The most common sentence in the clinic is not "I cannot do it" or "the food is bad". It is: "I got bored."

And it tends to arrive at the same moment. Not the first week, not the third month. Week three.

This article is not about how to get motivated again. It is about the fact that week three has a recognisable shape, and that knowing the shape changes what you do when you reach it.

## The first two weeks run on something else entirely

At the start you are running on novelty. Everything is new: the food, the routine, the very fact that there is a plan at all. Novelty gives real energy — but it has an expiry date.

Something else is working early on too: the fast result. The first fortnight of any change in eating involves shifts that are not necessarily related to the long-term goal, and a body responds to a sudden change differently from how it responds to a sustained one.

CLINICAL_INPUT: What is actually happening in the body during the first two weeks of a change in eating — in general terms, no figures? Two sentences.

## Week three is the first ordinary week

By week three the novelty has gone, the early movement has settled, and the plan has become simply… something you do. No excitement, no obvious reward.

That is not the moment it fails. It is the first moment you see the plan as it really is: something you are going to do for a long time. What happens is that your mind compares this feeling to week one, and the comparison is unfair — it measures a sustained state against a temporary one.

"I got bored", at that moment, means precisely: the thing that was exciting has become ordinary. That was always going to happen, whatever the plan was.

## The distance between stopping and adjusting

The problem is not that people get bored. It is that boredom gets read as a verdict: I am not capable of this, this plan is not for me, it happens every time.

There is a great deal of room between stopping and adjusting. A plan adjusted in week three carries on working; a plan abandoned in week three starts again from zero two months later.

CLINICAL_INPUT: When a patient reaches week three and tells you she is bored, what do you actually say? The sentence you really use.

## What follow-up is for

Follow-up does not exist so that somebody can check up on you. It exists for this moment in particular: so that there is somebody who recognises that this is week three, and does not read it as failure.

CLINICAL_INPUT: What is the first thing you change in a plan when somebody reaches this point? The principle, not a specific plan.

## Worth remembering

Week three is not a sign that the plan is wrong. It is a sign that the plan has become part of an ordinary day, which is the thing you wanted from the beginning.

Boredom has an answer, but the answer is not more enthusiasm. It is adjusting the plan so that it works in an ordinary week rather than an excited one.

If you are at that point now, this is not the moment to stop. It is exactly the moment where talking to somebody makes a difference.
EN,
                ],
            ],
            [
                'slug' => 'what-the-scale-does-not-say',
                'category' => 'weight-management',
                'tags' => ['adherence', 'lab-results'],
                'cover' => 'food-kitchen-still-life',
                'title' => [
                    'ar' => 'الميزان بيقول إيه — والحاجات اللي مش بيقولها',
                    'en' => 'What the scale says, and what it does not',
                ],
                'excerpt' => [
                    'ar' => 'قراية واحدة على الميزان فيها حاجات كتير غير اللي إنتِ بتقيسيها. وفي حاجات أهم منها بتتقاس بطريقة تانية.',
                    'en' => 'One reading contains a great deal besides the thing you are trying to measure. And the things that matter more are measured another way.',
                ],
                'body' => [
                    'ar' => <<<'AR'
الميزان بيدي رقم واحد، والرقم ده بيتقري على إنه إجابة. هو مش إجابة — هو قياس لحاجة واحدة، وفيه حاجات كتير جواه مش ليها علاقة بالسؤال.

## القراية الواحدة فيها إيه غير اللي بتدوري عليه

وزن الجسم في أي لحظة بيتأثر بحاجات كتير بتتغير خلال اليوم الواحد: المية، الأكل اللي لسه في الجهاز الهضمي، الهرمونات، النوم، وحتى الملح في وجبة امبارح.

معنى كده إن الفرق بين قرايتين قريبين من بعض ممكن يكون كله من الحاجات دي، من غير ما يكون في أي تغيير في الحاجة اللي بتحاولي تغيريها فعلاً.

CLINICAL_INPUT: إيه أكتر سبب بتشوفيه في العيادة لقراية ميزان بتفزّع مريضة من غير سبب حقيقي؟ جملة.

## ليه الوزن المتكرر بيخلي الصورة أوحش مش أوضح

كل ما تزيد مرات القياس، كل ما الضوضاء تبقى أعلى من الإشارة. القراية اليومية بتوري تغيّرات يومية طبيعية، والعين بتقراها على إنها تقدّم أو تراجع.

النتيجة إن اليوم بيبدأ بحكم على النفس مبني على رقم اتغير لأسباب مالهاش علاقة بالمجهود.

## اللي العيادة بتتابعه غير الرقم

في المتابعة بنسأل عن حاجات تانية: الطاقة خلال اليوم، النوم، الالتزام بالخطة، والتحاليل لما يكون ليها لازمة. الحاجات دي بتوصف الحالة بشكل أقرب للحقيقة من رقم واحد.

CLINICAL_INPUT: إيه الحاجات اللي إنتِ بتتابعيها فعليًا مع المريضة غير الوزن؟ اذكري اللي بتستخدميه.

## طب أقيس إمتى؟

مفيش قاعدة واحدة تنفع كل الناس، والقرار ده بيتاخد مع حد شايف حالتك.

CLINICAL_INPUT: إنتِ بتنصحي بالقياس كل قد إيه، وإمتى بتنصحي حد إنه يبطل يقيس خالص؟

## اللي يستاهل تفتكريه

الميزان أداة، مش حكم. وأي أداة بتبقى مفيدة لما تتقري صح ومؤذية لما تتقري غلط.

لو الرقم بقى أول حاجة بتحددلك مزاجك الصبح، دي مش مشكلة في إرادتك — دي مشكلة في إن الأداة بقت بتستخدمك.
AR,
                    'en' => <<<'EN'
A scale gives one number, and that number gets read as an answer. It is not an answer. It measures one thing, and a great deal inside it has nothing to do with the question.

## What a single reading contains besides what you are looking for

Body weight at any moment is affected by things that change within a single day: water, food still moving through the digestive system, hormones, sleep, even the salt in yesterday's dinner.

Which means the difference between two readings close together can be entirely those things, without any change at all in what you are actually trying to change.

CLINICAL_INPUT: What is the most common cause you see in clinic of a reading that alarms a patient for no real reason? One sentence.

## Why weighing more often makes the picture worse, not clearer

The more often you measure, the louder the noise becomes relative to the signal. A daily reading shows normal daily variation, and the eye reads it as progress or its opposite.

The result is a day that begins with a judgement about yourself, based on a number that moved for reasons unconnected to anything you did.

## What the clinic follows instead

Follow-up asks about other things: energy through the day, sleep, how the plan is actually going, and lab work where there is a reason for it. Those describe the situation far more honestly than a single figure.

CLINICAL_INPUT: What do you actually track with a patient besides weight? List what you use.

## So how often should I weigh?

There is no single rule that fits everybody, and this is a decision made with somebody who has seen your case.

CLINICAL_INPUT: How often do you advise weighing, and when do you advise somebody to stop weighing altogether?

## Worth remembering

A scale is a tool, not a verdict. Any tool is useful read correctly and harmful read badly.

If the number has become the first thing that sets your mood in the morning, that is not a problem with your willpower. It is a problem with a tool that has started using you.
EN,
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function medicalNutrition(): array
    {
        return [
            [
                'slug' => 'what-fix-your-diet-actually-means',
                'category' => 'medical-nutrition',
                'tags' => ['first-visit', 'questions-to-ask'],
                'cover' => 'consultation-desk-wide',
                'title' => [
                    'ar' => 'لما الدكتور يقولك «ظبطي أكلك» — يعني إيه بالظبط؟',
                    'en' => 'When your doctor says "fix your diet" — what does that mean?',
                ],
                'excerpt' => [
                    'ar' => 'الجملة دي بتتقال في كل عيادة تقريبًا، وبتسيب المريضة تدور على إجابة على النت. الجملة الواحدة دي معناها مختلف حسب مين قالها وليه.',
                    'en' => 'It is said in nearly every clinic, and it sends the patient to the internet. The same sentence means different things depending on who said it and why.',
                ],
                'body' => [
                    'ar' => <<<'AR'
«ظبطي أكلك» أكتر جملة بتخرج بيها مريضة من عيادة، وأكتر جملة بتتحوّل لبحث على النت في نفس اليوم.

المشكلة في الجملة مش إنها غلط. المشكلة إنها مش تعليمات — هي عنوان لكلام طويل، والكلام الطويل ده بيختلف من حالة للتانية اختلاف كامل.

## الجملة الواحدة، معاني مختلفة

نفس الجملة بتيجي من دكتور باطنة، ومن دكتورة نسا، ومن دكتور أطفال — وكل واحد فيهم قاصد حاجة مختلفة عن التاني، لأن اللي بيقلقه مختلف.

CLINICAL_INPUT: أشهر تلات سياقات بتيجي فيها الجملة دي للعيادة، وإيه اللي بيكون مقصود في كل واحدة؟ سطر لكل واحدة.

## ليه مفيش إجابة واحدة على النت

اللي على النت مكتوب لجمهور عام. وأي كلام عن الأكل بيبقى نافع أو مؤذي حسب حاجات إنتِ بس ودكتورك عارفينها: التاريخ المرضي، الأدوية، التحاليل، والحياة اليومية اللي الخطة هتعيش جواها.

الحاجات دي هي بالظبط اللي محرك البحث مش شايفه.

## إيه اللي بيحصل في جلسة تغذية إكلينيكية

الجلسة الأولى معظمها أسئلة: عن اليوم العادي، وعن الأكل اللي موجود في البيت فعلاً، وعن الحاجات اللي اتجربت قبل كده وإيه اللي حصل فيها.

CLINICAL_INPUT: إيه أهم تلات أسئلة بتسأليها في الجلسة الأولى، وليه؟

## إيه اللي تجيبيه معاكي

CLINICAL_INPUT: إيه الحاجات اللي المريضة تجيبها معاها عشان الجلسة تبقى مفيدة؟ من غير ما نقول لحد يعمل تحاليل معينة.

## اللي يستاهل تفتكريه

«ظبطي أكلك» مش وصفة، هي إحالة. ومعناها إن في كلام طويل محتاج حد يقعد معاكي يترجمه لحالتك إنتِ.

اللي بيحصل في العيادة هو الترجمة دي — مش نظام جاهز، وإنما خطة مبنية على اللي إنتِ جايباه معاكي.
AR,
                    'en' => <<<'EN'
"Fix your diet" is the sentence patients most often leave a clinic holding, and the one most often typed into a search box the same day.

The problem with it is not that it is wrong. It is that it is not an instruction. It is a heading for a much longer conversation, and that conversation is completely different from one case to the next.

## One sentence, several meanings

The same words arrive from a physician, from an obstetrician, from a paediatrician — and each of them means something different, because each is worried about something different.

CLINICAL_INPUT: The three most common contexts this sentence arrives in, and what is meant in each? One line each.

## Why the internet has no single answer

What is written online is written for a general audience. Any advice about food is helpful or harmful depending on things only you and your doctor know: your history, your medication, your lab work, and the daily life the plan has to survive inside.

Those are precisely the things a search engine cannot see.

## What actually happens in a clinical nutrition session

The first session is mostly questions: about an ordinary day, about the food that is genuinely in your kitchen, and about what has been tried before and what happened.

CLINICAL_INPUT: The three most important questions you ask in a first session, and why?

## What to bring

CLINICAL_INPUT: What should a patient bring so the session is useful? Without telling anybody to go and get particular tests.

## Worth remembering

"Fix your diet" is not a prescription. It is a referral, and it means there is a longer conversation that needs somebody to translate it into your case.

That translation is what happens in the clinic — not a ready-made system, but a plan built on what you brought with you.
EN,
                ],
            ],
            [
                'slug' => 'dietitian-versus-downloadable-plan',
                'category' => 'medical-nutrition',
                'tags' => ['first-visit'],
                'cover' => 'consultation-meal-plan',
                'title' => [
                    'ar' => 'الفرق بين أخصائي التغذية والدايت اللي بتلاقيه أونلاين',
                    'en' => 'A clinical dietitian versus a plan you download',
                ],
                'excerpt' => [
                    'ar' => 'الخطة الجاهزة مش دايمًا غلط. بس في حاجات مبنية على معلومات مفيش طريقة تعرفها من غير ما حد يشوف حالتك.',
                    'en' => 'A template is not always wrong. But some things rest on information nobody can have without seeing your case.',
                ],
                'body' => [
                    'ar' => <<<'AR'
في خطط أكل كتير متاحة مجانًا، وبعضها مكتوب كويس. المقارنة هنا مش بين عيادة وعيادة تانية — هي بين حاجة مكتوبة لجمهور، وحاجة مكتوبة لواحدة.

## الخطة الجاهزة بتعرف إيه ومش بتعرف إيه

الخطة الجاهزة عارفة معلومات عامة عن الأكل. مش عارفة تاريخك المرضي، ولا الأدوية اللي بتاخديها، ولا نتايج تحاليلك، ولا مواعيد يومك، ولا ميزانيتك، ولا الأكل اللي بيتطبخ في بيتك أصلاً.

الحاجات دي مش تفاصيل صغيرة — هي اللي بتحدد إذا كانت الخطة هتكمل أسبوعين ولا ستة شهور.

## إمتى الخطة الجاهزة تنفع فعلاً

ودي حاجة تستاهل تتقال بصراحة: في ناس كتير الخطة العامة تنفعها. حد صحته كويسة، مش بياخد أدوية، وعايز يظبط شكل وجباته — ممكن يستفيد من كلام عام من غير ما يحتاج عيادة.

CLINICAL_INPUT: إمتى بتقولي لحد إنه مش محتاج عيادة أصلاً؟ الحالات اللي بتحوّليها لكلام عام.

## إمتى بتبقى فكرة وحشة

CLINICAL_INPUT: إيه الحالات اللي فيها خطة عامة من النت ممكن تأذي فعلاً؟ من غير تفاصيل — الفئات بس.

## الجزء اللي بيعمل الشغل: المتابعة

أي خطة أول أسبوع بتبان كويسة. اللي بيفرق هو اللي بيحصل لما الحياة تدخل: سفر، شغل ضاغط، مرض، مناسبة.

المتابعة هي اللحظة اللي الخطة بتتعدل فيها عشان تفضل ممكنة، وده الجزء اللي ملف PDF مش بيقدر يعمله.

## اللي يستاهل تفتكريه

السؤال مش «أنهي أحسن». السؤال هو: الخطة دي مبنية على معلومات عن مين؟

لو الإجابة «على حد تاني»، يبقى دي نقطة بداية معقولة. لو حالتك فيها حاجة تخص إنتِ بالذات، يبقى نقطة البداية دي مش كفاية.
AR,
                    'en' => <<<'EN'
There are many eating plans available free, and some of them are written well. The comparison here is not between one clinic and another. It is between something written for an audience and something written for one person.

## What a template knows, and what it does not

A template knows general information about food. It does not know your history, your medication, your lab results, the hours you actually keep, your budget, or what is cooked in your kitchen.

Those are not small details. They are what decides whether a plan lasts a fortnight or six months.

## When a template genuinely is enough

This deserves saying plainly: for a great many people, general advice works. Somebody in good health, on no medication, who wants to tidy up the shape of their meals can be helped by general information without needing a clinic at all.

CLINICAL_INPUT: When do you tell somebody they do not need a clinic? The cases you send away with general advice.

## When it is a bad idea

CLINICAL_INPUT: In which situations can a general plan from the internet actually cause harm? Categories, not details.

## The part that does the work: follow-up

Any plan looks good in week one. What matters is what happens when life arrives: travel, a hard week at work, illness, a family occasion.

Follow-up is the moment the plan gets adjusted so it stays possible, and that is the part a PDF cannot do.

## Worth remembering

The question is not which is better. It is: who is this plan built on information about?

If the answer is somebody else, it is a reasonable starting point. If there is something about your case that is specific to you, a starting point is not enough.
EN,
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function labReview(): array
    {
        return [
            [
                'slug' => 'normal-results-still-tired',
                'category' => 'lab-review',
                'tags' => ['lab-results', 'questions-to-ask'],
                'featured' => true,
                'cover' => 'food-clinical-flatlay',
                'title' => [
                    'ar' => 'تحاليلك «طبيعية» — طب ليه لسه تعبانة؟',
                    'en' => 'Your results are "normal" — so why are you still tired?',
                ],
                'excerpt' => [
                    'ar' => 'كلمة «طبيعي» على ورقة تحليل معناها إحصائي، مش إكلينيكي. المقال ده عن الفرق، ومن غير أي أرقام تقارني نفسك بيها.',
                    'en' => 'The word "normal" on a lab report is a statistical statement, not a clinical one. This is about the difference — with no figures to measure yourself against.',
                ],
                'body' => [
                    'ar' => <<<'AR'
بتاخدي ورقة التحاليل، وكل حاجة مكتوب جنبها إنها في النطاق الطبيعي. وبرضه إنتِ تعبانة.

الإحساس اللي بيجي بعدها غالبًا إن حد مش مصدقك، أو إن التعب ده «من دماغك». المقال ده عن حاجة تالتة: إن كلمة «طبيعي» بتقول حاجة أضيق بكتير من اللي إحنا فاهمينها.

## النطاق الطبيعي جاي منين

النطاق اللي مطبوع على ورقة التحليل مش حد بين الصحة والمرض. هو وصف إحصائي لنتايج مجموعة كبيرة من الناس اللي اتعملهم نفس التحليل.

معنى كده إن النطاق بيقول: «معظم الناس نتيجتهم بتقع هنا». مش بيقول: «لو نتيجتك هنا يبقى إنتِ كويسة».

## ليه الاتجاه أهم من القراية الواحدة

نتيجة واحدة هي نقطة. نقطة واحدة مش بتوري اتجاه.

اللي بيتقري في العيادة هو العلاقة بين النتايج عبر الوقت، والعلاقة بين التحاليل وبعضها، والعلاقة بين ده كله وبين اللي إنتِ حاساه فعلاً.

CLINICAL_INPUT: ليه نتيجة واحدة مش كفاية، بشكل عام ومن غير ما نسمي تحليل معين ولا نطاق؟

## «طبيعي» مش نفس معنى «مفيش حاجة»

في فرق بين إن التحليل ما لقاش حاجة، وبين إن مفيش حاجة. التحليل بيدوّر على حاجات معينة بس، والتعب المستمر ليه أسباب كتير مش كلها بتظهر في تحليل دم.

CLINICAL_INPUT: لما تحاليل مريضة تبقى في النطاق وهي لسه تعبانة، إيه اللي بتفكري فيه بعد كده؟ الاتجاه العام بس.

## ده سؤال لمين

الجزء ده مهم: قراية التحاليل قرار طبي. أخصائي التغذية بيشتغل جنب الدكتور المعالج، مش بدله.

CLINICAL_INPUT: إمتى الكلام ده يبقى سؤال لدكتور باطنة، وإمتى يبقى ليكي؟

## اللي يستاهل تفتكريه

«طبيعي» كلمة إحصائية. وإحساسك مش نتيجة تحليل — هو معلومة كمان، والمفروض تتحط جنب الورقة مش تحتها.

لو التعب مستمر، ده يستاهل محادثة، مش تطمين.
AR,
                    'en' => <<<'EN'
You take the report, and everything on it is marked as within the normal range. And you are still tired.

What usually follows is a feeling that nobody believes you, or that the tiredness is in your head. This article is about a third possibility: that the word "normal" says something far narrower than we take it to mean.

## Where a reference range comes from

The range printed on the report is not a line between health and illness. It is a statistical description of the results of a large group of people who had the same test.

Which means the range says: most people's results fall here. It does not say: if your result is here, you are well.

## Why a trend matters more than one reading

A single result is a point. One point shows no direction.

What gets read in clinic is the relationship between results over time, the relationship between different tests, and the relationship between all of that and what you actually feel.

CLINICAL_INPUT: Why is a single result not enough — in general terms, without naming a test or a range?

## "Normal" is not the same as "nothing"

There is a difference between a test not finding something and there being nothing to find. A test looks for particular things, and persistent tiredness has many causes, not all of which appear in a blood test.

CLINICAL_INPUT: When a patient's results are in range and she is still tired, what do you think about next? The general direction only.

## Whose question is this

This part matters: reading lab results is a medical decision. A dietitian works alongside the treating doctor, not instead of them.

CLINICAL_INPUT: When does this become a question for a physician, and when is it one for you?

## Worth remembering

"Normal" is a statistical word. And what you feel is not a test result — it is another piece of information, and it belongs beside the report rather than underneath it.

If the tiredness persists, that deserves a conversation, not reassurance.
EN,
                ],
            ],
            [
                'slug' => 'what-to-bring-to-a-first-appointment',
                'category' => 'lab-review',
                'tags' => ['first-visit', 'questions-to-ask'],
                'cover' => 'food-cookbook-overhead',
                'title' => [
                    'ar' => 'إيه اللي تجيبيه معاكي في أول جلسة',
                    'en' => 'What to bring to a first appointment',
                ],
                'excerpt' => [
                    'ar' => 'قايمة عملية للورق واللي تكتبيه قبل الجلسة، وإيه اللي بيحصل فيها. وملف ناقص برضه يستاهل تجيبيه.',
                    'en' => 'A practical list of papers and notes, and what happens in the session. An incomplete file is still worth bringing.',
                ],
                'body' => [
                    'ar' => <<<'AR'
الجلسة الأولى بتبقى أنفع لما تيجي ومعاكي حاجات معينة. المقال ده مش بيقولك تعملي تحاليل — الطلب ده قرار طبي مش بتاعنا. هو بيقولك تجيبي اللي عندك.

## الورق

CLINICAL_INPUT: إيه الورق اللي تجيبه المريضة معاها، ومن آد إيه بيبقى مفيد؟

الحاجة المهمة هنا: لو الورق ناقص، تعالي برضه. ملف ناقص أحسن من جلسة متأجلة، والناقص ممكن يتكمل بعدين.

## الأدوية والمكملات

اكتبي كل حاجة بتاخديها — دوا، مكمل، فيتامين، أعشاب. الاسم والجرعة لو تعرفيها، ولو مش فاكرة، صوّري العلبة.

ده مش فضول: في حاجات بتتفاعل مع بعض، والدكتورة لازم تعرف اللي إنتِ واخداه فعلاً مش اللي المفروض تاخديه.

## اللي تكتبيه في الأسبوع اللي قبل الجلسة

مش دفتر أكل. حاجات بسيطة: اليوم بيبدأ إمتى وبيخلص إمتى، بتاكلي فين غالبًا، والأوقات اللي بتحسي فيها بجوع أو تعب.

CLINICAL_INPUT: إيه الحاجات اللي نفسك المريضة تلاحظها في الأسبوع اللي قبل الجلسة؟

## إيه اللي بيحصل في الجلسة نفسها

معظمها أسئلة وكلام. مفيش وزن إجباري، ومفيش حكم على اللي حصل قبل كده.

CLINICAL_INPUT: بتبدأي الجلسة بإيه، وإيه اللي بيخرج بيه المريض في آخرها؟

## اللي يستاهل تفتكريه

الجلسة الأولى مش امتحان، وإنتِ مش محتاجة تكوني «جاهزة». الحاجات اللي فوق بتخلي الوقت يتصرف في الكلام المفيد بدل ما يتصرف في تجميع معلومات.

ولو مجبتيش حاجة خالص — تعالي. ده أحسن من إنك تأجلي.
AR,
                    'en' => <<<'EN'
A first session is more useful when you arrive with certain things. This article does not tell you to go and get tests — ordering tests is a medical decision and not ours. It tells you to bring what you already have.

## The papers

CLINICAL_INPUT: What papers should a patient bring, and how far back are they useful?

The important thing: if the file is incomplete, come anyway. An incomplete file beats a postponed session, and gaps can be filled later.

## Medication and supplements

Write down everything you take — medicine, supplement, vitamin, herbal. Name and dose if you know them, and if you cannot remember, photograph the box.

This is not curiosity. Some things interact, and the clinician needs to know what you are actually taking rather than what you are supposed to be taking.

## What to note in the week before

Not a food diary. Simple things: when your day starts and ends, where you usually eat, and the times you feel hungry or tired.

CLINICAL_INPUT: What would you like a patient to notice in the week before her session?

## What happens in the session itself

Mostly questions and conversation. No compulsory weighing, and no judgement about what came before.

CLINICAL_INPUT: How do you open the session, and what does the patient leave with?

## Worth remembering

A first session is not an examination, and you do not need to arrive "ready". The things above simply mean the time is spent on the useful conversation rather than on assembling information.

And if you bring nothing at all — come. That is better than postponing.
EN,
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pcos(): array
    {
        return [
            [
                'slug' => 'pcos-and-food-judging-a-claim',
                'category' => 'pcos-hormonal',
                'tags' => ['myths', 'questions-to-ask'],
                'cover' => 'food-vegetables-overhead',
                'title' => [
                    'ar' => 'تكيس المبايض والأكل: إزاي تحكمي على أي كلام بتقريه',
                    'en' => 'PCOS and food: how to judge what you read',
                ],
                'excerpt' => [
                    'ar' => 'النت مليان أنظمة لتكيس المبايض. المقال ده مش نظام كمان — هو طريقة تفرّقي بيها بين ادعاء وادعاء.',
                    'en' => 'The internet is full of PCOS diets. This is not another one — it is a way of telling one claim from another.',
                ],
                'body' => [
                    'ar' => <<<'AR'
لو دوّرتي على «أكل تكيس المبايض» هتلاقي عشرات الأنظمة، وكل واحد فيهم واثق. وبعضهم بيقول عكس التاني بالظبط.

المقال ده مش هيضيف نظام تلاتة وعشرين. هو عن حاجة أنفع: إزاي تقري ادعاء وتعرفي هو مبني على إيه.

## أول حاجة: التشخيص الواحد مش حالة واحدة

اتنين عندهم نفس التشخيص ممكن يكون عندهم صور مختلفة تمامًا — أعراض مختلفة، تحاليل مختلفة، وأولويات علاجية مختلفة.

ده لوحده بيفسّر ليه نظام «نجح مع واحدة» مش بالضرورة له أي علاقة بيكي. هي مش نفس الحالة.

CLINICAL_INPUT: ليه الحالة بتختلف من واحدة للتانية بنفس التشخيص؟ جملتين بشكل عام.

## علامات إن الكلام يستاهل شك

في أشكال كلام بتتكرر ومعظمها مش بيصمد:

الكلام اللي بيوعد بنتيجة مؤكدة. الكلام اللي بيمنع مجموعة أكل كاملة من غير سبب مرتبط بحالتك. الكلام اللي بيبيع منتج في آخره. والكلام اللي بيقول «الدراسات أثبتت» من غير ما يقول أنهي دراسة.

## أسئلة تسأليها لأي ادعاء

مين اللي بيقول ده؟ مبني على إيه؟ بيتكلم عن حالة زي حالتي ولا حالة تانية؟ وهو عايز مني أشتري حاجة؟

الأسئلة دي مش هتخليكي خبيرة. هي هتخليكي تفرّقي بين كلام عام وكلام متسرع.

## مين بيقرر الخطة

خطة الأكل في الحالة دي بتتبني مع الدكتور المعالج، مش بعيد عنه. في قرارات — خصوصًا اللي ليها علاقة بالأدوية — مش قرارات أخصائي تغذية.

CLINICAL_INPUT: إيه دور أخصائي التغذية بالظبط جنب الدكتور في الحالة دي، وإيه اللي مش بتقرريه إنتِ؟

## اللي يستاهل تفتكريه

مفيش نظام واحد لتكيس المبايض، وأي حد بيقولك غير كده بيقولك حاجة أبسط من الحقيقة.

اللي ينفع هو خطة مبنية على حالتك إنتِ، ومتعملة مع الناس اللي شايفين الحالة دي.
AR,
                    'en' => <<<'EN'
Search for "PCOS diet" and you will find dozens of systems, each of them confident. Some of them say the exact opposite of the others.

This article is not going to add a twenty-third. It is about something more useful: how to read a claim and see what it rests on.

## First: one diagnosis is not one condition

Two people with the same diagnosis can present completely differently — different symptoms, different results, different treatment priorities.

That alone explains why a system that "worked for someone" may have nothing to do with you. She is not the same case.

CLINICAL_INPUT: Why does the picture differ so much between two people with the same diagnosis? Two sentences, general.

## Signs a claim deserves scepticism

Certain shapes recur, and most of them do not hold up:

Anything promising a guaranteed result. Anything removing an entire food group for a reason unconnected to your case. Anything selling a product at the end. And anything that says "studies show" without saying which study.

## Questions to put to any claim

Who is saying this? What is it based on? Is it about a case like mine or a different one? And do they want me to buy something?

These will not make you an expert. They will let you tell careful information from a hurried claim.

## Who decides the plan

An eating plan here is built with the treating doctor, not away from them. Some decisions — particularly anything touching medication — are not a dietitian's to make.

CLINICAL_INPUT: What exactly is the dietitian's role alongside the doctor here, and what is not yours to decide?

## Worth remembering

There is no single PCOS diet, and anyone telling you otherwise is telling you something simpler than the truth.

What works is a plan built on your case, made with the people who are actually looking at it.
EN,
                ],
            ],
            [
                'slug' => 'questions-to-ask-about-hormones-and-food',
                'category' => 'pcos-hormonal',
                'tags' => ['questions-to-ask'],
                'cover' => 'food-market-counter',
                'title' => [
                    'ar' => 'أسئلة تسأليها لدكتورتك عن التغذية والهرمونات',
                    'en' => 'Questions worth asking your doctor about food and hormones',
                ],
                'excerpt' => [
                    'ar' => 'المقال ده مش بيدي إجابات. بيدي أسئلة أحسن تروحي بيها للعيادة، وطريقة تسجلي بيها الإجابات.',
                    'en' => 'This article gives no answers. It gives better questions to take to your appointment, and a way to record what you are told.',
                ],
                'body' => [
                    'ar' => <<<'AR'
أصعب حاجة في زيارة قصيرة إنك تخرجي وإنتِ فاكرة إن في حاجة نسيتي تسأليها. المقال ده قايمة أسئلة، مش قايمة إجابات — والإجابات هي اللي إنتِ هتجيبيها من عند حد شايف حالتك.

## اسألي: التشخيص ده بيغيّر إيه في أكلي؟

سؤال مباشر وبيفتح الباب. الإجابة ممكن تبقى «مفيش حاجة كتير» — ودي إجابة كويسة برضه، ولها قيمة إنك تسمعيها من حد بدل ما تدوري عليها.

CLINICAL_INPUT: إيه الإجابة العامة اللي بتقوليها على السؤال ده أكتر حاجة؟

## اسألي: المكمل ده بيعمل إيه، وإزاي هنعرف إنه بيعمل حاجة؟

الجزء التاني من السؤال هو المهم. حاجة من غير طريقة نعرف بيها إنها اشتغلت بتفضل معاكي سنين من غير سبب.

## اسألي: أتوقع إيه، وفي خلال قد إيه؟

من غير السؤال ده بيبقى صعب تفرّقي بين خطة محتاجة وقت وخطة مش شغالة.

CLINICAL_INPUT: إزاي بتوصّفي التوقعات لمريضة من غير ما تدي وعود؟

## اسألي: مين أرجعله لو حصل كذا؟

ودي أهم واحدة. عايزة تعرفي مين تكلمي لو حصلت حاجة، وإمتى الحاجة دي تبقى مستعجلة.

CLINICAL_INPUT: إيه الحاجات اللي لو حصلت المريضة تتصل فورًا؟

## اكتبي الإجابات وإنتِ قاعدة

مش بعدين. الذاكرة بتعيد صياغة الكلام الطبي بعد ساعة، وبتحوّل «ممكن» لـ«لازم».

## اللي يستاهل تفتكريه

الأسئلة دي مش بتخليكي دكتورة. هي بتخلي الزيارة القصيرة تطلع بأكبر قدر ممكن، وبتخليكي تخرجي بحاجة مكتوبة بدل انطباع.
AR,
                    'en' => <<<'EN'
The hardest part of a short appointment is leaving it certain you forgot to ask something. This is a list of questions, not of answers — the answers come from somebody looking at your case.

## Ask: what does this diagnosis change about my food?

Direct, and it opens the door. The answer may well be "not very much" — which is a good answer, and worth hearing from somebody rather than hunting for.

CLINICAL_INPUT: What is the general answer you give to this question most often?

## Ask: what is this supplement doing, and how will we know if it is doing it?

The second half is the important half. Anything without a way of telling whether it worked stays with you for years for no reason.

## Ask: what should I expect, and over what period?

Without this it is hard to tell a plan that needs time from a plan that is not working.

CLINICAL_INPUT: How do you set expectations without making promises?

## Ask: who do I come back to if X happens?

The most important one. You want to know who to contact if something happens, and when that something is urgent.

CLINICAL_INPUT: What should make a patient call immediately?

## Write the answers down while you are sitting there

Not afterwards. Memory rewrites medical conversations within the hour, and turns "might" into "must".

## Worth remembering

These questions do not make you a doctor. They make a short appointment produce as much as it can, and they mean you leave with something written rather than an impression.
EN,
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pregnancy(): array
    {
        return [
            [
                'slug' => 'pregnancy-eating-myths',
                'category' => 'pregnancy-nutrition',
                'tags' => ['myths', 'family'],
                'cover' => 'pregnancy-bump',
                'title' => [
                    'ar' => 'الحمل والأكل: الخرافات اللي بتتقال في كل بيت',
                    'en' => 'Eating in pregnancy: the things everyone tells you',
                ],
                'excerpt' => [
                    'ar' => 'مين ما قالكيش «إنتِ بتاكلي لاتنين»؟ المقال ده عن الكلام ده جاي منين، وإمتى يبقى سؤال لدكتورة النسا.',
                    'en' => 'Everybody says "you are eating for two". This is about where that comes from, and when it becomes a question for your obstetrician.',
                ],
                'body' => [
                    'ar' => <<<'AR'
أول ما الخبر يتقال، بيبدأ الكلام. كل واحدة في العيلة عندها قاعدة، ومعظم القواعد دي متقالة بنية طيبة وبتتناقل من غير ما حد يسأل هي جت منين.

المقال ده مش هيقولك تاكلي إيه — ده كلام بيتقال مع دكتورة النسا اللي شايفة حملك. هو عن الكلام نفسه.

## «إنتِ بتاكلي لاتنين»

أشهر جملة، وأكترها تسبب قلق. الجملة دي بتخلي أي أكل قليل يبقى ذنب وأي أكل كتير يبقى واجب.

CLINICAL_INPUT: الرد العام على الجملة دي إيه، من غير أرقام ولا كميات؟

## «الوحام معناه جسمك محتاج الحاجة دي»

فكرة مريحة، وبتستخدم كتفسير لأي حاجة. مفيش طريقة تعرفي بيها إن الرغبة دي معناها نقص في حاجة معينة.

## الأكل اللي العيلة بتمنعه

كل بيت عنده قايمة ممنوعات، والقوايم دي بتختلف من بيت لبيت — وده لوحده مؤشر على إنها تقاليد مش طب.

في حاجات فعلاً ليها سبب طبي تتجنب في الحمل، وفي حاجات القايمة بتاعتها اتوارثت.

CLINICAL_INPUT: إزاي المريضة تفرّق بين ممنوع ليه سبب وممنوع موروث؟ من غير ما نسمي أكل معين.

## الحاجات بتختلف على مدار الحمل

اللي مناسب في شهر مش بالضرورة نفس اللي مناسب في شهر تاني، ودي حاجة معظم النصايح العامة مش بتفرّق فيها.

## ده سؤال لمين

كل قرار يخص الحمل بيمر على دكتورة النسا. أخصائي التغذية بيشتغل جنبها، ومفيش حاجة في المقال ده تعدّل خطة اتحطت من دكتورة شايفة حالتك.

CLINICAL_INPUT: إمتى المريضة تكلم دكتورة النسا فورًا؟ العلامات اللي متستناش.

## اللي يستاهل تفتكريه

الكلام اللي بيتقال في البيت غالبًا نيته كويسة. بس نية كويسة مش نفس معلومة صحيحة.

ولو حاجة قلقتك — دي بالظبط الحاجة اللي تتسأل، مش اللي يتبحث عنها على النت.
AR,
                    'en' => <<<'EN'
The moment the news is shared, the advice starts. Everyone in the family has a rule, and most of those rules are offered kindly and passed on without anybody asking where they came from.

This article will not tell you what to eat — that conversation belongs with the obstetrician who is seeing your pregnancy. It is about the advice itself.

## "You are eating for two"

The most common sentence, and the one that causes most anxiety. It turns eating little into guilt and eating a lot into duty.

CLINICAL_INPUT: What is the general reply to this, with no figures or quantities?

## "A craving means your body needs it"

A comforting idea, used to explain anything. There is no way to know that a craving indicates a shortage of anything in particular.

## The foods the family forbids

Every household has a list, and the lists differ from house to house — which is itself a sign that they are tradition rather than medicine.

Some things genuinely are avoided in pregnancy for a medical reason. Others are on the list because they always have been.

CLINICAL_INPUT: How can a patient tell a restriction with a reason from an inherited one? Without naming specific foods.

## Things change across a pregnancy

What suits one month is not necessarily what suits another, and general advice rarely makes that distinction.

## Whose question is this

Every decision about a pregnancy goes through the obstetrician. A dietitian works alongside her, and nothing in this article changes a plan set by a doctor who is seeing your case.

CLINICAL_INPUT: When should a patient contact her obstetrician immediately? The signs that do not wait.

## Worth remembering

What gets said at home is usually said kindly. Kindness is not the same as accuracy.

And if something has worried you, that is precisely the thing to ask about rather than search for.
EN,
                ],
            ],
            [
                'slug' => 'feeding-and-eating-recurring-questions',
                'category' => 'pregnancy-nutrition',
                'tags' => ['questions-to-ask', 'family'],
                'cover' => 'infant-feeding-hands',
                'title' => [
                    'ar' => 'الرضاعة والأكل: الأسئلة اللي بتتكرر',
                    'en' => 'Feeding and eating: the questions that keep coming up',
                ],
                'excerpt' => [
                    'ar' => 'أسئلة بتتكرر في الشهور الأولى، ومين المفروض تسأليه في كل واحدة.',
                    'en' => 'The questions that recur in the early months, and who to take each one to.',
                ],
                'body' => [
                    'ar' => <<<'AR'
الشهور الأولى فيها أسئلة كتير ووقت قليل. المقال ده بيرتّب الأسئلة اللي بتتكرر ويقول كل واحدة تروح لمين.

## الجوع والعطش بيتغيروا

كتير بيوصفوا إحساس بجوع أو عطش مختلف عن المعتاد في الفترة دي. ده شائع بالدرجة اللي يخليه مش مقلق في حد ذاته.

CLINICAL_INPUT: إيه اللي بيتغيّر في الفترة دي بشكل عام، ومن غير أرقام؟

## «لبني مش كفاية»

الجملة دي بتتقال كتير جدًا، وغالبًا بتتبني على علامات مش دقيقة. وهي مش سؤال أكل — دي حاجة تتقاس بطريقة تانية خالص مع حد مختص.

CLINICAL_INPUT: الجملة دي سؤال لمين بالظبط، وإيه اللي بيتشاف قبل ما حد يتكلم عن الأكل؟

## الأكل اللي بيتقال إنه «بيزوّد» أو «بيقلّل»

كل بيت عنده قايمة. القوايم دي بتختلف، وده بيقول عليها حاجة.

## النوم والوقت

أكبر عائق في الفترة دي مش المعرفة، هو الوقت. أي خطة مش واخدة في اعتبارها إن اليوم مقطّع مش هتكمل أسبوع.

CLINICAL_INPUT: إزاي بتعدّلي خطة لواحدة نومها مقطّع ووقتها مش في إيدها؟ المبدأ.

## اللي يستاهل ترفعيه في زيارة ما بعد الولادة

CLINICAL_INPUT: إيه الحاجات اللي تستاهل تتقال في زيارة ما بعد الولادة وبتتنسي غالبًا؟

## اللي يستاهل تفتكريه

معظم الأسئلة في الفترة دي مش أسئلة أكل، حتى لما تبقى متسألة عن الأكل.

والحاجة اللي تستاهل تتعمل: تكتبي السؤال أول ما ييجي، عشان الزيارة القصيرة تلاقيه مكتوب.
AR,
                    'en' => <<<'EN'
The early months hold many questions and very little time. This article sorts the recurring ones and says who each belongs to.

## Hunger and thirst change

Many people describe hunger or thirst that feels different in this period. It is common enough not to be worrying in itself.

CLINICAL_INPUT: What changes in this period, in general terms and without figures?

## "My milk is not enough"

This is said very often, and usually rests on signs that are not reliable. It is not a question about food — it is measured a completely different way, with somebody qualified.

CLINICAL_INPUT: Who exactly is this question for, and what gets looked at before anybody discusses food?

## The foods said to increase or reduce supply

Every household has a list. The lists disagree, which tells you something.

## Sleep and time

The biggest obstacle here is not knowledge, it is time. Any plan that does not account for a day cut into pieces will not last a week.

CLINICAL_INPUT: How do you adjust a plan for somebody whose sleep is broken and whose time is not her own? The principle.

## What to raise at the postnatal visit

CLINICAL_INPUT: What is worth raising at a postnatal visit that usually gets forgotten?

## Worth remembering

Most questions in this period are not questions about food, even when they are asked about food.

And the thing worth doing: write the question down when it arrives, so the short appointment finds it already written.
EN,
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function child(): array
    {
        return [
            [
                'slug' => 'the-child-who-will-not-eat',
                'category' => 'child-nutrition',
                'tags' => ['family', 'myths'],
                'cover' => 'food-fruit-bowl',
                'title' => [
                    'ar' => 'الطفل اللي «مش بياكل»',
                    'en' => 'The child who "will not eat"',
                ],
                'excerpt' => [
                    'ar' => 'الجملة دي بتتقال كتير، وغالبًا بتوصف حاجة مختلفة عن اللي بتبان. وأول حد يشوفها مش أخصائي التغذية.',
                    'en' => 'A sentence heard constantly, usually describing something other than it appears. And the first person to see it is not the dietitian.',
                ],
                'body' => [
                    'ar' => <<<'AR'
«مش بياكل خالص» جملة بتتقال في كل بيت فيه طفل. ولما نقعد نتكلم، بيطلع إن الصورة مختلفة عن الجملة.

## الجملة دي بتوصف إيه غالبًا

في الغالب الطفل بياكل، بس بياكل حاجات قليلة، أو بياكل في أوقات مش أوقات السفرة، أو بياكل قدام حد ومش قدام حد تاني.

الفرق ده مهم لأن كل صورة فيهم ليها تفسير مختلف.

CLINICAL_INPUT: أشهر تلات صور بتيجي تحت جملة «مش بياكل»؟ سطر لكل واحدة.

## الضغط على السفرة بيزوّد المشكلة

ده الجزء اللي بيفاجئ الأهل: كل ما الضغط يزيد، كل ما الأكل يقل. السفرة بتتحول لمعركة، والأكل بيبقى موضوع المعركة مش هدفها.

## مين بيشوف الطفل الأول

دكتور الأطفال. في حاجات لازم تتشاف قبل ما حد يتكلم عن الأكل أصلاً، ودي مش حاجات أخصائي التغذية بيقررها.

CLINICAL_INPUT: إيه اللي دكتور الأطفال بيتأكد منه الأول، وليه ده قبل أي كلام عن الأكل؟

## أخصائي التغذية بيدخل إمتى

بعد ما الجزء الطبي يتطمن عليه. ساعتها الشغل بيبقى على شكل الوجبة والروتين والتعامل، مش على كميات.

CLINICAL_INPUT: بعد ما دكتور الأطفال يطمّن، إنتِ بتشتغلي على إيه بالظبط مع العيلة؟

## اللي يستاهل تفتكريه

«مش بياكل» جملة قلق، ونادرًا ما تكون وصف دقيق. وأول خطوة مش تغيير الأكل — أول خطوة إن حد يشوف الطفل.

ولو السفرة بقت مكان توتر يومي، دي حاجة تستاهل تتحل بنفس أهمية الأكل نفسه.
AR,
                    'en' => <<<'EN'
"He does not eat at all" is said in every house with a child in it. And when you sit down and talk, the picture turns out to be different from the sentence.

## What the sentence usually describes

Most often the child does eat, but eats a narrow range, or eats outside mealtimes, or eats in front of one person and not another.

The distinction matters, because each of those has a different explanation.

CLINICAL_INPUT: The three most common pictures that arrive under "will not eat"? One line each.

## Pressure at the table makes it worse

This is the part that surprises parents: the more pressure, the less eating. The table becomes a battleground, and food becomes the subject of the battle rather than its object.

## Who sees the child first

The paediatrician. Some things must be looked at before anybody discusses food at all, and they are not a dietitian's to decide.

CLINICAL_INPUT: What does the paediatrician rule out first, and why does that come before any conversation about food?

## When the dietitian comes in

Once the medical part is settled. The work then is about the shape of a meal, the routine and the handling — not about quantities.

CLINICAL_INPUT: Once the paediatrician has cleared it, what exactly do you work on with the family?

## Worth remembering

"Will not eat" is a sentence of worry, and rarely an accurate description. The first step is not changing the food — it is having somebody see the child.

And if the table has become a daily source of tension, that is worth solving with as much seriousness as the eating itself.
EN,
                ],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function sports(): array
    {
        return [
            [
                'slug' => 'eating-around-training',
                'category' => 'sports-nutrition',
                'tags' => ['supplements', 'myths'],
                'cover' => 'pantry-jars-legumes',
                'title' => [
                    'ar' => 'الأكل حوالين التمرين: إيه اللي بيتقال وإيه اللي بيتطبق',
                    'en' => 'Eating around training: what gets said and what applies',
                ],
                'excerpt' => [
                    'ar' => 'معظم الكلام عن الأكل والتمرين مكتوب لرياضيين محترفين. لو بتتمرني تلات مرات في الأسبوع، الصورة مختلفة.',
                    'en' => 'Most of what is written about food and training is written for competitive athletes. Training three times a week is a different picture.',
                ],
                'body' => [
                    'ar' => <<<'AR'
لو دوّرتي على الأكل قبل وبعد التمرين، هتلاقي كلام كتير دقيق جدًا: توقيتات، ونسب، ومكملات. ومعظمه مكتوب لناس شغلهم إنهم يتمرنوا.

## الفرق بين المحترف وبين حد بيتمرن بعد الشغل

الرياضي المحترف بيتمرن كل يوم أو أكتر، وتحت إشراف، والفروق الصغيرة عنده ليها معنى لأنها بتتراكم.

حد بيتمرن تلات مرات في الأسبوع بعد يوم شغل صورته مختلفة تمامًا: الأولوية عنده إنه يوصل للتمرين أصلاً ويرجع من غير ما يتعب اليوم اللي بعده.

## الحاجات اللي بتتقال وهي مش مهمة للأغلبية

في تفاصيل بتاخد مساحة كبيرة في الكلام وهي في الآخر ليها تأثير صغير على حد بيتمرن معدل عادي.

CLINICAL_INPUT: إيه الحاجات اللي الناس بتسأل عنها كتير وهي مش أولوية لحد بيتمرن تلات مرات في الأسبوع؟

## المكملات: أكتر سؤال وأقل إجابة

ده أكتر سؤال بيتسأل، وأقل سؤال ليه إجابة عامة. أي كلام عن مكمل من غير معرفة بحالتك وتحاليلك وأدويتك بيبقى تخمين.

CLINICAL_INPUT: إيه موقفك العام من المكملات لحد بيتمرن بشكل عادي، ومتى بتبقى محادثة تستاهل؟

## إمتى ده يستاهل عيادة أصلاً

وده يستاهل يتقال بصراحة: كتير من الناس مش محتاجين عيادة تغذية عشان يتمرنوا تلات مرات في الأسبوع.

CLINICAL_INPUT: إمتى الرياضة بتبقى سبب حقيقي لزيارة عيادة تغذية؟

## اللي يستاهل تفتكريه

أول سؤال مش «أكل إيه قبل التمرين». أول سؤال: الكلام ده مكتوب لمين؟

ولو الإجابة «لحد بيتمرن يوميًا تحت إشراف»، يبقى معظمه مش موجّه ليكي.
AR,
                    'en' => <<<'EN'
Search for what to eat before and after training and you will find very precise advice: timings, ratios, supplements. Most of it is written for people whose job is training.

## The difference between a professional and somebody training after work

A competitive athlete trains daily or more, under supervision, and small differences matter because they accumulate.

Somebody training three times a week after a day's work is a completely different picture: the priority is getting to the session at all and getting home without wrecking the following day.

## Things that get discussed and do not matter for most people

Some details take up a great deal of the conversation and have a small effect on somebody training at an ordinary rate.

CLINICAL_INPUT: What do people ask about most that is not a priority for somebody training three times a week?

## Supplements: the most asked, the least answerable

The most common question, and the one with the least general answer. Any advice about a supplement without knowing your case, your results and your medication is guesswork.

CLINICAL_INPUT: What is your general position on supplements for somebody training normally, and when does it become a conversation worth having?

## When this is worth a clinic at all

Worth saying plainly: many people do not need a nutrition clinic in order to train three times a week.

CLINICAL_INPUT: When does training become a real reason to see a nutrition clinic?

## Worth remembering

The first question is not what to eat before training. It is: who was this written for?

If the answer is somebody training daily under supervision, most of it is not addressed to you.
EN,
                ],
            ],
        ];
    }
}
