<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Fee agreed at enrolment and payments received (amounts in NPR, whole rupees). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedInteger('fee_total')->nullable()->after('certified');     // course fee before discount
            $table->unsignedInteger('fee_discount')->default(0)->after('fee_total');
            $table->string('discount_reason')->nullable()->after('fee_discount');      // e.g. Dashain offer, group discount
            $table->unsignedInteger('fee_paid')->default(0)->after('discount_reason');
            $table->string('payment_method')->nullable()->after('fee_paid');           // cash | bank | esewa | khalti | fonepay | cheque
            $table->string('receipt_no')->nullable()->after('payment_method');
            $table->date('fee_due_on')->nullable()->after('receipt_no');               // next instalment
            $table->boolean('fee_confirmed')->default(false)->after('fee_due_on');     // office confirmed the fee arrangement
            $table->foreignId('fee_confirmed_by')->nullable()->after('fee_confirmed')->constrained('users')->nullOnDelete();
            $table->timestamp('fee_confirmed_at')->nullable()->after('fee_confirmed_by');
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->after('ip_hash')->constrained('users')->nullOnDelete(); // manual entries
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', fn (Blueprint $t) => $t->dropConstrainedForeignId('created_by'));
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('fee_confirmed_by');
            $table->dropColumn(['fee_total', 'fee_discount', 'discount_reason', 'fee_paid', 'payment_method', 'receipt_no', 'fee_due_on', 'fee_confirmed', 'fee_confirmed_at']);
        });
    }
};
