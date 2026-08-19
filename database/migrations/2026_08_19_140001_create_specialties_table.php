<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Specialties are CLINICAL AREAS. They are not services.
     *
     * A service is a thing you can buy and book: it has a price, a duration, a
     * session count, and it becomes a row in `appointments`. A specialty is a
     * field of practice the clinic works in — "PCOS", "sports nutrition",
     * "paediatric nutrition". You cannot book a specialty; you book a
     * consultation and the specialty is what it is about.
     *
     * They are deliberately kept apart rather than modelled as one table with
     * a nullable price, because merging them would put a bookable/unbookable
     * flag on every row and make `services` a table where half the columns are
     * meaningless for half the rows. It would also mean the booking flow has
     * to filter a table whose whole purpose is to be bookable — and the day
     * someone forgets that filter, a patient books "corporate wellness" for
     * 0 EGP.
     *
     * They may well relate later (a service could list the specialties it
     * covers), and that is a pivot table when it is needed, not a merge.
     */
    public function up(): void
    {
        Schema::create('specialties', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            // Same bilingual JSON pattern as services/posts/faqs: two locales,
            // so JSON columns beat a translations table — no join on render.
            $table->json('name');
            $table->json('description')->nullable();

            /*
             * An icon KEY, not an emoji and not a file path.
             *
             * The key resolves to an inline SVG in the x-icon component, drawn
             * in currentColor. That keeps the icon monochrome and on-brand,
             * lets it invert on the navy band for free, and means it renders
             * identically everywhere.
             *
             * Emoji were the alternative and are worse here: they are full
             * colour and clash with a two-colour palette, they render as a
             * different picture on every platform, several carry skin-tone and
             * gender variants nobody chose, and a screen reader announces them
             * by a name the clinic did not write. Short enough for the longest
             * key with room to spare.
             */
            $table->string('icon', 40);

            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specialties');
    }
};
