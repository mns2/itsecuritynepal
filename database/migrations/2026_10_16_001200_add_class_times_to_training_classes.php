<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Class days and times, so instructors see "today's classes" and attendance is taken on class days. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_classes', function (Blueprint $table) {
            $table->json('days')->nullable()->after('schedule');        // ["sun","mon",…]
            $table->time('start_time')->nullable()->after('days');
            $table->time('end_time')->nullable()->after('start_time');
        });
    }

    public function down(): void
    {
        Schema::table('training_classes', fn (Blueprint $t) => $t->dropColumn(['days', 'start_time', 'end_time']));
    }
};
