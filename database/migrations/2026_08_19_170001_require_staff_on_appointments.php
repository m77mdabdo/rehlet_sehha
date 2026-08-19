<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * appointments.staff_id becomes NOT NULL.
     *
     * WHY THIS AND NOT A RUNTIME GUARD
     *
     * A NULL staff_id collapses slot_key to "0-<instant>" (see
     * Appointment::syncSlotKey). With one practitioner that is harmless — an
     * unassigned booking really does consume her hour. With two it is a bug in
     * both directions: two unassigned bookings at the same time are refused
     * even though the clinic could take both, and an unassigned booking does
     * not lock the practitioner who ends up doing it.
     *
     * The alternative was to keep the column nullable and throw when a NULL
     * appointment is created while more than one practitioner has active
     * hours. That guard is weaker in three ways:
     *
     *   1. Its correctness depends on data unrelated to the row being saved.
     *      Deactivate one schedule and the guard stops firing, while the
     *      collapsed slot_key stays exactly as wrong.
     *   2. It only prevents NEW rows. Every NULL row already in the table
     *      silently becomes a cross-practitioner lock the day of the hire —
     *      which is the moment nobody is reading migrations.
     *   3. It lives in application code, so a seeder, a bulk import or a
     *      manual SQL fix walks straight past it.
     *
     * NOT NULL is enforced by the database, applies retroactively, and cannot
     * be bypassed. The availability engine already tags every slot with the
     * practitioner it belongs to, so the booking flow has the value at hand —
     * NULL was never carrying information we did not already have. It was an
     * absent value we could always have filled in.
     *
     * If "not yet assigned" ever becomes a real clinical state — a triage
     * queue, a request awaiting the doctor's approval — that is a different
     * shape, not a NULL column. An appointment without a practitioner is not
     * an appointment; it is a request for one, and it should be a row that
     * says so.
     */
    public function up(): void
    {
        $this->backfillMissingStaff();

        Schema::table('appointments', function (Blueprint $table) {
            // MySQL will not alter a column while a foreign key references it.
            $table->dropForeign(['staff_id']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_id')->nullable(false)->change();
        });

        Schema::table('appointments', function (Blueprint $table) {
            /*
             * restrictOnDelete, not the previous nullOnDelete — which is now
             * impossible anyway, and was the wrong behaviour regardless.
             *
             * Deleting a practitioner must not quietly detach her from the
             * clinical record of every patient she treated. The database
             * should refuse; the clinic deactivates a practitioner rather than
             * erasing her.
             */
            $table->foreign('staff_id')->references('id')->on('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_id')->nullable()->change();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Give every existing unassigned appointment a practitioner.
     *
     * Deliberately refuses to guess. With exactly one practitioner the answer
     * is not a guess at all — there is only one person it could have been. With
     * more than one, the migration STOPS rather than distributing patients
     * among doctors at random: an appointment attached to the wrong clinician
     * is a clinical record error, and it would be invisible afterwards because
     * the column would be perfectly populated.
     */
    private function backfillMissingStaff(): void
    {
        $unassigned = DB::table('appointments')->whereNull('staff_id')->count();

        if ($unassigned === 0) {
            return;
        }

        /** @var list<int> $practitioners */
        $practitioners = DB::table('working_hours')
            ->where('is_active', true)
            ->distinct()
            ->pluck('staff_id')
            ->all();

        if (count($practitioners) === 1) {
            DB::table('appointments')
                ->whereNull('staff_id')
                ->update(['staff_id' => $practitioners[0]]);

            return;
        }

        throw new RuntimeException(sprintf(
            'Cannot make appointments.staff_id NOT NULL: %d appointment(s) have no practitioner '
            .'and %d practitioner(s) have active working hours, so there is no single correct '
            .'answer. Assign these appointments by hand before migrating — guessing would write '
            .'the wrong clinician into a patient record and leave no trace that it happened.',
            $unassigned,
            count($practitioners),
        ));
    }
};
