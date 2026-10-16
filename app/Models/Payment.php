<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One fee payment from a student. Saving or deleting one updates the student's total paid. */
class Payment extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['paid_on' => 'date', 'amount' => 'integer'];

    protected static function booted(): void
    {
        static::creating(fn (Payment $p) => $p->received_by ??= auth()->id());
        static::saved(fn (Payment $p) => $p->student?->syncFeePaid());
        static::deleted(fn (Payment $p) => $p->student?->syncFeePaid());
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
