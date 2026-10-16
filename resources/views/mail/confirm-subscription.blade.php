<x-mail::message>
# Confirm your subscription

Thank you for signing up for new batch dates, scholarships and offers from **IT Security Nepal**.

Please confirm that this is your email address:

<x-mail::button :url="$subscriber->confirmUrl()" color="primary">
Yes, subscribe me
</x-mail::button>

If you did not sign up, simply ignore this email. You will not receive anything else from us.

IT Security Nepal
</x-mail::message>
