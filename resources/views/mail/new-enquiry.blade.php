<x-mail::message>
# New enquiry from the website

| | |
|:--|:--|
| **Name** | {{ $enquiry->name }} |
| **Phone** | {{ $enquiry->phone }} |
| **Email** | {{ $enquiry->email }} |
| **Training for** | {{ $enquiry->audience }}{{ $enquiry->group_size ? ' ('.$enquiry->group_size.')' : '' }} |
| **Course / area** | {{ $enquiry->course?->name ?? ucfirst((string) $enquiry->interest) }} |
@if ($enquiry->mode)
| **Preferred mode** | {{ $enquiry->mode }} |
@endif
@if ($enquiry->organisation)
| **Organisation** | {{ $enquiry->organisation }} |
@endif
| **Form** | {{ \App\Models\Enquiry::FORMS[$enquiry->form] ?? $enquiry->form }} |
| **Page** | {{ $enquiry->source_url }} |

@if ($enquiry->message)
**Message**

{{ $enquiry->message }}
@endif

<x-mail::button :url="url('/admin/enquiries/'.$enquiry->id.'/edit')">
Open in admin panel
</x-mail::button>

Reply to this email to answer {{ $enquiry->name }} directly.
</x-mail::message>
