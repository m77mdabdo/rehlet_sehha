<?php

declare(strict_types=1);

namespace App\Filament\Resources\Appointments\RelationManagers;

use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Services\Clinical\ClinicalAccessLog;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * The patient's own words about her health.
 *
 * THIS IS THE BLOCK A RECEPTIONIST MUST NOT SEE, and the enforcement is
 * IntakeFormPolicy rather than a hidden field.
 *
 * The distinction matters and is the reason this is a relation manager at all.
 * A Filament field marked ->hidden() is still resolved on the server and still
 * serialised into the Livewire snapshot sent to the browser, where anybody can
 * read it in the network tab. canViewForRecord() below is checked BEFORE the
 * relation manager is mounted: when it returns false the component is never
 * constructed, the query never runs, and there is nothing about the patient's
 * medications anywhere in the response — not in the HTML, not in the JSON.
 *
 * Every read is logged with the reader's identity. See ClinicalAccessLog for
 * why a read log and not just a write log.
 */
class IntakeRelationManager extends RelationManager
{
    protected static string $relationship = 'intakeForm';

    protected static ?string $title = 'المعلومات الطبية';

    protected static ?string $modelLabel = 'المعلومات الطبية';

    /**
     * The gate the PAGE asks: should this tab exist for this user?
     *
     * Necessary but NOT sufficient on its own — see mount(), which asks the
     * same question again for the case where the page is not the one doing the
     * mounting.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('viewAny', IntakeForm::class) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('goal')
                    ->label('الهدف')
                    ->formatStateUsing(fn (?string $state): string => $state
                        ? __('booking.goals.'.$state)
                        : __('booking.rights.blank')),

                TextColumn::make('medications')
                    ->label('أدوية')
                    ->wrap()
                    ->placeholder(__('booking.rights.blank')),

                TextColumn::make('conditions')
                    ->label('أمراض أو حالات')
                    ->wrap()
                    ->placeholder(__('booking.rights.blank')),

                TextColumn::make('avoid_foods')
                    ->label('أكل بتتجنبه')
                    ->wrap()
                    ->placeholder(__('booking.rights.blank')),

                TextColumn::make('note')
                    ->label('ملاحظات')
                    ->wrap()
                    ->placeholder(__('booking.rights.blank')),

                TextColumn::make('consent_at')
                    ->label('الموافقة')
                    ->dateTime('j F Y — H:i', timezone: config('clinic.timezone'))
                    ->placeholder('—'),

                TextColumn::make('erased_at')
                    ->label('اتمسحت في')
                    ->dateTime('j F Y — H:i', timezone: config('clinic.timezone'))
                    ->placeholder('—')
                    ->color('warning'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('تعديل')
                    /*
                     * Correction closes once the consultation has happened —
                     * the same rule the patient is held to. A record read
                     * during a session must not change afterwards, or the
                     * notes and the decision taken from them disagree.
                     */
                    ->visible(fn (IntakeForm $record): bool => $record->isCorrectable()
                        && auth()->user()->can('update', $record)),
            ])
            ->emptyStateHeading('مفيش معلومات طبية مع الحجز ده')
            ->emptyStateDescription('المريضة بتكتب المعلومات دي بنفسها وقت الحجز.');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('goal')
                ->label('الهدف')
                ->options(fn (): array => __('booking.goals'))
                ->native(false),
            Textarea::make('medications')->label('أدوية')->rows(3),
            Textarea::make('conditions')->label('أمراض أو حالات')->rows(3),
            Textarea::make('avoid_foods')->label('أكل بتتجنبه')->rows(3),
            Textarea::make('note')->label('ملاحظات')->rows(3),
        ]);
    }

    /**
     * Authorise the mount itself, then log the read.
     *
     * THE AUTHORISE CALL IS NOT REDUNDANT with canViewForRecord above, and
     * finding that out is the reason it is here.
     *
     * canViewForRecord is consulted by the Filament PAGE when it decides which
     * relation managers to render. It is not consulted when this component is
     * mounted by anything else. Mounted directly — by name, through Livewire,
     * or by any future page that renders it without going through the resource
     * — the component came up perfectly happily for a receptionist and its
     * HTML contained the patient's medications.
     *
     * That was never reachable over HTTP by an unprivileged user, because
     * Livewire will not update a component whose signed snapshot the server
     * never issued. But "the transport happens to prevent it" is not the same
     * as "the component refuses", and only the second survives somebody
     * mounting this from a new screen next year.
     *
     * So the class that holds the clinical data enforces the policy itself.
     *
     * The read log sits after the authorisation, so a refused mount produces
     * no entry — nothing was read.
     */
    public function mount(): void
    {
        abort_unless(
            auth()->user()?->can('viewAny', IntakeForm::class) ?? false,
            403,
        );

        parent::mount();

        /** @var Appointment $appointment */
        $appointment = $this->getOwnerRecord();

        $intake = $appointment->intakeForm;

        if ($intake !== null) {
            ClinicalAccessLog::read($intake, 'appointment.intake');
        }
    }
}
