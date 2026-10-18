<x-mail::message>
# Confirm your email address

Hi {{ strtok($booking->name, ' ') }},

We received your Red Hat exam booking request **{{ $booking->reference }}** for **{{ $booking->exam }}**.

Please confirm this is your email address. Our team starts checking your payment once you do.

<x-mail::button :url="$url">
Confirm email and track booking
</x-mail::button>

After you confirm, your payment and exam booking are **pending verification**. We will email you at each step, and you can check progress any time on the tracking page with your booking reference and this email address.

If you did not make this request, ignore this email.

We will never ask for your Red Hat password or bank PIN.

IT Security Nepal
</x-mail::message>
