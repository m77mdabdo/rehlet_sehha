<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            // Translatable JSON: {"ar": "...", "en": "..."}.
            $table->json('title');
            $table->json('excerpt')->nullable();
            $table->json('body');

            $table->string('cover_path')->nullable();
            $table->string('category')->nullable();
            $table->unsignedTinyInteger('reading_minutes')->nullable();

            // NULL = draft. A future value = scheduled.
            $table->dateTime('published_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
