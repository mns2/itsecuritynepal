<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A person enrolled in a course, optionally linked to a class and to the enquiry they came from. */
class Student extends Model
{
    public const STATUSES = [
        'enrolled' => 'Studying',
        'completed' => 'Completed',
        'dropped' => 'Dropped',
    ];

    protected $guarded = ['id'];

    public const PAYMENT_METHODS = ['cash' => 'Cash', 'bank' => 'Bank transfer', 'esewa' => 'eSewa', 'khalti' => 'Khalti', 'fonepay' => 'Fonepay / QR', 'cheque' => 'Cheque'];

    protected $casts = [
        'enrolled_on' => 'date',
        'completed_on' => 'date',
        'certified' => 'boolean',
        'fee_due_on' => 'date',
        'fee_confirmed' => 'boolean',
        'fee_confirmed_at' => 'datetime',
    ];

    /** Amount payable after discount (null when no fee recorded). */
    public function feePayable(): ?int
    {
        return $this->fee_total === null ? null : max(0, (int) $this->fee_total - (int) $this->fee_discount);
    }

    public function feeBalance(): ?int
    {
        $payable = $this->feePayable();

        return $payable === null ? null : max(0, $payable - (int) $this->fee_paid);
    }

    /** not_set | unpaid | partial | paid */
    public function paymentStatus(): string
    {
        $payable = $this->feePayable();

        return match (true) {
            $payable === null => 'not_set',
            $payable === 0 || (int) $this->fee_paid >= $payable => 'paid',
            (int) $this->fee_paid > 0 => 'partial',
            default => 'unpaid',
        };
    }

    public const PAYMENT_STATUSES = ['not_set' => 'No fee recorded', 'unpaid' => 'Unpaid', 'partial' => 'Partly paid', 'paid' => 'Paid'];

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class)->latest('paid_on');
    }

    /** Keep fee_paid equal to the sum of recorded payments. */
    public function syncFeePaid(): void
    {
        $sum = (int) $this->payments()->sum('amount');
        $last = $this->payments()->first();
        $this->forceFill([
            'fee_paid' => $sum,
            'payment_method' => $last?->method ?? $this->payment_method,
            'receipt_no' => $last?->receipt_no ?? $this->receipt_no,
        ])->saveQuietly();
    }

    public function feeConfirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fee_confirmed_by');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function trainingClass(): BelongsTo
    {
        return $this->belongsTo(TrainingClass::class);
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /** Attended (present + late) as a percentage of this student's marks, or null if none yet. */
    public function attendanceRate(): ?int
    {
        $total = $this->attendances()->count();

        return $total ? (int) round($this->attendances()->whereIn('status', Attendance::ATTENDED)->count() / $total * 100) : null;
    }

    public function scopeStudying(Builder $q): Builder
    {
        return $q->where('status', 'enrolled');
    }

    public function scopeCompleted(Builder $q): Builder
    {
        return $q->where('status', 'completed');
    }
}
