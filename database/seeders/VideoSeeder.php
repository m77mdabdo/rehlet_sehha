<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    public function run(): void
    {
        $videos = [
            [
                'youtube_id' => 'rs01AAAAAAA',
                'title' => [
                    'ar' => 'خمسة أخطاء توقف نزول وزنك',
                    'en' => 'Five mistakes that stall your weight loss',
                ],
                'description' => [
                    'ar' => 'الأخطاء اللي بتتكرر عند معظم الناس وبتخلي الوزن يقف رغم الالتزام بالدايت.',
                    'en' => 'The mistakes most people repeat that keep the scale stuck despite sticking to a diet.',
                ],
                'duration_seconds' => 512,
                'category' => 'تغذية',
                'is_featured' => true,
            ],
            [
                'youtube_id' => 'rs02BBBBBBB',
                'title' => [
                    'ar' => 'إزاي تقرأ الملصق الغذائي صح',
                    'en' => 'How to read a nutrition label properly',
                ],
                'description' => [
                    'ar' => 'شرح عملي لقراءة الملصق الغذائي على المنتجات المتوفرة في السوق المصري.',
                    'en' => 'A practical walkthrough of nutrition labels on products sold in the Egyptian market.',
                ],
                'duration_seconds' => 388,
                'category' => 'تغذية',
                'is_featured' => false,
            ],
            [
                'youtube_id' => 'rs03CCCCCCC',
                'title' => [
                    'ar' => 'فطار صحي في عشر دقائق',
                    'en' => 'A healthy breakfast in ten minutes',
                ],
                'description' => [
                    'ar' => 'ثلاث أفكار فطار سريعة من مكونات موجودة في أي بيت مصري.',
                    'en' => 'Three quick breakfast ideas from ingredients found in any Egyptian kitchen.',
                ],
                'duration_seconds' => 296,
                'category' => 'وصفات',
                'is_featured' => false,
            ],
            [
                'youtube_id' => 'rs04DDDDDDD',
                'title' => [
                    'ar' => 'تغذية مرضى السكري من النوع الثاني',
                    'en' => 'Nutrition for type 2 diabetes',
                ],
                'description' => [
                    'ar' => 'الأساسيات اللي كل مريض سكري لازم يعرفها عن توزيع النشويات على اليوم.',
                    'en' => 'The basics every type 2 patient should know about spreading carbohydrates across the day.',
                ],
                'duration_seconds' => 734,
                'category' => 'صحة عامة',
                'is_featured' => true,
            ],
            [
                'youtube_id' => 'rs05EEEEEEE',
                'title' => [
                    'ar' => 'الأكل في المناسبات من غير ما تخرب نظامك',
                    'en' => 'Eating at social events without derailing your plan',
                ],
                'description' => [
                    'ar' => 'استراتيجية عملية للعزومات والأفراح تحافظ على تقدمك بدل ما توقفه.',
                    'en' => 'A practical strategy for gatherings and weddings that protects your progress instead of halting it.',
                ],
                'duration_seconds' => 421,
                'category' => 'تغذية',
                'is_featured' => false,
            ],
            [
                'youtube_id' => 'rs06FFFFFFF',
                'title' => [
                    'ar' => 'المكملات الغذائية: إمتى تحتاجها فعلاً؟',
                    'en' => 'Supplements: when do you actually need them?',
                ],
                'description' => [
                    'ar' => 'متى يكون المكمل ضرورياً ومتى يكون مجرد مصروف زائد بلا فائدة.',
                    'en' => 'When a supplement is genuinely necessary, and when it is just money with no benefit.',
                ],
                'duration_seconds' => 605,
                'category' => 'أسئلة شائعة',
                'is_featured' => false,
            ],
        ];

        foreach ($videos as $index => $video) {
            Video::updateOrCreate(
                ['youtube_id' => $video['youtube_id']],
                $video + ['is_active' => true, 'sort_order' => $index + 1],
            );
        }
    }
}
