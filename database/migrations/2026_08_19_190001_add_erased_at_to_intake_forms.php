<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * When the patient erased the clinical content of this intake form.
     *
     * Erasure nulls the five clinical fields and keeps the row. The row has to
     * survive because the APPOINTMENT has to survive — the clinic still ran
     * that hour, still has to account for it, and in many cases still has to
     * bill for it. Deleting the booking to honour an erasure request would
     * destroy the clinic's own records to satisfy a right that only covers the
     * patient's clinical narrative.
     *
     * The timestamp is kept, and deliberately: it is evidence the request was
     * honoured, and the date of an erasure is not itself health data. Without
     * it, an erased intake is indistinguishable from one nobody ever filled
     * in, and the clinic cannot show it complied.
     */
    public function up(): void
    {
        Schema::table('intake_forms', function (Blueprint $table) {
            $table->dateTime('erased_at')->nullable()->after('consent_ip');
        });
    }

    public function down(): void
    {
        Schema::table('intake_forms', function (Blueprint $table) {
            $table->dropColumn('erased_at');
        });
    }
};
