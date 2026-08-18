<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('working_hours', function (Blueprint $table) {
            $table->id();

            /*
             * Every schedule belongs to a specific practitioner. A nullable
             * "clinic-wide default" row was ambiguous the moment a second
             * practitioner existed: it could not say whether the second doctor
             * inherited those hours, overrode them, or was closed. Requiring an
             * owner makes the calendar answerable from the row alone.
             */
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();

            // 0 = Sunday ... 6 = Saturday (Carbon's dayOfWeek). Friday is 5 and
            // simply has no row: absence of a row means closed.
            $table->tinyInteger('day_of_week');

            // Cairo wall-clock times, not UTC — "we open at 10:00" is a local
            // statement that stays true across DST. They are resolved against
            // config('clinic.timezone') when generating concrete UTC slots.
            $table->time('start_time');
            $table->time('end_time');

            $table->unsignedSmallInteger('slot_minutes')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // One schedule block per practitioner per day per opening time.
            // Every column here is NOT NULL, so unlike a nullable composite
            // this index genuinely prevents duplicates.
            $table->unique(['staff_id', 'day_of_week', 'start_time']);

            $table->index(['staff_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
