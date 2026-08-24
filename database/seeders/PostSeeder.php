<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PostSeeder extends Seeder
{
    /*
     * cover_path holds a PHOTO LIBRARY SLUG, not a filesystem path.
     *
     * The column predates the photography pipeline. What goes in it is a key
     * into config/photos.php, which is what lets one image be served at three
     * widths with exact dimensions and an alt in both languages — a raw path
     * could carry none of that. App\Support\Photo resolves it.
     *
     * Each cover was chosen to illustrate its own article, not to decorate it:
     * the budget-protein piece gets the counter with chickpeas and meat on it,
     * the lab-results piece gets the flat-lay with the stethoscope. An article
     * with no matching image gets none.
     */
    public function run(): void
    {
        $posts = [
            [
                'slug' => 'building-a-habit-that-lasts',
                'cover_path' => 'food-cookbook-overhead',
                'title' => [
                    'ar' => 'كيف تبني عادة غذائية تستمر معك',
                    'en' => 'How to build an eating habit that lasts',
                ],
                'excerpt' => [
                    'ar' => 'معظم الأنظمة الغذائية تفشل لأنها تعتمد على الإرادة وحدها. الحل يبدأ من خطوة أصغر بكثير مما تتخيل.',
                    'en' => 'Most diets fail because they lean on willpower alone. The fix starts with a far smaller step than you would expect.',
                ],
                'body' => [
                    'ar' => "العادة الغذائية الناجحة لا تبدأ بقرار كبير، بل بخطوة صغيرة تتكرر يومياً حتى تصبح جزءاً من روتينك بلا مجهود.\n\nالخطأ الشائع أن نبدأ بتغيير كل شيء دفعة واحدة: نغيّر الفطار والغداء والعشاء وننضم لصالة رياضية في نفس الأسبوع. هذا الأسلوب يستهلك طاقتك الذهنية بالكامل، وعند أول يوم ضاغط في العمل ينهار كل شيء معاً.\n\nابدأ بعادة واحدة فقط لمدة أسبوعين: كوب ماء قبل كل وجبة، أو مصدر بروتين في الفطار. عندما تصبح تلقائية، أضف الثانية. التقدم البطيء الذي يبقى أفضل من التقدم السريع الذي يتبخر بعد شهر.",
                    'en' => "A habit that lasts does not start with a big decision. It starts with a small step, repeated daily, until it becomes part of your routine without effort.\n\nThe common mistake is changing everything at once: breakfast, lunch, dinner and a gym membership all in the same week. That approach consumes your entire mental budget, and the first stressful day at work brings the whole structure down together.\n\nStart with a single habit for two weeks — a glass of water before each meal, or a protein source at breakfast. Once it is automatic, add the second. Slow progress that stays beats fast progress that evaporates after a month.",
                ],
                'category' => ['ar' => 'تغذية', 'en' => 'Nutrition'],
                'reading_minutes' => 4,
                'published_at' => Carbon::now()->subDays(7),
                'is_featured' => true,
            ],
            [
                'slug' => 'protein-on-an-egyptian-budget',
                'cover_path' => 'food-market-counter',
                'title' => [
                    'ar' => 'البروتين بميزانية مصرية',
                    'en' => 'Getting enough protein on an Egyptian budget',
                ],
                'excerpt' => [
                    'ar' => 'لست مضطراً لشراء اللحوم المستوردة أو المكملات الغالية لتصل إلى احتياجك اليومي من البروتين.',
                    'en' => 'You do not need imported meat or expensive supplements to hit your daily protein target.',
                ],
                'body' => [
                    'ar' => "أكثر سؤال يتكرر في العيادة: «البروتين غالي، أعمل إيه؟» والإجابة أن أرخص مصادر البروتين في السوق المصري هي في الغالب أفضلها من الناحية الغذائية.\n\nالبيض والعدس والفول والجبن القريش والفراخ البلدي كلها مصادر ممتازة وسعرها في متناول اليد. الفول والعدس بالذات يوفران بروتيناً وأليافاً في نفس الوقت، وهي تركيبة تشعرك بالشبع لساعات أطول.\n\nالمكملات ليست ضرورة إلا في حالات محددة يحددها التقييم، مثل صعوبة الوصول للاحتياج بسبب حالة صحية أو جدول عمل قاسٍ.",
                    'en' => "The most repeated question in clinic is: “protein is expensive, what do I do?” The answer is that the cheapest protein sources in the Egyptian market are usually the best ones nutritionally too.\n\nEggs, lentils, foul, karish cheese and local chicken are all excellent and affordable. Foul and lentils in particular deliver protein and fibre together, a combination that keeps you full for far longer.\n\nSupplements are not a necessity except in specific cases identified during assessment — a medical condition or a punishing work schedule that makes hitting the target genuinely difficult.",
                ],
                'category' => ['ar' => 'تغذية', 'en' => 'Nutrition'],
                'reading_minutes' => 5,
                'published_at' => Carbon::now()->subDays(21),
                'is_featured' => false,
            ],
            [
                'slug' => 'reading-your-lab-results',
                'cover_path' => 'food-clinical-flatlay',
                'title' => [
                    'ar' => 'ماذا تعني نتائج تحاليلك من الناحية الغذائية',
                    'en' => 'What your lab results mean nutritionally',
                ],
                'excerpt' => [
                    'ar' => 'الرقم داخل النطاق الطبيعي لا يعني دائماً أن كل شيء على ما يرام. إليك ما ننظر إليه فعلاً.',
                    'en' => 'A number inside the normal range does not always mean everything is fine. Here is what we actually look at.',
                ],
                'body' => [
                    'ar' => "تحليل واحد لا يحكي القصة كاملة. ما ننظر إليه في العيادة هو الاتجاه عبر الوقت والعلاقة بين القيم المختلفة، لا الرقم المفرد داخل نطاقه.\n\nمثلاً، فيتامين د عند الحد الأدنى للنطاق الطبيعي مع شكوى من الإرهاق المستمر يستحق التدخل، حتى لو كان التقرير يقول «طبيعي». والعكس صحيح: قيمة خارج النطاق بقليل قد لا تعني شيئاً إن كانت مستقرة عبر ثلاث تحاليل متتالية.\n\nهذا المقال لا يغني عن استشارة طبيبك، والتغذية العلاجية تكمّل العلاج الدوائي ولا تحل محله.",
                    'en' => "A single test never tells the whole story. What we look at in clinic is the trend over time and the relationship between different values, not one number inside its range.\n\nVitamin D sitting at the bottom of the normal range alongside persistent fatigue is worth acting on, even when the report says “normal.” The reverse holds too: a value slightly outside the range may mean nothing at all if it has been stable across three consecutive tests.\n\nThis article does not replace consulting your physician. Therapeutic nutrition complements medical treatment; it does not substitute for it.",
                ],
                'category' => ['ar' => 'صحة عامة', 'en' => 'General health'],
                'reading_minutes' => 6,
                'published_at' => Carbon::now()->subDays(45),
                'is_featured' => false,
            ],
        ];

        foreach ($posts as $post) {
            Post::updateOrCreate(['slug' => $post['slug']], $post);
        }
    }
}
