<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Patient reviews.
 *
 * REPLACES THREE INVENTED TESTIMONIALS. Nothing on this site claims a patient
 * said something a patient did not say.
 *
 * Two gates stand between a submission and the homepage, and they are
 * different gates on purpose:
 *
 *   consented_at — the PATIENT agreed to publication. Without it nothing may
 *   ever be shown, and no amount of clinic-side approval substitutes. A review
 *   written in confidence is not ours to publish because we liked it.
 *
 *   approved_at — the CLINIC checked it. Reviews are not auto-published:
 *   somebody may write a diagnosis into a public box, or a name, or another
 *   patient's details, and that has to be caught before it is on the internet
 *   rather than after.
 *
 * Both are nullable timestamps rather than booleans, because "when" is
 * evidence and "true" is not. If a patient later withdraws consent we need to
 * know when it was given.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();

            /*
             * The invitation token. Same discipline as the appointment cancel
             * token: long, random, and a bearer credential — anyone holding it
             * can write a review as that patient, so the page it opens is
             * noindex, no-store and no-referrer.
             */
            $table->string('token', 64)->unique();

            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('comment')->nullable();

            /*
             * What is shown publicly. Defaulted to a first name plus an
             * initial and editable by the patient, because a full name beside
             * a description of medical care is more exposure than most people
             * intend when they tick a box.
             */
            $table->string('display_name')->nullable();

            $table->timestamp('invited_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('consented_at')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('moderation_note')->nullable();

            $table->timestamps();

            // The homepage asks one question — approved reviews, newest first.
            $table->index(['approved_at', 'rating']);

            /*
             * One invitation per appointment. Without this a second run of the
             * scheduler, or a status flipped back and forth, invites the same
             * patient twice for the same visit.
             */
            $table->unique('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
