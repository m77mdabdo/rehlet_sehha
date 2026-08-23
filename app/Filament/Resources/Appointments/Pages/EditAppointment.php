<?php

declare(strict_types=1);

namespace App\Filament\Resources\Appointments\Pages;

use App\Filament\Actions\AppointmentActions;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    public function getTitle(): string
    {
        /** @var Appointment $record */
        $record = $this->getRecord();

        return 'حجز '.$record->reference;
    }

    /**
     * The same actions the list row offers, so the two screens cannot drift
     * apart — and each one still asks the policy for itself.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            AppointmentActions::confirm(),
            AppointmentActions::reschedule(),
            AppointmentActions::complete(),
            AppointmentActions::markNoShow(),
            AppointmentActions::cancel(),
        ];
    }
}
