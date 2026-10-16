{{-- Newsletter / offers sign-up pop-up. Settings: Site settings → Pop-ups. --}}
@php
    $cfg = [
        'key' => 'itsn-newsletter-popup',
        'priority' => 10,
        'trigger' => $p['trigger'] ?? 'scroll',
        'delay' => (int) ($p['delay_seconds'] ?? 20),
        'scroll' => (int) ($p['scroll_percent'] ?? 60),
        'frequency' => $p['frequency'] ?? 'week',
        'device' => $p['device'] ?? 'all',
    ];
@endphp
<div x-data="sitePopup(@js($cfg))" x-show="open" x-cloak @newsletter-subscribed.window="done()"
     class="fixed inset-0 z-[65] flex items-end justify-center sm:items-center sm:p-6"
     role="dialog" aria-modal="true" aria-labelledby="nl-popup-title" @keydown.escape.window="open && close()">
    <div class="absolute inset-0 bg-navy-900/60" @click="close()" x-show="open" x-transition.opacity aria-hidden="true"></div>
    <div x-show="open" x-trap.noscroll.inert="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0 sm:translate-y-0 sm:scale-95"
         class="relative max-h-[92dvh] w-full overflow-y-auto rounded-t-2xl bg-white shadow-2xl sm:max-w-md sm:rounded-2xl">
        <button type="button" @click="close()" class="absolute top-3 right-3 z-10 grid size-10 place-items-center rounded-full bg-white/95 text-navy-700 shadow-md hover:bg-white">
            <x-icon name="x" class="size-5" /><span class="sr-only">Close</span>
        </button>
        <div class="flex items-center gap-3 bg-navy-700 px-6 py-5 text-white sm:px-8" aria-hidden="true">
            <span class="grid size-12 place-items-center rounded-xl bg-sun-400 text-navy-900"><x-icon name="mail" class="size-6" /></span>
            <span class="font-display text-lg font-bold">{{ $p['badge'] ?: 'Newsletter' }}</span>
        </div>
        <div class="p-6 sm:p-8">
            <h2 id="nl-popup-title" class="text-h3">{{ $p['title'] }}</h2>
            @if (filled($p['text'] ?? null))<p class="mt-2 mb-5 text-ink-muted">{{ $p['text'] }}</p>@endif
            <x-newsletter-form id="nl-popup-form" source="popup" :button="$p['button']" />
        </div>
    </div>
</div>
