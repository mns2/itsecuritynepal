<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** Someone who opted in to the newsletter / offers list. */
class Subscriber extends Model
{
    public const STATUSES = [
        'pending' => 'Waiting for confirmation',
        'subscribed' => 'Subscribed',
        'unsubscribed' => 'Unsubscribed',
        'bounced' => 'Bounced',
    ];

    public const SOURCES = ['popup' => 'Website pop-up', 'footer' => 'Footer sign-up', 'enquiry' => 'Enquiry form', 'landing' => 'Offer page', 'admin' => 'Added by staff'];

    protected $guarded = ['id'];

    protected $casts = [
        'consent_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Subscriber $s) {
            $s->email = Str::lower(trim($s->email));
            $s->token ??= Str::random(48);
            $s->consent_at ??= now();
        });
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function scopeSubscribed(Builder $q): Builder
    {
        return $q->where('status', 'subscribed');
    }

    public function confirm(): void
    {
        $this->forceFill(['status' => 'subscribed', 'confirmed_at' => $this->confirmed_at ?? now(), 'unsubscribed_at' => null])->save();
    }

    public function unsubscribe(): void
    {
        $this->forceFill(['status' => 'unsubscribed', 'unsubscribed_at' => now()])->save();
    }

    public function unsubscribeUrl(): string
    {
        return route('newsletter.unsubscribe', $this->token);
    }

    public function confirmUrl(): string
    {
        return route('newsletter.confirm', $this->token);
    }
}
