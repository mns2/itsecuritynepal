@php($record = $getRecord())
@php($url = route('exam-booking.proof', $record))
<div>
    @if ($record->proofIsImage())
        <a href="{{ $url }}" target="_blank" rel="noopener" title="Open full size">
            <img src="{{ $url }}" alt="Payment screenshot for {{ $record->reference }}" style="max-height: 28rem; width: auto; max-width: 100%; border-radius: .5rem; border: 1px solid rgb(229 231 235);">
        </a>
    @else
        <p style="font-size: .875rem;">PDF receipt uploaded.</p>
    @endif
    <p style="margin-top: .75rem; font-size: .875rem;">
        <a href="{{ $url }}" target="_blank" rel="noopener" style="color: #1F5BB8; font-weight: 600;">Open in a new tab</a>
    </p>
</div>
