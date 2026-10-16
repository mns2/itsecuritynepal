@php($contact = $site['contact'] ?? [])
<div class="fixed inset-x-0 bottom-0 z-40 border-t border-line bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur sm:hidden">
    <div class="grid grid-cols-3 text-xs font-semibold">
        <a href="tel:{{ $contact['phonePrimary']['tel'] ?? '' }}" class="flex min-h-14 flex-col items-center justify-center gap-1 text-navy-700 no-underline"><x-icon name="phone" class="size-5" />Call</a>
        @if (! empty($contact['whatsapp']['number']))
            <a href="https://wa.me/{{ $contact['whatsapp']['number'] }}?text={{ rawurlencode('Hello, I would like to know about your courses.') }}" class="flex min-h-14 flex-col items-center justify-center gap-1 text-navy-700 no-underline" rel="noopener" target="_blank"><x-icon name="message-circle" class="size-5" />WhatsApp<span class="sr-only"> (opens in a new tab)</span></a>
        @else
            <a href="/contact/" class="flex min-h-14 flex-col items-center justify-center gap-1 text-navy-700 no-underline"><x-icon name="mail" class="size-5" />Contact</a>
        @endif
        <button type="button" class="flex min-h-14 flex-col items-center justify-center gap-1 bg-sun-400 text-navy-700" @click="$dispatch('open-enquiry', {})"><x-icon name="send" class="size-5" />Enquire</button>
    </div>
</div>
<div class="h-14 sm:hidden" aria-hidden="true"></div>
