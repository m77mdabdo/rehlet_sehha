<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
        return view('pages.manage-appointment', ['token' => $token]);
    }
}
