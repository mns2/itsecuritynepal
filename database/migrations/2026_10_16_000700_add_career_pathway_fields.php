<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** Learning paths become public career-pathway pages: /career-pathways/{slug}/ */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_paths', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('key');
            $table->string('image')->nullable()->after('icon');
            $table->text('intro')->nullable()->after('goal');           // who it is for / why
            $table->text('role_text')->nullable()->after('intro');      // what the job involves
            $table->json('skills')->nullable()->after('role_text');
            $table->json('job_titles')->nullable()->after('skills');
            $table->string('salary_note')->nullable()->after('job_titles'); // only figures the institute can stand behind
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
        });

        foreach (DB::table('learning_paths')->get() as $p) {
            DB::table('learning_paths')->where('id', $p->id)->update(['slug' => Str::slug($p->name)]);
        }
    }

    public function down(): void
    {
        Schema::table('learning_paths', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'image', 'intro', 'role_text', 'skills', 'job_titles', 'salary_note', 'meta_title', 'meta_description']);
        });
    }
};
