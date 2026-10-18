<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Exam bookings now start as "unverified" until the candidate clicks the link emailed to them.
 * Bookings received before this change keep their status and count as confirmed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_bookings', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->timestamp('confirmation_sent_at')->nullable()->after('email_verified_at');
        });

        DB::table('exam_bookings')->whereNull('email_verified_at')->update(['email_verified_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('exam_bookings', fn (Blueprint $table) => $table->dropColumn(['email_verified_at', 'confirmation_sent_at']));
    }
};
