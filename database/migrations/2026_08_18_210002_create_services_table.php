<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            // Translatable JSON: {"ar": "...", "en": "..."} via spatie/laravel-translatable.
            // Two locales only, so JSON columns beat a *_translations table — no join
            // on any page render, and the whole record loads in one query.
            $table->json('name');
            $table->json('subtitle')->nullable();
            $table->json('description')->nullable();
            $table->json('features')->nullable();

            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('EGP');
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedTinyInteger('sessions_count')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
