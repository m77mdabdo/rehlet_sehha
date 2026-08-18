<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\IntakeForm;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<IntakeForm>
 */
class IntakeFormFactory extends Factory
{
    protected $model = IntakeForm::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'appointment_id' => Appointment::factory(),
            'goal' => fake()->randomElement([
                'إنقاص الوزن',
                'زيادة الوزن',
                'تحسين مستوى الطاقة',
                'ضبط سكر الدم',
                'تغذية رياضية',
            ]),
            'medications' => fake()->randomElement([
                'ميتفورمين ٥٠٠ مجم مرتين يومياً',
                'لا يوجد',
                'فيتامين د ٥٠٠٠ وحدة أسبوعياً',
            ]),
            'conditions' => fake()->randomElement([
                'تكيس المبايض',
                'ارتفاع ضغط الدم',
                'قصور الغدة الدرقية',
                'لا يوجد',
            ]),
            'avoid_foods' => fake()->randomElement([
                'حساسية من المكسرات',
                'لا أتناول اللاكتوز',
                'نباتي',
                'لا يوجد',
            ]),
            'note' => fake()->randomElement([
                'أعمل بنظام ورديات وأحتاج خطة تناسب مواعيد غير منتظمة.',
                'أمارس المشي ثلاث مرات أسبوعياً.',
                null,
            ]),
            'consent_at' => Carbon::now()->subDays(fake()->numberBetween(0, 30)),
            'consent_ip' => fake()->ipv4(),
        ];
    }

    public function withoutConsent(): self
    {
        return $this->state(fn (): array => [
            'consent_at' => null,
            'consent_ip' => null,
        ]);
    }
}
