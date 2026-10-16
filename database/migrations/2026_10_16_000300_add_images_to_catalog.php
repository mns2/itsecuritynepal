<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Optional photos for tracks and courses (course cards fall back to the track photo, then to the code-art block). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', fn (Blueprint $t) => $t->string('image')->nullable()->after('icon'));
        Schema::table('courses', fn (Blueprint $t) => $t->string('image')->nullable()->after('summary'));
    }

    public function down(): void
    {
        Schema::table('categories', fn (Blueprint $t) => $t->dropColumn('image'));
        Schema::table('courses', fn (Blueprint $t) => $t->dropColumn('image'));
    }
};
