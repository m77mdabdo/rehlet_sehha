<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            /*
             * Where the locally stored thumbnail lives on the public disk.
             *
             * Stored rather than hotlinked from img.youtube.com, and the
             * reason is the whole point of the facade pattern it serves: an
             * <img> pointing at Google's CDN makes a request to Google on
             * every homepage visit, carrying the visitor's IP, User-Agent and
             * Referer. That is Google learning who visits a nutrition clinic —
             * which is exactly what not embedding the iframe was meant to
             * prevent. Loading the thumbnail from Google while congratulating
             * ourselves on not loading the player would be theatre.
             *
             * Nullable because a row can exist before its thumbnail has been
             * fetched, and because a fetch can fail; the view falls back to a
             * placeholder rather than a broken image.
             */
            $table->string('thumbnail_path')->nullable()->after('youtube_id');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('thumbnail_path');
        });
    }
};
