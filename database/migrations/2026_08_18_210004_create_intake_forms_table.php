<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intake_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->unique()->constrained()->cascadeOnDelete();

            $table->string('goal')->nullable();

            /*
             * Clinical fields. These are health data about an identifiable
             * person, so they are encrypted at the application layer via
             * 'encrypted' casts on the model — the database only ever sees
             * ciphertext, which keeps them out of query logs, replicas and
             * mysqldump output in readable form.
             *
             * TEXT rather than VARCHAR because Laravel's encrypter emits a
             * base64 JSON envelope (iv + value + mac); even a short answer
             * expands past a few hundred bytes, and a long medication list
             * would silently truncate in a VARCHAR(255).
             *
             * Trade-off, deliberately accepted: encrypted columns cannot be
             * searched, sorted or indexed by the database.
             */
            $table->text('medications')->nullable();
            $table->text('conditions')->nullable();
            $table->text('avoid_foods')->nullable();
            $table->text('note')->nullable();

            $table->dateTime('consent_at')->nullable();
            $table->string('consent_ip', 45)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intake_forms');
    }
};
