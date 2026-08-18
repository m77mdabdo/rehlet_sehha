<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            // Unique was not specified, but the same YouTube video appearing
            // twice in the library is always a data-entry mistake rather than
            // an intent, so the database rejects it.
            $table->string('youtube_id', 20)->unique();

            $table->json('title');
            $table->json('description')->nullable();

            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
