<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CampaignController extends Controller
{
    /** Landing page for ads: /offer/{slug}/ */
    public function show(Request $request, string $slug): View
    {
        $campaign = Campaign::where('slug', $slug)->where('has_landing_page', true)->with('course')->firstOrFail();
        $preview = self::isPreview($request, $campaign);

        // Switched-off campaigns disappear; ended ones keep their URL (shared links) but say so.
        abort_unless($campaign->is_active || $preview, 404);
        abort_if(! $preview && $campaign->starts_at?->isFuture(), 404);

        return view('pages.campaign', [
            'campaign' => $campaign,
            'ended' => $campaign->ends_at?->isPast() ?? false,
            'body' => filled($campaign->landing_body) ? Str::markdown($campaign->landing_body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) : null,
            'preview' => $preview,
        ]);
    }

    /** Counts pop-up views and button clicks (sent by the pop-up script). */
    public function track(Request $request, Campaign $campaign): JsonResponse
    {
        $event = $request->input('event');
        if ($campaign->isLive() && in_array($event, ['view', 'click'], true)) {
            $campaign->increment($event === 'view' ? 'views' : 'clicks');
        }

        return response()->json(['ok' => true]);
    }

    /** Signed-in staff can preview any campaign with ?campaign_preview={id}. */
    public static function isPreview(Request $request, Campaign $campaign): bool
    {
        return (int) $request->query('campaign_preview') === $campaign->id && auth()->check();
    }

    /** The campaign to show as a pop-up on this request, if any. */
    public static function forRequest(Request $request): ?Campaign
    {
        if ($request->is('admin', 'admin/*', 'offer/*') || ! $request->isMethod('GET')) {
            return null;
        }

        if ($id = (int) $request->query('campaign_preview')) {
            return auth()->check() ? Campaign::with('course')->find($id) : null;
        }

        $path = '/'.ltrim($request->path(), '/');

        return Campaign::live()->with('course')->orderByDesc('priority')->latest('id')->get()
            ->first(fn (Campaign $c) => $c->matchesPath($path));
    }
}
