<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Red Hat exam booking requests from /red-hat-exam-booking-nepal/.
 * Payment screenshots live on the private `local` disk (never web-accessible); staff view them through an authorised route.
 * Red Hat passwords are never collected.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();          // shown to the candidate, e.g. RHX-7K2P9Q
            $table->string('name');
            $table->string('phone', 30);
            $table->string('email');
            $table->string('redhat_login', 100);                // Red Hat login / username
            $table->string('redhat_email');                     // email on the Red Hat account
            $table->string('exam');                             // exam label at time of booking
            $table->unsignedInteger('fee_npr');                 // fee shown when they booked
            $table->string('transaction_id', 100)->nullable();
            $table->string('proof_path');                       // private disk path
            $table->string('proof_mime', 60);
            $table->string('status')->default('pending');       // see ExamBooking::STATUSES
            $table->dateTime('exam_at')->nullable();            // filled by staff once booked
            $table->text('staff_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('source_url', 500)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_bookings');
    }
};
