<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Bookings nobody has acted on, at the top of the dashboard, any date.
 *
 * WHY THIS EXISTS, written plainly because it is a failure and not a feature
 * request: two real patients booked and nobody at the clinic ever saw either
 * one. A booking on 26 September for the 27th, and one on 11 October for the
 * 12th. Both got their confirmation email. Both stayed `pending` — the status
 * that means a human has not yet looked — and the 27th simply passed.
 *
 * Three things were each independently enough to hide them, and all three
 * were date-anchored:
 *
 *   1. The appointments list defaulted to today only, so the screen opened
 *      empty on every morning that was not the booking's own day.
 *   2. The sidebar badge counted today, so it was blank on those mornings too.
 *   3. The clinic's new-booking email sat in a queue that cron never drained.
 *
 * Fixing any one of them would still have left the other two. So this widget
 * has NO DATE FILTER, on purpose, and it is the first thing on the dashboard:
 * the one view whose answer cannot go quiet because time passed. An overdue
 * booking is not less urgent than tomorrow's — it is worse, and every
 * date-windowed view stops showing it precisely when that becomes true.
 *
 * IT HIDES ITSELF WHEN THERE IS NOTHING. A panel that always carries an empty
 * "needs attention" block teaches people to stop reading it, and then it is
 * worth nothing on the day it is not empty.
 */
class AwaitingConfirmation extends TableWidget
{
    /** Above TodaySchedule (2) and the overview. Nothing outranks this. */
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    /**
     * Shown only when something is actually waiting.
     */
    public static function canView(): bool
    {
        return Appointment::query()->awaitingConfirmation()->exists();
    }

    public function table(Table $table): Table
    {
        $zone = config('clinic.timezone');

        return $table
            ->heading('حجوزات مستنية تأكيد')
            ->description('الحجوزات دي محدش أكّدها لسه — أي تاريخ، قديم أو جاي.')
            ->query(
                Appointment::query()
                    ->with(['patient', 'service'])
                    ->awaitingConfirmation()
            )
            ->columns([
                /*
                 * The date, and how late it is, in the same cell. "2 سبتمبر"
                 * on its own does not read as a problem; "فات من 15 يوم"
                 * beside it does, and that is the whole point of the widget.
                 */
                TextColumn::make('starts_at')
                    ->label('الميعاد')
                    ->dateTime('j M — H:i', timezone: $zone)
                    // Carbon renders this in Arabic from the app locale, so
                    // "منذ ١٥ يوم" / "بعد يوم" come out without help here.
                    ->description(fn (Appointment $record): string => $record->starts_at->diffForHumans())
                    ->color(fn (Appointment $record): string => $record->starts_at->isPast() ? 'danger' : 'warning'),

                TextColumn::make('patient.name')
                    ->label('المريضة')
                    // The phone, because an overdue unconfirmed booking is a
                    // call, not an email.
                    ->description(fn (Appointment $record): string => $record->patient->phone),

                TextColumn::make('service.name')->label('الباقة'),

                TextColumn::make('reference')
                    ->label('رقم الحجز')
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('created_at')
                    ->label('اتحجز')
                    ->since(timezone: $zone),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('افتحي')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Appointment $record): string => AppointmentResource::getUrl('edit', ['record' => $record])),
            ])
            ->defaultSort('starts_at')
            ->paginated(false)
            ->emptyStateHeading('مفيش حجوزات مستنية تأكيد');
    }
}
