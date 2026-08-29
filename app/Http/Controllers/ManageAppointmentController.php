<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Support\PublicContent;
use Illuminate\Contracts\View\View;

class ManageAppointmentController extends Controller
{
    /**
     * The self-service page for one appointment.
     *
     * The controller does nothing but hand the token to the component, which
     * 404s if it matches nothing. Deliberately no lookup here: a controller
     * that resolved the appointment and passed the model would make it very
     * easy for a later refactor to expose the token in a route-model-bound URL.
     */
    public function __invoke(string $token): View
    {
        /*
         * EXPIRY IS DECIDED HERE, and only for a token we recognise.
         *
         * An unknown token still falls through to the component, which 404s —
         * a URL that never existed must stay indistinguishable from a wrong
         * guess, or this page becomes an oracle for probing tokens.
         *
         * A token we DO recognise, past its date, gets an explanation instead.
         * She is a patient holding an email we sent her; "not found" would
         * tell her she had done something wrong.
         */
        $appointment = Appointment::query()->where('cancel_token', $token)->first();

        if ($appointment !== null && $appointment->tokenHasExpired()) {
            return view('pages.token-expired', [
                'reason' => 'tokens.expired.appointment',
                'footerServices' => PublicContent::services(),
            ]);
        }

        return view('pages.manage-appointment', ['token' => $token]);
    }
}
