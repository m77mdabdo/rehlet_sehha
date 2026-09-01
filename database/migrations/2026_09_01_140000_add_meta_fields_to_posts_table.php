<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A SEARCH SNIPPET IS NOT AN EXCERPT, AND WAS BEING USED AS ONE.
 *
 * The article page passed `excerpt` straight into the meta description. That
 * works and is a reasonable default — but the two are written for different
 * readers and are cut at different lengths. An excerpt sits under a headline
 * on the index and can run as long as it reads well; a meta description is
 * truncated by search engines at roughly 155 characters, mid-word.
 *
 * The site's own SEO pass found five descriptions over that limit, up to 209
 * characters, and there was nowhere to fix them without rewriting the excerpt
 * that appears on the page.
 *
 * NULLABLE, AND THE FALLBACK STAYS. An article that says nothing here keeps
 * behaving exactly as before — title for the title, excerpt for the
 * description. This is an override for the pieces that need one, not a second
 * mandatory field on every article.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            // Translatable JSON, like every other copy field on this table.
            $table->json('meta_title')->nullable()->after('excerpt');
            $table->json('meta_description')->nullable()->after('meta_title');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn(['meta_title', 'meta_description']);
        });
    }
};
