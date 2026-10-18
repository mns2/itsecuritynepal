<?php

namespace App\Mail;

use App\Models\ExamBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Staff alert for a new exam booking. The payment screenshot is NOT attached: staff open it in the admin panel. */
class NewExamBooking extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ExamBooking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->booking->email, $this->booking->name)],
            subject: "Exam booking {$this->booking->reference}: {$this->booking->exam} (verify payment)",
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.new-exam-booking');
    }
}
