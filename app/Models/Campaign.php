<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/** A marketing campaign: website pop-up + optional /offer/{slug}/ landing page. */
class Campaign extends Model
{
    use \App\Models\Concerns\HasSeo;

    public const STYLES = ['poster' => 'Poster (image only, like a banner)', 'card' => 'Card (image + headline, text and button)'];

    public const ACTIONS = ['enquiry' => 'Open the enquiry form', 'landing' => 'Go to the campaign landing page', 'link' => 'Go to a link'];

    public const SHOW_ON = ['all' => 'Every page', 'home' => 'Homepage only', 'selected' => 'Selected pages'];

    public const DEVICES = ['all' => 'Desktop and mobile', 'desktop' => 'Desktop only', 'mobile' => 'Mobile only'];

    public const TRIGGERS = ['delay' => 'After a few seconds', 'scroll' => 'After scrolling part of the page', 'exit' => 'When the visitor is about to leave (desktop)'];

    public const FREQUENCIES = ['session' => 'Once per visit', 'day' => 'Once a day', 'week' => 'Once a week', 'always' => 'Every page view'];

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'show_countdown' => 'boolean',
        'has_landing_page' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'paths' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enquiries(): HasMany
    {
        return $this->hasMany(Enquiry::class);
    }

    /** Active and inside its date window. */
    public function scopeLive(Builder $q): Builder
    {
        return $q->where('is_active', true)
            ->where(fn ($w) => $w->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($w) => $w->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function isLive(): bool
    {
        return $this->is_active && (! $this->starts_at || $this->starts_at->lte(now())) && (! $this->ends_at || $this->ends_at->gt(now()));
    }

    public function seoPath(): string
    {
        return $this->landingUrl();
    }

    public function landingUrl(): string
    {
        return '/offer/'.$this->slug.'/';
    }

    public function ctaHref(): ?string
    {
        return match ($this->cta_action) {
            'landing' => $this->has_landing_page ? $this->landingUrl() : null,
            'link' => $this->cta_url ?: null,
            default => null, // enquiry: handled by the pop-up (opens the form)
        };
    }

    /** Does this campaign target the given path ("/", "/redhat/")? */
    public function matchesPath(string $path): bool
    {
        $path = '/'.trim($path, '/').($path === '/' ? '' : '/');
        $path = $path === '//' ? '/' : $path;

        return match ($this->show_on) {
            'home' => $path === '/',
            'selected' => collect($this->paths ?? [])->contains(function ($p) use ($path) {
                $p = '/'.trim((string) $p, '/').'/';
                $p = $p === '//' ? '/' : $p;

                return Str::is($p, $path) || Str::is(rtrim($p, '/'), rtrim($path, '/'));
            }),
            default => true,
        };
    }

    public function leadsCount(): int
    {
        return $this->enquiries()->where('status', '!=', 'spam')->count();
    }

    /** Settings the pop-up script needs (no internal fields). */
    public function toPopupArray(): array
    {
        return [
            'id' => $this->id,
            'key' => 'itsn-campaign-'.$this->id.'-'.$this->updated_at?->timestamp, // editing a campaign shows it again
            'leadKey' => 'itsn-lead-campaign-'.$this->id,                          // set after an enquiry from it
            'priority' => 30,
            'device' => $this->device,
            'trigger' => $this->trigger,
            'delay' => max(0, (int) $this->delay_seconds),
            'scroll' => min(95, max(5, (int) $this->scroll_percent)),
            'frequency' => $this->frequency,
            'endsAt' => $this->show_countdown && $this->ends_at ? $this->ends_at->toIso8601String() : null,
            'action' => $this->cta_action,
            'course' => $this->course?->slug,
            'trackUrl' => route('campaigns.track', $this),
        ];
    }
}
