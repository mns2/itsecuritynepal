<?php

namespace App\Mail;

use App\Models\ExamBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Tells the candidate their booking moved on (payment verified, exam booked, payment not confirmed). */
class ExamBookingUpdate extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ExamBooking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Red Hat exam booking '.$this->booking->reference.': '.ExamBooking::STATUSES[$this->booking->status]);
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.exam-booking-update', with: ['url' => $this->booking->trackUrl()]);
    }
}
