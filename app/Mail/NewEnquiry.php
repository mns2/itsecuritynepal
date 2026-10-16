<?php

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewEnquiry extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry) {}

    public function envelope(): Envelope
    {
        $what = $this->enquiry->course?->name ?? ucfirst((string) $this->enquiry->interest ?: 'General');

        return new Envelope(
            replyTo: [new Address($this->enquiry->email, $this->enquiry->name)],
            subject: "New {$this->enquiry->audience} enquiry: {$what}",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.new-enquiry');
    }
}
