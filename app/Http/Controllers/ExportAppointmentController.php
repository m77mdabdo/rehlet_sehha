<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View;

class ExportAppointmentController extends Controller
{
    /**
     * The patient's own record, as a file they can keep.
     *
     * THIS IS THE ACCESS RIGHT UNDER EGYPTIAN LAW 151/2020, IN SUBSTANCE.
     * Not a convenience feature. Showing someone their data on a screen they
     * can only reach while we keep the page online is not access — a patient
     * changing clinics, or querying a bill, or simply wanting a copy of what
     * she told a doctor, needs something she can take with her. Do not
     * "simplify" this away.
     *
     * FORMAT: a self-contained HTML file, not a PDF.
     *
     * No PDF library is installed and one was not added. The two candidates
     * both cost more than they return here:
     *
     *   dompdf   cannot shape Arabic. It renders the letters in isolated
     *            forms, unjoined and left-to-right — a document that is
     *            technically a PDF and functionally unreadable to the person
     *            it belongs to.
     *   mPDF     handles Arabic properly but is a large dependency carrying
     *            its own font bundle, for one download on one page.
     *
     * The HTML file is rendered by the patient's own browser, which already
     * does Arabic shaping and bidi correctly, already has the fonts, and
     * prints to PDF from the share sheet on every phone. It carries its own
     * styles inline so it survives being emailed, moved to a USB stick, or
     * opened years from now with no network.
     */
    public function __invoke(string $token): Response
    {
        $appointment = Appointment::query()
            ->with(['service', 'patient', 'staff', 'intakeForm'])
            ->where('cancel_token', $token)
            ->first();

        // Same as the manage page: an invalid token is indistinguishable from
        // a URL that never existed.
        abort_if($appointment === null, 404);

        /*
         * Record THAT an export happened, never what it contained.
         *
         * Same rule as everything else touching this record: the fact is
         * auditable — it answers "was a copy of this taken, and when" — and
         * the content is not. Logged against the appointment rather than the
         * intake form, because an export covers the whole record and happens
         * even when there is no intake left to describe.
         */
        activity('appointment')
            ->performedOn($appointment)
            ->withProperties(['exported' => true])
            ->event('exported')
            ->log('exported');

        /*
         * Nothing may attach itself to this response.
         *
         * The debug bar injects its own markup and scripts into every HTML
         * response it sees, and this response is not a page — it is a file the
         * patient keeps. Injected into it, the debug payload would break the
         * self-contained promise (it links to assets on a host that will not
         * exist wherever this file ends up) and would carry the request's SQL
         * into a document containing a medical record.
         *
         * Guarded by the container binding rather than the facade: the package
         * is a dev dependency and is simply absent in production, where this
         * is a no-op. The test suite cannot catch this on its own — the debug
         * bar is disabled under `testing`, so the "no <script> tags" assertion
         * passes there while a real local download carries the payload.
         */
        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }

        $html = View::make('exports.appointment-record', [
            'appointment' => $appointment,
            'intake' => $appointment->intakeForm,
        ])->render();

        $filename = sprintf('rehlet-sehha-%s.html', $appointment->reference);

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            // attachment, so it is saved rather than browsed. A patient who
            // wanted to read it on screen already has the page it came from.
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            /*
             * no-store, no-referrer and noindex come from the `token-url`
             * middleware on this route — the same protections as the manage
             * page, set in one place so the two cannot drift apart. They
             * matter MORE here, not less: this response body IS the medical
             * record, so an intermediary caching it would be caching the
             * document itself rather than a page that renders it.
             *
             * nosniff is local because it is specific to serving a file: it
             * stops a browser deciding this attachment is something other than
             * what we said it is.
             */
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
