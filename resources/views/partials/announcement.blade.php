{{-- Offer bar (Theme E). Edited in Admin → Site settings → Announcement. --}}
@php($a = $site['announcement'] ?? [])
@if (($a['enabled'] ?? false) && ! empty($a['text']))
    <div x-data="{ show: true }" x-show="show" class="bg-sun-400 text-navy-700">
        <div class="container-page flex min-h-10 items-center justify-center gap-3 py-2 text-center text-[0.84375rem] font-semibold">
            <p class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1.5">
                <span>{{ $a['text'] }}</span>
                @if (! empty($a['url']))
                    <a href="{{ $a['url'] }}" class="rounded-full bg-navy-700 px-3 py-1.5 text-xs font-semibold text-white no-underline hover:bg-action hover:text-white">{{ $a['linkText'] ?? 'Learn more' }}</a>
                @endif
            </p>
            <button type="button" class="-mr-2 ml-auto flex size-9 shrink-0 items-center justify-center rounded-md hover:bg-black/5" @click="show = false" aria-label="Dismiss announcement"><x-icon name="x" class="size-4" /></button>
        </div>
    </div>
@endif
