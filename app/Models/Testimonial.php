<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['consent_given' => 'boolean', 'is_published' => 'boolean'];

    /** Only reviews with recorded consent can ever be shown. */
    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_published', true)->where('consent_given', true)->orderBy('sort');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
