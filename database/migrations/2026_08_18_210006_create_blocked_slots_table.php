<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_slots', function (Blueprint $table) {
            $table->id();
            // NULL staff_id = the whole clinic is closed for this window
            // (public holiday, maintenance), not just one staff member.
            $table->foreignId('staff_id')->nullable()->constrained('users')->cascadeOnDelete();

            // UTC, like every other stored instant.
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_slots');
    }
};
