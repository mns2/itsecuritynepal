{{-- Marketing campaign pop-up (Admin → Leads → Campaigns). One campaign per page view; the script decides when and how often. --}}
@php
    $campaign = \App\Http\Controllers\CampaignController::forRequest(request());
@endphp
@if ($campaign)
@php
    $preview = \App\Http\Controllers\CampaignController::isPreview(request(), $campaign);
    $cfg = $campaign->toPopupArray() + ['preview' => $preview, 'token' => csrf_token()];
    $href = $campaign->ctaHref();
    $titleId = 'campaign-title-'.$campaign->id;
    $img = media($campaign->image);
    $mobileImg = media($campaign->mobile_image);
    $poster = $campaign->style === 'poster' && $img;
@endphp
<div x-data="sitePopup(@js($cfg))" x-show="open" x-cloak
     class="fixed inset-0 z-[65] flex items-end justify-center sm:items-center sm:p-6"
     role="dialog" aria-modal="true" aria-labelledby="{{ $titleId }}" @keydown.escape.window="open && close()">
    <div class="absolute inset-0 bg-navy-900/70" @click="close()" x-show="open" x-transition.opacity aria-hidden="true"></div>

    <div x-show="open" x-trap.noscroll.inert="open" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0 sm:translate-y-0 sm:scale-95"
         @class(['relative max-h-[92dvh] w-full overflow-y-auto rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl', 'sm:max-w-[34rem]' => $poster, 'sm:max-w-lg' => ! $poster])>
        <button type="button" @click="close()" class="absolute top-3 right-3 z-10 grid size-10 place-items-center rounded-full bg-white/95 text-navy-700 shadow-md hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-navy-700">
            <x-icon name="x" class="size-5" /><span class="sr-only">Close</span>
        </button>
        @if ($preview)<p class="bg-sun-400 px-4 py-1.5 text-center text-xs font-semibold text-navy-900">Preview (only you can see this; views are not counted)</p>@endif

        @if ($poster)
            <h2 id="{{ $titleId }}" class="sr-only">{{ $campaign->image_alt ?: $campaign->name }}</h2>
            @php($pictureTag = $href ? 'a' : 'button')
            <{{ $pictureTag }} @if ($href) href="{{ $href }}" @click="track('click')" @else type="button" @click="cta()" @endif class="block w-full cursor-pointer" aria-label="{{ $campaign->cta_label }}: {{ $campaign->image_alt ?: $campaign->name }}">
                <picture>
                    @if ($mobileImg)<source media="(max-width: 639px)" srcset="{{ $mobileImg }}">@endif
                    <img src="{{ $img }}" alt="{{ $campaign->image_alt }}" class="block h-auto w-full" decoding="async">
                </picture>
            </{{ $pictureTag }}>
        @else
            @if ($img)<img src="{{ $img }}" alt="{{ $campaign->image_alt }}" class="block aspect-[1200/630] w-full object-cover" decoding="async">@endif
            <div class="px-6 pt-6 sm:px-8 sm:pt-8">
                @if ($campaign->badge)<span class="badge bg-sun-400 text-navy-900">{{ $campaign->badge }}</span>@endif
                <h2 id="{{ $titleId }}" class="mt-3 text-h2">{{ $campaign->headline ?: $campaign->name }}</h2>
                @if ($campaign->text)<p class="mt-3 text-ink-muted">{{ $campaign->text }}</p>@endif
            </div>
        @endif

        <div class="flex flex-col gap-4 p-6 sm:px-8 sm:pb-8">
            @include('partials.campaign-extras', ['campaign' => $campaign, 'compact' => true])
            <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center">
                @if ($href)
                    <a href="{{ $href }}" @click="track('click')" class="btn btn-sun btn-lg flex-1">{{ $campaign->cta_label }}</a>
                @else
                    <button type="button" @click="cta()" class="btn btn-sun btn-lg flex-1">{{ $campaign->cta_label }}</button>
                @endif
                <button type="button" @click="close()" class="btn btn-ghost">No thanks</button>
            </div>
        </div>
    </div>
</div>
@endif
