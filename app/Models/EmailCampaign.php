<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** An email sent from the admin panel to everyone on the newsletter list. */
class EmailCampaign extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'scheduled' => 'Scheduled',
        'sending' => 'Sending',
        'sent' => 'Sent',
        'cancelled' => 'Cancelled',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(EmailCampaignRecipient::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'scheduled', 'cancelled'], true);
    }

    /**
     * Queue every current subscriber (once) and mark the campaign for sending.
     * The scheduler (`newsletter:send`, every minute) then delivers in small batches to stay within the host's email limits.
     */
    public function queueForSending(?\DateTimeInterface $at = null): int
    {
        $now = now();
        $rows = Subscriber::subscribed()->pluck('id')
            ->map(fn ($id) => ['email_campaign_id' => $this->id, 'subscriber_id' => $id, 'status' => 'queued'])
            ->all();
        foreach (array_chunk($rows, 500) as $chunk) {
            EmailCampaignRecipient::insertOrIgnore($chunk);
        }

        $this->forceFill([
            'status' => $at && $at > $now ? 'scheduled' : 'sending',
            'scheduled_at' => $at ?? $now,
            'recipients_count' => $this->recipients()->count(),
        ])->save();

        return $this->recipients_count;
    }

    /** The link in the email goes through /newsletter/click/{id} so clicks are counted. */
    public function trackedCtaUrl(): ?string
    {
        return $this->cta_url ? route('newsletter.click', $this) : null;
    }
}
