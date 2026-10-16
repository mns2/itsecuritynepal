<?php

namespace App\Support;

use App\Mail\NewsletterEmail;
use App\Models\EmailCampaign;
use App\Models\EmailCampaignRecipient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * Delivers queued email-campaign messages in small batches.
 * Two ways to drive it (either works, both together are safe thanks to the lock):
 *  - from the admin panel: the Email campaigns page sends a batch every 15 seconds while it is open;
 *  - automatically: the scheduler (`schedule:run` from a cPanel cron job) calls `newsletter:send` every minute.
 */
class NewsletterSender
{
    /** Emails per minute, from Admin → Site settings → Pop-ups → Newsletter (fallback: .env NEWSLETTER_PER_MINUTE). */
    public static function perMinute(): int
    {
        return max(1, (int) (Popups::get('newsletter.per_minute') ?: config('services.newsletter.per_minute', 30)));
    }

    /** Is anything waiting to go out (now or already due)? */
    public static function hasWork(): bool
    {
        return EmailCampaign::where('status', 'sending')->exists()
            || EmailCampaign::where('status', 'scheduled')->where('scheduled_at', '<=', now())->exists();
    }

    /** True if the server cron has run the scheduler in the last 3 minutes. */
    public static function cronActive(): bool
    {
        $last = Cache::get('scheduler.last_run');

        return $last && now()->diffInSeconds($last, absolute: true) < 180;
    }

    public static function heartbeat(): void
    {
        Cache::put('scheduler.last_run', now(), now()->addHour());
    }

    /**
     * Send up to $limit messages. Respects the per-minute limit across all callers (cron + open admin pages).
     * @return int messages handled in this run
     */
    public static function run(?int $limit = null): int
    {
        $lock = Cache::lock('newsletter.sending', 120);
        if (! $lock->get()) {
            return 0; // another run (cron or another admin tab) is busy
        }

        try {
            // Per-minute budget shared by everyone who triggers sending
            $minuteKey = 'newsletter.sent.'.now()->format('YmdHi');
            $usedThisMinute = (int) Cache::get($minuteKey, 0);
            $budget = min($limit ?? PHP_INT_MAX, self::perMinute() - $usedThisMinute);
            if ($budget <= 0) {
                return 0;
            }

            EmailCampaign::where('status', 'scheduled')->where('scheduled_at', '<=', now())
                ->update(['status' => 'sending', 'started_at' => now()]);

            $sent = 0;
            foreach (EmailCampaign::where('status', 'sending')->orderBy('scheduled_at')->get() as $email) {
                $email->started_at ??= now();
                $batch = $email->recipients()->where('status', 'queued')->with('subscriber')->limit(max(0, $budget - $sent))->get();
                foreach ($batch as $recipient) {
                    self::deliver($email, $recipient);
                    $sent++;
                }

                $email->forceFill([
                    'sent_count' => $email->recipients()->where('status', 'sent')->count(),
                    'failed_count' => $email->recipients()->where('status', 'failed')->count(),
                ]);
                if (! $email->recipients()->where('status', 'queued')->exists()) {
                    $email->forceFill(['status' => 'sent', 'finished_at' => now()]);
                }
                $email->save();

                if ($sent >= $budget) {
                    break;
                }
            }

            Cache::put($minuteKey, $usedThisMinute + $sent, now()->addMinutes(2));

            return $sent;
        } finally {
            $lock->release();
        }
    }

    private static function deliver(EmailCampaign $email, EmailCampaignRecipient $recipient): void
    {
        $subscriber = $recipient->subscriber;
        if (! $subscriber || $subscriber->status !== 'subscribed') {
            $recipient->update(['status' => 'skipped']); // unsubscribed after the campaign was queued

            return;
        }

        try {
            Mail::to($subscriber->email)->send(new NewsletterEmail($email, $subscriber));
            $recipient->update(['status' => 'sent', 'sent_at' => now(), 'error' => null]);
        } catch (\Throwable $e) {
            $recipient->update(['status' => 'failed', 'error' => mb_substr($e->getMessage(), 0, 500)]);
        }
    }
}
