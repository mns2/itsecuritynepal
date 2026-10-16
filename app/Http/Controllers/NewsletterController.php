<?php

namespace App\Http\Controllers;

use App\Models\EmailCampaign;
use App\Models\Subscriber;
use App\Support\Newsletter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /** Sign-up from the pop-up, footer or an offer page. Same spam protection as enquiries. */
    public function subscribe(Request $request): JsonResponse|RedirectResponse
    {
        if (filled($request->input('website')) || ! $this->filledSlowly($request)) {
            return $this->respond($request, 'subscribed'); // bots get a normal-looking answer
        }

        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:160'],
            'name' => ['nullable', 'string', 'max:120'],
            'source' => ['nullable', 'in:popup,footer,landing'],
            'consent' => ['accepted'],
        ], [
            'consent.accepted' => 'Please tick the box so we can email you.',
            'email.required' => 'Enter your email address.',
            'email.email' => 'Enter a valid email address, e.g. name@example.com.',
        ]);

        $result = Newsletter::subscribe($data['email'], $data['name'] ?? null, $data['source'] ?? 'popup', null, hash('sha256', $request->ip().config('app.key')));

        return $this->respond($request, $result);
    }

    public function confirm(string $token): View
    {
        $subscriber = Subscriber::where('token', $token)->firstOrFail();
        if ($subscriber->status !== 'unsubscribed') {
            $subscriber->confirm();
        }

        return view('pages.newsletter', ['state' => 'confirmed', 'subscriber' => $subscriber]);
    }

    /** Shows a confirmation button (links in emails are often pre-fetched by scanners, so GET never unsubscribes). */
    public function showUnsubscribe(string $token): View
    {
        $subscriber = Subscriber::where('token', $token)->firstOrFail();

        return view('pages.newsletter', ['state' => $subscriber->status === 'unsubscribed' ? 'unsubscribed' : 'confirm-unsubscribe', 'subscriber' => $subscriber]);
    }

    /** Button on the page above, and mail apps' one-click "Unsubscribe" (RFC 8058, no CSRF token). */
    public function unsubscribe(string $token): View
    {
        $subscriber = Subscriber::where('token', $token)->firstOrFail();
        $subscriber->unsubscribe();

        return view('pages.newsletter', ['state' => 'unsubscribed', 'subscriber' => $subscriber]);
    }

    /** Counts clicks on an email campaign's button, then forwards to its link. */
    public function click(EmailCampaign $emailCampaign): RedirectResponse
    {
        abort_unless($emailCampaign->cta_url, 404);
        $emailCampaign->increment('clicks');

        return redirect()->away($emailCampaign->cta_url);
    }

    private function filledSlowly(Request $request): bool
    {
        try {
            $issued = (int) decrypt((string) $request->input('issued'));
        } catch (\Throwable) {
            return false;
        }

        return now()->timestamp - $issued >= 2;
    }

    private function respond(Request $request, string $result): JsonResponse|RedirectResponse
    {
        $message = Newsletter::message($result);

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'result' => $result, 'message' => $message])
            : back()->with('newsletter_status', $message);
    }
}
