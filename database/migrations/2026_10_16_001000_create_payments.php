<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Every fee payment (instalment) received from a student. students.fee_paid is kept as their sum. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount');                 // NPR
            $table->date('paid_on');
            $table->string('method')->default('cash');         // see Student::PAYMENT_METHODS
            $table->string('receipt_no')->nullable();
            $table->string('note')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('paid_on');
        });

        // Carry over any amount already recorded on students as a single opening payment.
        foreach (DB::table('students')->where('fee_paid', '>', 0)->get() as $s) {
            DB::table('payments')->insert([
                'student_id' => $s->id, 'amount' => $s->fee_paid, 'paid_on' => $s->enrolled_on,
                'method' => $s->payment_method ?: 'cash', 'receipt_no' => $s->receipt_no, 'note' => 'Opening balance',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
