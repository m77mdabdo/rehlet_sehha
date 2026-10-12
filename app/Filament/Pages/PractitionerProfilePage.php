<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Support\ClinicSettings;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

/**
 * The practitioner's own details, owned by the clinic rather than by a deploy.
 *
 * Everything here used to live in config/clinic.php, which meant a code change
 * and a deploy to correct a licence year or add a training course. None of it
 * is a developer's to hold.
 *
 * SAME MECHANISM AS ClinicSettingsPage, deliberately: the stored values
 * overlay config through ClinicSettings::apply(), so every existing
 * config('clinic.…') call site keeps working untouched and config/clinic.php
 * becomes the fallback rather than the source. There is no second settings
 * system here.
 *
 * THE ALLOW-LIST IS THE SAFETY. ClinicSettings::EDITABLE names every key this
 * screen may write, and put() ignores anything else. That matters more here
 * than it looks: these values are written into the application's
 * configuration, so without the list a stray key could set app.debug on a
 * production site.
 *
 * WHAT IS DELIBERATELY NOT HERE:
 *
 *   - The philosophy paragraph and the specialty descriptions. They live in
 *     the translation files and stay there — see the note beside the
 *     `philosophy` entry in lang/ar/about.php for the argument, and for the
 *     constraint that applies if anybody ever moves them.
 *   - The portrait and the certificates. Those are an upload pipeline —
 *     storage, validation, image processing, and a certificate flow that has
 *     to warn her to crop her national ID herself rather than doing it for
 *     her. Bolting that onto a settings form would do both badly.
 *   - The clinic's email, phone and WhatsApp number. Those are already
 *     editable on ClinicSettingsPage and they are CLINIC contact details
 *     rather than the practitioner's. Two screens writing the same setting is
 *     how one of them silently loses; this page links there instead.
 *
 * @property-read Schema $form Resolved at runtime by Filament's
 *   InteractsWithSchemas trait, which builds schemas through __get rather than
 *   declaring a property.
 */
