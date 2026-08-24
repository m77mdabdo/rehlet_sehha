<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plate_foods', function (Blueprint $table) {
            $table->id();

            // Bilingual, like every other piece of content on this site.
            $table->json('name');

            /*
             * The food group, and the ONLY axis this feature reasons about.
             *
             * There is deliberately no calories column, no grams, no macro
             * split and no portion size — see App\Models\PlateFood for why a
             * nutrition clinic's homepage must not ship a calorie counter in
             * disguise. Adding a number here later would not be a new column,
             * it would be a different feature.
             */
            $table->string('group', 20);

            // An emoji, not an image: it renders instantly, needs no asset
            // pipeline, and is legible at any size on any device.
            $table->string('emoji', 16);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plate_foods');
    }
};
