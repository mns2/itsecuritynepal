<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enquiry extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'enrolled' => 'Enrolled',
        'closed' => 'Closed',
        'spam' => 'Spam',
    ];

    /** "Training for" options: audience id (settings) => value stored on the enquiry. */
    public const AUDIENCES = ['individual' => 'Individual', 'group' => 'Group', 'corporate' => 'Corporate Batch'];

    public const FORMS = ['course' => 'Course enquiry', 'group' => 'Group training', 'corporate' => 'Corporate', 'contact' => 'Contact page'];

    protected $guarded = ['id'];

    protected $casts = ['consent' => 'boolean'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }
}
