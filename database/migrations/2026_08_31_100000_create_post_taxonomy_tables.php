<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Categories and tags for the blog.
 *
 * TWO TAXONOMIES, AND ONLY TWO. A category answers "what area of practice is
 * this about" and every article has exactly one; a tag answers "what else is
 * this about" and an article has any number. That is the smallest pair that
 * supports a useful index without inventing structure nobody will maintain.
 *
 * WHY CATEGORIES ARE NOT THE `specialties` TABLE, which lists the same clinical
 * areas. A specialty is a service the clinic sells: it carries a price-adjacent
 * description, an order in the services list, and copy written to persuade. A
 * category is an editorial grouping: it carries a sentence explaining what a
 * reader will find and nothing else. Pointing articles at specialties would put
 * marketing copy at the top of a reading index, and would mean withdrawing a
 * service silently orphaned its articles.
 *
 * `posts.category` was a translatable free-text column — the same words retyped
 * per post, unlinkable and unindexable. Its values are carried across below and
 * the column is dropped, because two sources of truth for one fact is how a
 * category page ends up disagreeing with the article on it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();

            // Latin, stable, and in the URL. Same discipline as posts.slug:
            // an Arabic slug is unreadable once percent-encoded and cannot be
            // typed by anyone reading it aloud over a telephone.
            $table->string('slug')->unique();

            $table->json('name');
            $table->json('description')->nullable();

            // Its own meta description, because a category index is a landing
            // page for a search like "تغذية الحمل" and the first sentence of
            // the description is rarely the right thing to hand a search engine.
            $table->json('meta_description')->nullable();

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->timestamps();
        });

        Schema::create('post_tag', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();

            // One tag once per post. Without this a careless sync writes
            // duplicates and the tag index counts the same article twice.
            $table->unique(['post_id', 'tag_id']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            /*
             * nullOnDelete, not cascade. Deleting a category must not delete
             * the articles filed under it — an uncategorised article is a
             * tidying job, a deleted one is a clinical review thrown away.
             */
            $table->foreignId('category_id')
                ->nullable()
                ->after('slug')
                ->constrained()
                ->nullOnDelete();

            // Shown to the reader as "updated on", and fed to dateModified in
            // the article markup. Distinct from updated_at, which moves when
            // anybody touches the row for any reason including a typo fix.
            $table->timestamp('content_updated_at')->nullable()->after('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn('content_updated_at');
        });

        Schema::dropIfExists('post_tag');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
    }
};
