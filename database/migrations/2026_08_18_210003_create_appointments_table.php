<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->string('cancel_token', 64)->unique();

            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();

            // Always UTC. See config/app.php for why local time is never stored.
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');

            $table->string('mode');
            $table->string('status')->default('pending');

            // Price is snapshotted onto the appointment rather than read through
            // the service, so that later price changes never rewrite history.
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('EGP');

            $table->string('source')->default('website');
            $table->text('staff_notes')->nullable();

            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            /*
             * Double-booking guard.
             *
             * MySQL has no partial (filtered) unique indexes, so we cannot say
             * "unique on (staff_id, starts_at) WHERE status <> 'cancelled'".
             * What MySQL does give us is that a UNIQUE index permits an
             * unlimited number of NULLs. So we encode the occupancy of a slot
             * into a single nullable column:
             *
             *   holding the slot  ->  slot_key = "{staff_id}-{starts_at}"
             *   released          ->  slot_key = NULL
             *
             * A second attempt to book a slot that is already held violates the
             * unique index and the INSERT fails at the database, not in PHP.
             * That matters because the check-then-insert pattern in application
             * code has a race window: two concurrent requests can both read
             * "slot is free" before either writes. Here the database is the
             * single arbiter, so the loser of the race gets a QueryException.
             *
             * The key is maintained by Appointment::syncSlotKey(), wired up in
             * the model's booted() hook — including on soft delete, which must
             * release the slot too or a deleted appointment would block its
             * hour forever.
             */
            $table->string('slot_key', 64)->nullable()->unique();

            $table->timestamps();
            $table->softDeletes();

            $table->index('starts_at');
            $table->index('status');
            $table->index('patient_id');
            $table->index(['starts_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
