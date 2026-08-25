<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Who checked this article, and when.
 *
 * EVERY ARTICLE ON THIS SITE IS PUBLISHED UNDER A LICENSED PRACTITIONER'S
 * NAME. A visitor reading about PCOS on a clinic's website is not reading a
 * blog; she is reading something she has every reason to treat as clinical
 * advice from the person she is about to book with. If it is wrong, it is
 * wrong in her name and against her licence.
 *
 * WHO and WHEN, not a boolean. `is_reviewed = true` records that somebody once
 * clicked something. A name and a timestamp answer the only two questions that
 * matter when an article turns out to be wrong: who signed it off, and was that
 * before or after the paragraph in question was written.
 *
 * The columns are nullable because a draft has not been reviewed yet — that is
 * the normal state of a new article, not an error. What is forbidden is
 * PUBLISHING without them, and that is enforced on the model rather than here,
 * because a NOT NULL column cannot express "only once published_at is set".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->foreignId('reviewed_by')
                ->nullable()
                ->after('published_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn('reviewed_at');
        });
    }
};
