<?php

namespace App\Mail;

use App\Models\ExamBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Sent to the candidate after booking: the booking only reaches staff once they click the link. */
class ConfirmExamBookingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ExamBooking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Confirm your email: Red Hat exam booking {$this->booking->reference}");
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.confirm-exam-booking', with: ['url' => $this->booking->verifyUrl()]);
    }
}
