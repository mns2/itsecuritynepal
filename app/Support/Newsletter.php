<?php

namespace App\Support;

use App\Mail\ConfirmSubscription;
use App\Models\Subscriber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/** Adds people to the newsletter list (pop-up, footer, enquiry opt-in) with optional double opt-in. */
class Newsletter
{
    /** @return string 'pending' (confirmation email sent), 'subscribed' or 'already' */
    public static function subscribe(string $email, ?string $name, string $source, ?int $enquiryId = null, ?string $ipHash = null): string
    {
        $email = Str::lower(trim($email));
        $doubleOptIn = (bool) Popups::get('newsletter.double_opt_in', true);
        $subscriber = Subscriber::firstWhere('email', $email);

        if ($subscriber?->status === 'subscribed') {
            return 'already';
        }

        $subscriber ??= new Subscriber(['email' => $email]);
        $subscriber->fill([
            'name' => $subscriber->name ?: ($name ?: null),
            'source' => $subscriber->exists ? $subscriber->source : $source,
            'enquiry_id' => $subscriber->enquiry_id ?? $enquiryId,
            'ip_hash' => $ipHash,
            'consent_at' => now(),
            'status' => $doubleOptIn ? 'pending' : 'subscribed',
            'confirmed_at' => $doubleOptIn ? null : now(),
            'unsubscribed_at' => null,
        ])->save();

        if (! $doubleOptIn) {
            return 'subscribed';
        }

        try {
            Mail::to($subscriber->email)->send(new ConfirmSubscription($subscriber));
        } catch (\Throwable $e) {
            // The sign-up is kept as "waiting for confirmation"; staff can confirm it in the admin panel.
            Log::error('Newsletter confirmation email failed', ['subscriber' => $subscriber->id, 'error' => $e->getMessage()]);
        }

        return 'pending';
    }

    public static function message(string $result): string
    {
        return match ($result) {
            'pending' => 'Almost done: please check your inbox and click the link to confirm.',
            'already' => 'You are already subscribed. Thank you!',
            default => 'Thank you! You will get new batch dates and offers by email.',
        };
    }
}
