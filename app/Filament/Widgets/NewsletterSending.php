<?php

namespace App\Filament\Widgets;

use App\Models\EmailCampaign;
use App\Support\Access;
use App\Support\NewsletterSender;
use Filament\Widgets\Widget;

/**
 * Sends email campaigns from the browser: while this widget is on screen (Email campaigns page) and something
 * is waiting, it sends one small batch every 15 seconds. No server cron needed.
 */
class NewsletterSending extends Widget
{
    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.newsletter-sending';

    public int $lastBatch = 0;

    public ?string $lastError = null;

    public static function canView(): bool
    {
        return Access::allows('marketing');
    }

    /** Called by wire:poll every 15 seconds while the page is open. */
    public function tick(): void
    {
        if (! NewsletterSender::hasWork()) {
            $this->lastBatch = 0;

            return;
        }
        try {
            // A quarter of the per-minute limit every 15 s = the per-minute limit overall.
            $this->lastBatch = NewsletterSender::run((int) ceil(NewsletterSender::perMinute() / 4));
            $this->lastError = null;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
        }
    }

    protected function getViewData(): array
    {
        $active = EmailCampaign::whereIn('status', ['sending', 'scheduled'])->orderBy('scheduled_at')->get();

        return [
            'hasWork' => NewsletterSender::hasWork(),
            'cron' => NewsletterSender::cronActive(),
            'perMinute' => NewsletterSender::perMinute(),
            'active' => $active,
            'queued' => (int) $active->sum(fn (EmailCampaign $e) => $e->recipients()->where('status', 'queued')->count()),
        ];
    }
}
