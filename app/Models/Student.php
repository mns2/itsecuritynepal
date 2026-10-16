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

    protected $casts = [
        'enrolled_on' => 'date',
        'completed_on' => 'date',
        'certified' => 'boolean',
    ];

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

    public function scopeStudying(Builder $q): Builder
    {
        return $q->where('status', 'enrolled');
    }

    public function scopeCompleted(Builder $q): Builder
    {
        return $q->where('status', 'completed');
    }
}
