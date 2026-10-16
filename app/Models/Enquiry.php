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

    /** Entered by staff in the admin panel. */
    public const MANUAL_SOURCES = ['walkin' => 'Walk-in', 'phone' => 'Phone call', 'whatsapp' => 'WhatsApp / Viber', 'social' => 'Facebook / Instagram message', 'event' => 'Event / seminar', 'referral' => 'Referral', 'other_manual' => 'Other'];

    public const FORMS = ['course' => 'Website: course enquiry', 'group' => 'Website: group training', 'corporate' => 'Website: corporate', 'contact' => 'Website: contact page'] + self::MANUAL_SOURCES;

    protected $guarded = ['id'];

    protected $casts = ['consent' => 'boolean'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }
}