class PractitionerProfilePage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.practitioner-profile';

    /**
     * The keys this screen owns, as a subset of the allow-list.
     *
     * Named here rather than "everything in EDITABLE" so that adding a booking
     * rule to the allow-list does not silently put it on this form.
     *
     * @var array<int, string>
     */
    public const FIELDS = [
        'clinic.practitioner.name_ar',
        'clinic.practitioner.display_name_en',
        'clinic.practitioner.title_ar',
        'clinic.practitioner.title_en',
        'clinic.practitioner.degree_ar',
        'clinic.practitioner.licence_body_ar',
        'clinic.practitioner.licence_number',
        'clinic.practitioner.licence_year',
        'clinic.training',
        'clinic.platforms',
        'clinic.contact.area_served',
    ];

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public static function getNavigationLabel(): string
    {
        return 'بيانات الدكتورة';
    }

    public function getTitle(): string|Htmlable
    {
        return 'بيانات الدكتورة';
    }

    /**
     * The doctor and administrators. NOT reception.
     *
     * These are professional credentials — a licence number, a syndicate, a
     * degree. They are claims about a licensed practitioner that appear on a
     * public page and in the structured data search engines read, and the
     * person who answers the phone has no reason to be able to change them.
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'doctor']) ?? false;
    }

    public function mount(): void
    {
        $this->authorizeAccess();

        /*
         * Seeded from the LIVE config, which already has any stored overrides
         * applied over the file's defaults — so the form shows what is
         * actually in force rather than what the file says.
         */
        /** @var array<string, mixed> $initial */
        $initial = collect(self::FIELDS)
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
                Section::make('الاسم واللقب')
                    ->description('ده الاسم اللي بيظهر في صفحة «عن الدكتورة» وتحت كل مقال، وفي البيانات اللي جوجل بيقراها.')
                    ->schema([
                        TextInput::make('clinic__practitioner__name_ar')
                            ->label('الاسم بالعربي')
                            ->required()
                            ->maxLength(80)
                            ->helperText('بالشكل اللي المريضة تقراه، باللقب — زي «أ. رنا محمد أحمد سالم».'),

                        TextInput::make('clinic__practitioner__display_name_en')
                            ->label('الاسم بالإنجليزي')
                            ->required()
                            ->maxLength(80)
                            ->extraInputAttributes(['dir' => 'ltr'])
                            /*
                             * Transliterated, not translated. A person has one
                             * name; this is a second SPELLING of it, so an
                             * English page does not render an Arabic name
                             * mid-sentence.
                             */
                            ->helperText('نفس الاسم مكتوب بحروف إنجليزية، مش ترجمة. ده اللي بيظهر على الصفحات الإنجليزية.'),

                        TextInput::make('clinic__practitioner__title_ar')
                            ->label('المسمى المهني بالعربي')
                            ->required()
                            ->maxLength(80),

                        TextInput::make('clinic__practitioner__title_en')
                            ->label('المسمى المهني بالإنجليزي')
                            ->required()
                            ->maxLength(80)
                            ->extraInputAttributes(['dir' => 'ltr']),
                    ])
                    ->columns(2),

                Section::make('المؤهل والترخيص')
                    ->description('دي بيانات بتظهر للمريضة كإثبات مهني، فأي تغيير فيها لازم يطابق الورق.')
                    ->schema([
                        TextInput::make('clinic__practitioner__degree_ar')
                            ->label('المؤهل')
                            ->required()
                            ->maxLength(160),

                        TextInput::make('clinic__practitioner__licence_body_ar')
                            ->label('جهة الترخيص')
                            ->maxLength(120)
                            ->helperText('النقابة أو الجهة اللي مصدرة الترخيص. سيبيها فاضية لو مفيش.'),

                        TextInput::make('clinic__practitioner__licence_number')
                            ->label('رقم العضوية')
                            ->maxLength(40)
                            ->extraInputAttributes(['dir' => 'ltr'])
                            /*
                             * Required ONLY when a licensing body is named. A
                             * body with no number is a claim with nothing
                             * behind it, which is the one combination that
                             * must not reach a public page.
                             */
                            ->requiredWith('clinic__practitioner__licence_body_ar')
                            ->helperText('لازم يتكتب لو جهة الترخيص مكتوبة.'),

                        TextInput::make('clinic__practitioner__licence_year')
                            ->label('سنة الترخيص')
                            ->numeric()
                            ->minValue(1950)
                            ->maxValue((int) date('Y') + 1)
                            ->extraInputAttributes(['dir' => 'ltr']),
                    ])
                    ->columns(2),

                Section::make('التدريب')
                    ->description('كل سطر بيظهر في صفحة «عن الدكتورة». رتبيهم بالسحب.')
                    ->schema([
                        Repeater::make('clinic__training')
                            // An empty string does not suppress it — Filament
                            // falls back to a humanised field name, which put
                            // "Clinic training" in English above an otherwise
                            // Arabic form.
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('institution_ar')
                                    ->label('الجهة')
                                    ->required()
                                    ->maxLength(160),

                                TextInput::make('programme_ar')
                                    ->label('البرنامج')
                                    ->required()
                                    ->maxLength(160),

                                TextInput::make('hours')
                                    ->label('عدد الساعات')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(5000)
                                    ->extraInputAttributes(['dir' => 'ltr']),

                                TextInput::make('year')
                                    ->label('السنة')
                                    ->numeric()
                                    ->minValue(1950)
                                    ->maxValue((int) date('Y') + 1)
                                    ->extraInputAttributes(['dir' => 'ltr']),
                            ])
                            ->columns(2)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['institution_ar'] ?? null)
                            ->addActionLabel('ضيفي تدريب'),
                    ]),

                Section::make('الاستشارة ونطاق الخدمة')
                    ->schema([
                        CheckboxList::make('clinic__platforms')
                            ->label('المنصات')
                            ->options([
                                'zoom' => 'Zoom',
                                'google_meet' => 'Google Meet',
                                'whatsapp_video' => 'WhatsApp video',
                            ])
                            ->helperText('اللي الجلسة ممكن تتم عليه.'),

                        TextInput::make('clinic__contact__area_served')
                            ->label('نطاق الخدمة')
                            ->required()
                            ->maxLength(8)
                            ->extraInputAttributes(['dir' => 'ltr'])
                            ->helperText('كود الدولة زي EG. بيظهر في البيانات اللي جوجل بيقراها.'),
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

        /*
         * put() applies the allow-list again on write. This screen only offers
         * keys that are on it, so that is belt and braces — which is the point:
         * the guarantee belongs to ClinicSettings, not to whichever form
         * happens to be calling it.
         *
         * flush() clears the settings cache AND the rendered public content,
         * so the about page, the article bylines and the JSON-LD all pick the
         * new values up on the next request rather than a day later.
         */
        ClinicSettings::put($values);

        Notification::make()
            ->success()
            ->title('البيانات اتحفظت')
            ->body('الكاش اتمسح، والتغييرات ظاهرة على الموقع دلوقتي.')
            ->send();
    }
}
