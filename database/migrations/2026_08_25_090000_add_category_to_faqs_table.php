<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FAQs get a category.
 *
 * Two things need this. The packages page answers questions about BUYING —
 * can I switch, do I pay upfront, what happens if I cancel — which are not the
 * questions the homepage's general FAQ answers, and must not be mixed in with
 * them. And the standalone FAQ page, when it is built, groups everything by
 * category rather than showing an undifferentiated list.
 *
 * Doing it here rather than in the FAQ page's own task avoids writing the
 * buying questions into translation files first and migrating them into the
 * table afterwards. They are clinic-editable content, like every other FAQ, and
 * they belong in the same place the clinic already edits FAQs.
 *
 * EXISTING ROWS BECOME 'general', AND THE HOMEPAGE IS SCOPED TO THAT. The
 * homepage renders every active FAQ, so without the scope, adding buying
 * questions here would silently lengthen a homepage section that is supposed to
 * be unchanged by this work.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table): void {
            /*
             * A string rather than an enum or a lookup table. The set is small,
             * it is a presentation grouping rather than a domain concept, and
             * an enum column would need a migration every time the clinic wants
             * a new heading on its FAQ page.
             *
             * Indexed because both pages that use it filter on it.
             */
            $table->string('category', 40)->default('general')->index()->after('answer');
        });

        // Everything that predates this migration is a general question.
        DB::table('faqs')->whereNull('category')->update(['category' => 'general']);
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table): void {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
        });
    }
};
