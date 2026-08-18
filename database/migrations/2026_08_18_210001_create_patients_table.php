<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            /*
             * The patient's identity in practice. Stored normalised to E.164
             * (+20...) and UNIQUE, so a repeat caller is matched to their
             * existing file rather than silently duplicated.
             *
             * This table is ALSO soft-deleted, and the two interact: a deleted
             * patient keeps its row and therefore keeps its phone number
             * reserved. That is deliberate. A clinic must never hard-delete a
             * medical record, and a returning patient should get their history
             * back rather than a fresh empty file. Patient::findOrCreateByPhone()
             * is the supported way in — it restores the soft-deleted record.
             *
             * DO NOT "fix" this into a composite unique on (phone, deleted_at).
             * In MySQL a unique index treats every NULL as distinct, and
             * deleted_at is NULL for every live patient — so that index would
             * permit unlimited ACTIVE patients sharing one phone number, which
             * is precisely the duplication this constraint exists to prevent.
             * The composite index looks like the tighter constraint and is in
             * fact the weaker one.
             */
            $table->string('phone', 20)->unique();

            $table->string('email')->nullable();
            $table->date('birth_date')->nullable();

            // Backed by App\Enums\Gender. Clinically relevant to nutrition
            // planning (energy requirements, lab reference ranges), so it is
            // constrained rather than free text — see the enum's docblock.
            $table->string('gender')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
