<?php

namespace App\Console\Commands;

use App\Support\NewsletterSender;
use Illuminate\Console\Command;

/**
 * Sends the next batch of email-campaign messages. Optional: run every minute by the scheduler if the
 * cPanel cron job exists; otherwise the admin's Email campaigns page sends while it is open.
 */
class SendNewsletters extends Command
{
    protected $signature = 'newsletter:send {--limit= : Emails to send in this run}';

    protected $description = 'Send the next batch of queued email-campaign messages';

    public function handle(): int
    {
        $sent = NewsletterSender::run($this->option('limit') ? (int) $this->option('limit') : null);
        $this->info("Sent {$sent} email(s).");

        return self::SUCCESS;
    }
}
