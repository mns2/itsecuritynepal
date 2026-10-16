<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Internal training records for the admin dashboard (never shown on the public site):
 * classes = real running cohorts of a course; students = people enrolled in a course / class.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('name');                          // e.g. "RHCSA · Morning · Nov 2026"
            $table->string('audience')->default('Individual'); // Individual / Group / Corporate Batch
            $table->string('organisation')->nullable();      // for corporate batches
            $table->string('trainer')->nullable();
            $table->string('mode')->nullable();
            $table->string('schedule')->nullable();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->string('status')->default('upcoming');   // upcoming / ongoing / completed / cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'starts_on']);
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email', 160)->nullable();
            $table->foreignId('course_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('training_class_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('enquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('audience')->default('Individual');
            $table->string('organisation')->nullable();
            $table->string('status')->default('enrolled');   // enrolled / completed / dropped
            $table->date('enrolled_on');
            $table->date('completed_on')->nullable();
            $table->boolean('certified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'enrolled_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('training_classes');
    }
};
