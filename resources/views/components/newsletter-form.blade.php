{{-- Newsletter / offers sign-up. Works without JS (POST + redirect back) and with JS (inline, no reload).
     Spam protection: CSRF, honeypot, minimum fill time, rate limit. --}}
@props(['id' => 'newsletter', 'source' => 'footer', 'button' => 'Subscribe', 'dark' => false])
@php($flash = session('newsletter_status'))
<form id="{{ $id }}" action="{{ route('newsletter.subscribe') }}" method="post" novalidate {{ $attributes->class(['grid gap-3']) }}
      x-data="newsletterForm()" @submit.prevent="submit">
    @csrf
    <input type="hidden" name="source" value="{{ $source }}">
    <input type="hidden" name="issued" value="{{ encrypt(now()->timestamp) }}">
    <div class="absolute -left-[9999px]" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

    <template x-if="state === 'success'">
        <p @class(['flex items-start gap-2 rounded-lg p-3 text-sm font-medium', 'bg-white/10 text-white' => $dark, 'bg-success-50 text-success-700' => ! $dark]) role="status">
            <x-icon name="circle-check" class="mt-0.5 size-4 shrink-0" /><span x-text="message"></span>
        </p>
    </template>
    @if ($flash)<p @class(['rounded-lg p-3 text-sm', 'bg-white/10 text-white' => $dark, 'bg-success-50 text-success-700' => ! $dark]) role="status" x-show="state !== 'success'">{{ $flash }}</p>@endif

    <div x-show="state !== 'success'" class="grid gap-3">
        <div class="flex flex-col gap-2 sm:flex-row">
            <label for="{{ $id }}-email" class="sr-only">Email address</label>
            <input id="{{ $id }}-email" name="email" type="email" autocomplete="email" required maxlength="160" placeholder="you@example.com"
                   x-model.trim="email" :aria-invalid="!!error" aria-describedby="{{ $id }}-err" class="input min-w-0 flex-1">
            <button type="submit" class="btn btn-sun shrink-0" :disabled="state === 'loading'">
                <span x-show="state !== 'loading'">{{ $button }}</span><span x-show="state === 'loading'" x-cloak>Sending…</span>
            </button>
        </div>
        <label @class(['flex items-start gap-2.5 text-xs leading-relaxed', 'text-navy-100' => $dark, 'text-ink-muted' => ! $dark])>
            <input type="checkbox" name="consent" value="1" x-model="consent" class="mt-0.5 size-4 shrink-0 accent-navy-700" required>
            <span>Email me new batch dates, scholarships and offers. I can unsubscribe at any time. <a href="/privacy-policy/" @class(['underline', 'text-white' => $dark])>Privacy policy</a></span>
        </label>
        <p id="{{ $id }}-err" class="field-error" x-show="error" x-cloak><x-icon name="circle-alert" class="size-4" /><span x-text="error"></span></p>
    </div>
</form>
