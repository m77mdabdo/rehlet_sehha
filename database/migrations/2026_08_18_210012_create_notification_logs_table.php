<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('channel');

            /*
             * The patient's email address or phone number. Encrypted at the
             * application layer via an 'encrypted' cast, because otherwise this
             * table slowly becomes a plaintext directory of every patient
             * contact detail joined to an appointment id — sitting directly
             * beside the carefully encrypted clinical answers in intake_forms.
             *
             * TEXT, not VARCHAR(255): Laravel's encryption envelope adds around
             * 190 bytes of base64 overhead, so a long (but perfectly legal)
             * email address encrypts to over 300 bytes and would truncate.
             *
             * Encrypting costs nothing here because nothing queries by
             * recipient — lookups go through appointment_id, which is indexed.
             * NotificationLog is also Prunable, so these rows do not accumulate
             * indefinitely; see config('clinic.notification_log_retention_days').
             */
            $table->text('recipient');

            $table->string('template');
            $table->string('status')->default('queued');
            $table->text('error')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();

            $table->index(['appointment_id', 'channel']);

            // Pruning deletes by age, so the cutoff column needs its own index.
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
