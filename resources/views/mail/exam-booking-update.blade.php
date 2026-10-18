<x-mail::message>
# {{ \App\Models\ExamBooking::STATUSES[$booking->status] }}

Hi {{ strtok($booking->name, ' ') }},

@switch($booking->status)
@case('verified')
We have verified your payment of NPR {{ number_format($booking->fee_npr) }} for booking **{{ $booking->reference }}** ({{ $booking->exam }}).

**Next: exam booking in progress.** We are now booking your exam with your Red Hat account details. This can take some time. We will email you again as soon as your exam booking is confirmed.
@break
@case('booked')
Your exam is booked: **{{ $booking->exam }}**{{ $booking->exam_at ? ' on '.$booking->exam_at->format('l, j F Y \a\t g:i a') : '' }}.

Check your Red Hat account ({{ $booking->redhat_email }}) for the official exam details.
@break
@case('rejected')
We could not match your payment for booking **{{ $booking->reference }}** with our account. Please call us with your payment screenshot and transaction ID so we can sort it out.
@break
@default
The status of your booking **{{ $booking->reference }}** has changed.
@endswitch

<x-mail::button :url="$url">
Track your booking
</x-mail::button>

@if ($phone = \App\Models\Setting::get('contact.phonePrimary.label'))
Questions? Call {{ $phone }}.
@endif

IT Security Nepal
</x-mail::message>
