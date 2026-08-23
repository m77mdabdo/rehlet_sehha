<?php

declare(strict_types=1);

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Support\Bilingual;
use App\Models\WorkingHour;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('المحتوى')
                ->schema([
                    Bilingual::text('name', 'الاسم'),
                    Bilingual::text('subtitle', 'العنوان الفرعي', required: false),
                    Bilingual::textarea('description', 'الوصف'),
                ]),

            Section::make('الرابط')
                ->schema([
                    /*
                     * The slug is derived from the ARABIC name, because that is
                     * what the clinic writes first, and then left editable.
                     * Str::slug transliterates Arabic to Latin, so
                     * "استشارة تغذية فردية" becomes a usable path rather than
                     * percent-encoded bytes.
                     *
                     * Only auto-filled while creating: regenerating it on edit
                     * would silently change a published URL and break every
                     * link to it, including the ones in patients' inboxes.
                     */
                    TextInput::make('slug')
                        ->label('الرابط (slug)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->extraInputAttributes(['dir' => 'ltr'])
                        ->helperText('بيتولد من الاسم العربي وتقدري تعدليه. تغييره بيكسر أي لينك قديم.'),

                    TextInput::make('name_ar')
                        ->hidden()
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state): void {
                            if ($state && ! $get('slug')) {
                                $set('slug', Str::slug($state));
                            }
                        }),
                ]),

            Section::make('التفاصيل')
                ->schema([
                    TextInput::make('price')
                        ->label('السعر')
                        ->numeric()
                        ->required()
                        ->suffix(__('common.currency')),

                    /*
                     * The slot-grid guard.
                     *
                     * Enforced for real on the model (Service::booted saves
                     * through guardAgainstOutgrowingTheSlotGrid, which throws),
                     * so nothing can write a too-long service by any path —
                     * this panel, a seeder, tinker.
                     *
                     * The rule here exists purely so the clinic sees a sentence
                     * instead of a stack trace. It is NOT the enforcement, and
                     * removing it would not make an over-long service saveable.
                     */
                    TextInput::make('duration_minutes')
                        ->label('المدة بالدقايق')
                        ->numeric()
                        ->required()
                        ->minValue(5)
                        ->rule(function (): \Closure {
                            return function (string $attribute, mixed $value, \Closure $fail): void {
                                $shortest = WorkingHour::query()
                                    ->where('is_active', true)
                                    ->min('slot_minutes');

                                if ($shortest !== null && (int) $value > (int) $shortest) {
                                    $fail(sprintf(
                                        'أقصى مدة مسموح بيها %d دقيقة، عشان دي أقصر خانة في جدول العيادة. '
                                        .'باقة أطول من الخانة ممكن تتداخل مع الحجز اللي بعدها.',
                                        (int) $shortest,
                                    ));
                                }
                            };
                        })
                        ->helperText(fn (): string => sprintf(
                            'لازم تكون أقصر من أو تساوي أقصر خانة في جدول العيادة (%s دقيقة).',
                            WorkingHour::query()->where('is_active', true)->min('slot_minutes') ?? '—',
                        )),

                    TextInput::make('sessions_count')
                        ->label('عدد الجلسات')
                        ->numeric()
                        ->minValue(1)
                        ->default(1),

                    Select::make('specialties')
                        ->label('التخصصات')
                        ->relationship('specialties', 'name')
                        ->multiple()
                        ->preload(),

                    Toggle::make('is_active')
                        ->label('متاحة للحجز')
                        ->default(true)
                        ->helperText('لو اتقفلت، مش هتظهر في الحجز، والحجوزات القديمة بتفضل زي ما هي.'),
                ])
                ->columns(2),
        ]);
    }
}
