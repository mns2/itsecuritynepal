<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enquiry extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'enrolled' => 'Enrolled',
        'closed' => 'Closed',
        'spam' => 'Spam',
    ];

    public const FORMS = ['course' => 'Course enquiry', 'group' => 'Group training', 'corporate' => 'Corporate', 'contact' => 'Contact page'];

    protected $guarded = ['id'];

    protected $casts = ['consent' => 'boolean'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
