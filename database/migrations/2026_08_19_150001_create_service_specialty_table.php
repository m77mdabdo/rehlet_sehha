<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which packages suit which clinical area.
     *
     * This is the relation the specialties table deliberately did NOT collapse
     * into services. A specialty is what a consultation is about; a service is
     * what you buy. The two are many-to-many because a lab review suits every
     * area, and a three-month programme suits chronic cases far more than
     * one-off questions.
     *
     * Its job is a landing page: someone who searched for "تكيس المبايض" lands
     * on the specialty page and needs to be shown the two or three packages
     * that actually fit, not the full price list.
     *
     * cascadeOnDelete both ways — a pivot row describes a pairing, and a
     * pairing whose service has been deleted is not information, it is a
     * dangling reference waiting to render a blank card.
     */
    public function up(): void
    {
        Schema::create('service_specialty', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specialty_id')->constrained()->cascadeOnDelete();

            /*
             * Ordering is per specialty, not global: the package a PCOS
             * patient should see first is not the one a corporate enquiry
             * should. Without this the pivot would fall back to insertion
             * order, which is a seeder artefact rather than a decision.
             */
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            // One pairing per (service, specialty). Re-running the seeder or
            // double-clicking Save in a future admin panel must not create a
            // second row that renders the same package twice.
            $table->unique(['service_id', 'specialty_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_specialty');
    }
};
