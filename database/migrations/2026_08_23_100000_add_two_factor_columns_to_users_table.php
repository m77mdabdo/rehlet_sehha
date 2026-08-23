<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            /*
             * The TOTP shared secret, and the one-time recovery codes.
             *
             * Both are ENCRYPTED at the application layer (see the casts on
             * App\Models\User). The secret is the second factor: anyone holding
             * it can generate valid codes forever, so a database dump that
             * exposed it would silently defeat 2FA for every account in it —
             * the accounts would keep working and nobody would know.
             *
             * TEXT rather than VARCHAR because Laravel's encryption envelope
             * adds roughly 190 bytes of base64 overhead, and the recovery code
             * array is a JSON list of eight codes before it is encrypted.
             *
             * Nullable because 2FA is required only for administrators and
             * merely offered to everyone else; a doctor who has not enrolled
             * has neither value set.
             */
            $table->text('app_authentication_secret')->nullable()->after('password');
            $table->text('app_authentication_recovery_codes')->nullable()->after('app_authentication_secret');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['app_authentication_secret', 'app_authentication_recovery_codes']);
        });
    }
};
