<?php

declare(strict_types=1);

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Retire the free-text category column, carrying its values across first.
 *
 * Separate from the migration that creates the tables so the data move is its
 * own reviewable step, and so a failure here leaves the new tables in place
 * rather than rolling back the schema underneath them.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('posts', 'category')) {
            return;
        }

        /*
         * Every distinct Arabic value becomes a category, keyed by the English
         * spelling where one exists. Three seeded posts share two values, so
         * this creates two rows — but it is written to handle whatever is
         * actually in the table rather than what was there when it was written.
         */
        foreach (DB::table('posts')->select('id', 'category')->get() as $post) {
            $decoded = json_decode((string) $post->category, true);

            if (! is_array($decoded) || $decoded === []) {
                continue;
            }

            $english = $decoded['en'] ?? reset($decoded);
            $slug = Str::slug((string) $english) ?: 'general';

            $category = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $decoded, 'is_active' => true],
            );

            DB::table('posts')->where('id', $post->id)->update(['category_id' => $category->id]);
        }

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->json('category')->nullable()->after('excerpt');
        });
    }
};
