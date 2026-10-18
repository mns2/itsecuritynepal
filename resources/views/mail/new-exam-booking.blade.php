<x-mail::message>
# New Red Hat exam booking: verify the payment

| | |
|:--|:--|
| **Reference** | {{ $booking->reference }} |
| **Exam** | {{ $booking->exam }} |
| **Fee** | NPR {{ number_format($booking->fee_npr) }} |
| **Name** | {{ $booking->name }} |
| **Phone** | {{ $booking->phone }} |
| **Email** | {{ $booking->email }} |
| **Red Hat login** | {{ $booking->redhat_login }} |
| **Red Hat account email** | {{ $booking->redhat_email }} |
| **Transaction ID** | {{ $booking->transaction_id ?: '—' }} |

Check the payment screenshot in the admin panel and match it against the account statement before booking the exam.

<x-mail::button :url="url('/admin/exam-bookings/'.$booking->id.'/edit')">
Review in admin panel
</x-mail::button>

Reply to this email to answer {{ $booking->name }} directly.
</x-mail::message>
