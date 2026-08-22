<?php

use App\Support\Locales;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            /*
             * The language this booking was made in.
             *
             * Every notification is sent in it. Without this column the only
             * available answer at send time is the application default, which
             * would mail an English-speaking patient a reminder in Arabic —
             * and a reminder someone cannot read is worse than none, because
             * it looks like the clinic tried.
             *
             * It cannot be inferred later either: the request that sends a
             * reminder is a cron run with no locale of its own, and the
             * patient record has no language on it. This is the only moment
             * the answer is known, so it is recorded here.
             *
             * Short and NOT NULL with a default, because a booking always
             * happened in some language and there is no meaningful "unknown".
             */
            $table->string('locale', 5)
                ->default(Locales::DEFAULT)
                ->after('source');

            /*
             * When each reminder was sent — and, more importantly, the claim
             * that stops it being sent twice.
             *
             * The scheduler runs every minute from a shared-hosting cron that
             * nobody supervises. Two runs can overlap when one is slow, and a
             * reminder query that simply selects "appointments starting in an
             * hour" would hand the same rows to both. The sending command
             * therefore claims a row with a conditional UPDATE — set the stamp
             * WHERE it is still NULL — and only notifies if that update
             * affected a row. The database arbitrates, so concurrency is not
             * a matter of timing luck.
             *
             * A dedicated column rather than a lookup in notification_logs:
             * that table is Prunable and drops rows after ninety days, so
             * using it as the deduplication key would make idempotency expire.
             * These stamps live exactly as long as the appointment does.
             */
            $table->dateTime('reminder_24h_sent_at')->nullable()->after('cancellation_reason');
            $table->dateTime('reminder_1h_sent_at')->nullable()->after('reminder_24h_sent_at');
        });

        /*
         * The reminder sweep asks: which live appointments start soon and have
         * not been reminded yet? Indexed on the two columns that narrow it, so
         * a minutely cron does not table-scan every appointment ever made.
         */
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['starts_at', 'reminder_24h_sent_at'], 'appointments_reminder_24h_index');
            $table->index(['starts_at', 'reminder_1h_sent_at'], 'appointments_reminder_1h_index');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_reminder_24h_index');
            $table->dropIndex('appointments_reminder_1h_index');
            $table->dropColumn(['locale', 'reminder_24h_sent_at', 'reminder_1h_sent_at']);
        });
    }
};
