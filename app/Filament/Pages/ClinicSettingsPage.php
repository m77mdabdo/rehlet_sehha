<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Support\ClinicSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

/**
 * The handful of numbers and details the clinic changes for itself.
 *
 * Deliberately NOT a resource over the settings table. A generic key/value
 * editor would let anybody type any key with any value into the application's
 * configuration, and would show the clinic a screen of dotted strings. This
 * page offers eight named, validated fields and nothing else — the allow-list
 * is ClinicSettings::EDITABLE, and a key outside it is ignored on write.
 *
 * Saving invalidates both the settings cache and the rendered public content,
 * because the same values feed the booking engine AND the phone number printed
 * on every page. See ClinicSettings::flush() for why clearing one without the
 * other is worse than clearing neither.
 *
 * @property-read Schema $form Resolved at runtime by
 *   Filament's InteractsWithSchemas trait, which builds schemas through
 *   __get rather than declaring a property.
 */
class ClinicSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?int $navigationSort = 31;

    protected string $view = 'filament.pages.clinic-settings';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public static function getNavigationLabel(): string
    {
        return 'إعدادات العيادة';
    }

    public function getTitle(): string|Htmlable
    {
        return 'إعدادات العيادة';
    }

    /**
     * Administrators and the doctor. Not reception: these numbers decide how
     * far ahead the calendar opens and how late a patient may cancel, which is
     * clinic policy rather than day-to-day scheduling.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'doctor']) ?? false;
    }

    public function mount(): void
    {
        $this->authorizeAccess();

        // Seeded from the LIVE config, which already has any stored overrides
        // applied over the defaults — so the form shows what is actually in
        // force, not what the file says.
        /** @var array<string, mixed> $initial */
        $initial = collect(ClinicSettings::EDITABLE)
            ->mapWithKeys(fn (string $key): array => [str_replace('.', '__', $key) => config($key)])
            ->all();

        $this->form->fill($initial);
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('بيانات التواصل')
                    ->description('دي البيانات اللي بتظهر للمرضى على الموقع وفي كل الرسايل.')
                    ->schema([
                        TextInput::make('clinic__contact__email')
                            ->label('إيميل العيادة')
                            ->email()
                            ->required()
                            ->extraInputAttributes(['dir' => 'ltr'])
                            ->helperText('كل رسايل المرضى بترد على الإيميل ده.'),

                        TextInput::make('clinic__contact__phone')
                            ->label('رقم التليفون (بصيغة دولية)')
                            ->required()
                            ->extraInputAttributes(['dir' => 'ltr'])
                            ->helperText('بيبدأ بـ +20. ده اللي بتستخدمه لينكات الاتصال.'),

                        TextInput::make('clinic__contact__phone_display')
                            ->label('الرقم زي ما بيظهر')
                            ->required()
                            ->extraInputAttributes(['dir' => 'ltr'])
                            ->helperText('الشكل اللي المريضة بتقراه، زي 0100 481 8303.'),

                        TextInput::make('clinic__contact__whatsapp')
                            ->label('رقم واتساب (من غير +)')
                            ->required()
                            ->extraInputAttributes(['dir' => 'ltr'])
                            ->helperText('wa.me مابيقبلش علامة +، فالرقم بيتكتب من غيرها.'),
                    ])
                    ->columns(2),

                Section::make('قواعد الحجز')
                    ->description('الأرقام دي بتتحكم في الكالندر اللي المرضى بيشوفوه.')
                    ->schema([
                        TextInput::make('clinic__booking__lead_time_hours')
                            ->label('أقرب ميعاد (بالساعات)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(168)
                            ->helperText('أقل مدة بين دلوقتي وأقرب ميعاد متاح للحجز.'),

                        TextInput::make('clinic__booking__horizon_days')
                            ->label('الكالندر مفتوح لقدام (بالأيام)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(365)
                            ->helperText('كل ما تزود، كل ما يصعب تغيير المواعيد بعدين.'),

                        TextInput::make('clinic__booking__buffer_minutes')
                            ->label('وقت فاصل بين الحجوزات (بالدقايق)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(120),

                        TextInput::make('clinic__booking__reschedule_min_hours')
                            ->label('آخر ميعاد للإلغاء أو التغيير (بالساعات)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(168)
                            ->helperText('ساعة واحدة غالبًا معناها إن الميعاد الملغي مش هيتحجز تاني.'),
                    ])
                    ->columns(2),
            ]);
    }

    public function save(): void
    {
        $this->authorizeAccess();

        $data = $this->form->getState();

        /** @var array<string, mixed> $values */
        $values = collect($data)
            ->mapWithKeys(fn (mixed $value, string $key): array => [
                str_replace('__', '.', $key) => $value,
            ])
            ->all();

        ClinicSettings::put($values);

        Notification::make()
            ->success()
            ->title('الإعدادات اتحفظت')
            ->body('الكاش اتمسح، والتغييرات ظاهرة على الموقع دلوقتي.')
            ->send();
    }
}
