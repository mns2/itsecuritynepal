{{ $email->heading ?: $email->subject }}

{!! strip_tags($email->body) !!}

@if ($ctaUrl && $email->cta_label)
{{ $email->cta_label }}: {{ $ctaUrl }}
@endif

--
IT Security Nepal{{ $address ? ' · '.$address : '' }}
You are receiving this because you subscribed to updates on itsecuritynepal.com.
Unsubscribe: {{ $unsubscribeUrl }}
