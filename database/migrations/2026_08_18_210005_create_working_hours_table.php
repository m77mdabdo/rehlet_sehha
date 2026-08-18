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
            // NULL staff_id = clinic-wide default, applying to any staff member
            // who has no row of their own for that day.
            $table->foreignId('staff_id')->nullable()->constrained('users')->cascadeOnDelete();

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

            $table->index(['staff_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('working_hours');
    }
};
