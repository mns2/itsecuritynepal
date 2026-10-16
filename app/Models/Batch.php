<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    public const STATUSES = [
        'open' => 'Enrolling',
        'few' => 'Few seats left',
        'soon' => 'Opening soon',
        'waitlist' => 'Waitlist',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'starts_on' => 'date',
        'is_illustrative' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
