<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * posts.category was a plain string holding Arabic text.
     *
     * That made it the one content column on the site that could not be shown
     * to an English reader: rendering it put "تغذية" on the English page, so
     * the articles section had to omit the category entirely and show reading
     * time instead. A column that cannot be displayed in half the site is not
     * a category, it is a note to the author.
     *
     * This converts it to the same {"ar": "...", "en": "..."} JSON every other
     * translatable column uses. The existing Arabic values are preserved as
     * the `ar` translation and mapped to their English equivalents rather than
     * being duplicated, because a category is a closed vocabulary — three
     * values, all of them known here.
     */
    private const TRANSLATIONS = [
        'تغذية' => 'Nutrition',
        'صحة عامة' => 'General health',
        'وصفات' => 'Recipes',
    ];

    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->json('category_translations')->nullable()->after('category');
        });

        foreach (DB::table('posts')->select('id', 'category')->get() as $post) {
            if ($post->category === null || trim((string) $post->category) === '') {
                continue;
            }

            $arabic = trim((string) $post->category);

            DB::table('posts')->where('id', $post->id)->update([
                'category_translations' => json_encode([
                    'ar' => $arabic,
                    // An unmapped value falls back to the Arabic rather than to
                    // null: a missing English category is a cosmetic gap, but a
                    // silently dropped one loses data the migration cannot undo.
                    'en' => self::TRANSLATIONS[$arabic] ?? $arabic,
                ], JSON_UNESCAPED_UNICODE),
            ]);
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->renameColumn('category_translations', 'category');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('category_string')->nullable()->after('category');
        });

        foreach (DB::table('posts')->select('id', 'category')->get() as $post) {
            if ($post->category === null) {
                continue;
            }

            /** @var array<string, string>|null $decoded */
            $decoded = json_decode((string) $post->category, true);

            DB::table('posts')->where('id', $post->id)->update([
                // Down-migrating loses the English side; Arabic is the primary
                // language, so that is the one that survives.
                'category_string' => is_array($decoded) ? ($decoded['ar'] ?? null) : null,
            ]);
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->renameColumn('category_string', 'category');
        });
    }
};
