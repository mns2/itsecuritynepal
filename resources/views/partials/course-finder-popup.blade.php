{{-- "Not sure which course?" prompt that leads to the Course Finder. Settings: Site settings → Pop-ups. --}}
@php
    $cfg = [
        'key' => 'itsn-course-finder-popup',
        'priority' => 20,
        'trigger' => 'delay',
        'delay' => (int) ($p['delay_seconds'] ?? 5),
        'frequency' => $p['frequency'] ?? 'day',
        'device' => $p['device'] ?? 'all',
    ];
    $img = media($p['image'] ?? null);
@endphp
<div x-data="sitePopup(@js($cfg))" x-show="open" x-cloak
     class="fixed inset-0 z-[65] flex items-end justify-center sm:items-center sm:p-6"
     role="dialog" aria-modal="true" aria-labelledby="cf-popup-title" @keydown.escape.window="open && close()">
    <div class="absolute inset-0 bg-navy-900/60" @click="close()" x-show="open" x-transition.opacity aria-hidden="true"></div>
    <div x-show="open" x-trap.noscroll.inert="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0 sm:translate-y-0 sm:scale-95"
         class="relative max-h-[92dvh] w-full overflow-y-auto rounded-t-2xl bg-white shadow-2xl sm:max-w-md sm:rounded-2xl">
        <button type="button" @click="close()" class="absolute top-3 right-3 z-10 grid size-10 place-items-center rounded-full bg-white/95 text-navy-700 shadow-md hover:bg-white">
            <x-icon name="x" class="size-5" /><span class="sr-only">Close</span>
        </button>
        @if ($img)
            <img src="{{ $img }}" alt="" class="block aspect-[16/9] w-full object-cover" decoding="async">
        @else
            <div class="flex items-center gap-3 bg-navy-700 px-6 py-5 text-white sm:px-8" aria-hidden="true">
                <span class="grid size-12 place-items-center rounded-xl bg-sun-400 text-navy-900"><x-icon name="compass" class="size-6" /></span>
                <span class="font-display text-lg font-bold">Course Finder</span>
            </div>
        @endif
        <div class="p-6 sm:p-8">
            @if (filled($p['badge'] ?? null))<span class="badge bg-sun-100 text-navy-700">{{ $p['badge'] }}</span>@endif
            <h2 id="cf-popup-title" class="mt-3 text-h3">{{ $p['title'] }}</h2>
            @if (filled($p['text'] ?? null))<p class="mt-2 text-ink-muted">{{ $p['text'] }}</p>@endif
            <ol class="mt-5 grid grid-cols-4 gap-2 text-center text-xs text-ink-muted" aria-label="How it works">
                @foreach (['Interest', 'Experience', 'Goal', 'Format'] as $i => $step)
                    <li class="rounded-lg bg-surface px-1 py-2"><span class="block font-display text-base font-bold text-navy-700">{{ $i + 1 }}</span>{{ $step }}</li>
                @endforeach
            </ol>
            <div class="mt-6 flex flex-col gap-2.5 sm:flex-row sm:items-center">
                <a href="/course-finder/" @click="track('click'); done()" class="btn btn-sun btn-lg flex-1">{{ $p['button'] }} <x-icon name="arrow-right" class="size-4" /></a>
                <button type="button" @click="close()" class="btn btn-ghost">Maybe later</button>
            </div>
        </div>
    </div>
</div>
