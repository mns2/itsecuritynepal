{{-- Countdown + offer code, shared by the pop-up and the landing page. --}}
@if ($campaign->show_countdown && $campaign->ends_at && $campaign->ends_at->isFuture())
    <div x-data="countdown(@js($campaign->ends_at->toIso8601String()))" x-show="left" x-cloak class="flex flex-wrap items-center gap-3" role="timer" aria-live="off">
        <span class="text-sm font-semibold text-navy-700">Offer ends in</span>
        <div class="flex gap-1.5 font-mono" aria-hidden="true">
            <template x-for="[n, label] in [[left?.d, 'days'], [left?.h, 'hrs'], [left?.m, 'min'], [left?.s, 'sec']]" :key="label">
                <span class="grid min-w-12 place-items-center rounded-lg bg-navy-700 px-2 py-1.5 text-white">
                    <span class="text-lg leading-none font-bold tabular-nums" x-text="String(n ?? 0).padStart(2, '0')"></span>
                    <span class="mt-0.5 text-[0.625rem] tracking-wide text-navy-100 uppercase" x-text="label"></span>
                </span>
            </template>
        </div>
        <span class="sr-only">Offer ends {{ $campaign->ends_at->format('j F Y, g:i A') }}</span>
    </div>
@endif
@if ($campaign->offer_code)
    <div x-data="copyCode(@js($campaign->offer_code))" class="flex items-center justify-between gap-3 rounded-lg border border-dashed border-sun-500 bg-sun-100 px-4 py-2.5">
        <span class="text-sm text-navy-700">Use code <strong class="font-mono text-base tracking-wider">{{ $campaign->offer_code }}</strong></span>
        <button type="button" @click="copy()" class="btn btn-outline btn-sm" x-text="copied ? 'Copied' : 'Copy'">Copy</button>
    </div>
@endif
