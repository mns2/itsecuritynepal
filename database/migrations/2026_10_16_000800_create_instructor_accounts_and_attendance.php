<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff roles (admin / instructor), classes assigned to instructor accounts, and attendance:
 * one class session per class per date, one attendance mark per student per session.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->after('email');      // admin | instructor (existing accounts stay admin)
            $table->boolean('is_active')->default(true)->after('role');
            $table->foreignId('instructor_id')->nullable()->after('is_active')->constrained()->nullOnDelete(); // public profile
            $table->string('phone', 30)->nullable()->after('instructor_id');
        });

        Schema::table('training_classes', function (Blueprint $table) {
            $table->foreignId('instructor_user_id')->nullable()->after('trainer')->constrained('users')->nullOnDelete();
        });

        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_class_id')->constrained()->cascadeOnDelete();
            $table->date('held_on');
            $table->string('topic')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('taken_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['training_class_id', 'held_on']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('present');   // present | late | absent | excused
            $table->string('note')->nullable();
            $table->timestamps();
            $table->unique(['class_session_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('class_sessions');
        Schema::table('training_classes', fn (Blueprint $t) => $t->dropConstrainedForeignId('instructor_user_id'));
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('instructor_id');
            $table->dropColumn(['role', 'is_active', 'phone']);
        });
    }
};
