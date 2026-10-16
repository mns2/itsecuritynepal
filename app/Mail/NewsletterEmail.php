<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\Setting;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/** One email campaign, personalised for one subscriber (unsubscribe link + one-click unsubscribe header). */
class NewsletterEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EmailCampaign $email, public Subscriber $subscriber, public bool $isTest = false) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: ($this->isTest ? '[TEST] ' : '').$this->email->subject);
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.$this->subscriber->unsubscribeUrl().'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        $contact = (array) Setting::get('contact', []);

        return new Content(
            view: 'mail.newsletter',
            text: 'mail.newsletter-text',
            with: [
                'bodyHtml' => Str::markdown($this->email->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]),
                'imageUrl' => media($this->email->image),
                'ctaUrl' => $this->email->trackedCtaUrl(),
                'unsubscribeUrl' => $this->subscriber->unsubscribeUrl(),
                'address' => $contact['address'] ?? null,
                'phone' => $contact['phonePrimary']['label'] ?? null,
                'siteUrl' => rtrim(config('app.url'), '/').'/',
                'logoUrl' => asset('images/logo-itsn.png'),
            ],
        );
    }
}
