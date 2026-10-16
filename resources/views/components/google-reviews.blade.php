{{-- Real Google reviews (Admin → Settings → Site settings → Google reviews). Renders nothing until configured. --}}
@props(['class' => 'section'])
@php
    $g = \App\Support\GoogleReviews::get();
    $gs = \App\Support\GoogleReviews::settings();
@endphp
@if ($g && $g['count'] > 0)
<section {{ $attributes->class([$class]) }} aria-labelledby="google-reviews-title">
    <div class="container-page">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-eyebrow text-action">Google reviews</p>
                <h2 id="google-reviews-title" class="mt-2 text-h2">{{ $gs['title'] }}</h2>
                <p class="mt-3 flex flex-wrap items-center gap-2 text-ink-muted">
                    <span class="font-display text-2xl font-bold text-navy-700">{{ number_format($g['rating'], 1) }}</span>
                    <span class="flex text-sun-500" aria-hidden="true">
                        @for ($i = 1; $i <= 5; $i++)<x-icon name="star" @class(['size-5', 'fill-current' => $i <= round($g['rating'])]) />@endfor
                    </span>
                    <span class="sr-only">{{ number_format($g['rating'], 1) }} out of 5 stars,</span>
                    <span>{{ number_format($g['count']) }} reviews on Google</span>
                </p>
            </div>
            <div class="flex flex-wrap gap-2.5">
                @if ($g['url'])<a href="{{ $g['url'] }}" class="btn btn-outline" target="_blank" rel="noopener">See all on Google</a>@endif
                <a href="{{ $g['writeUrl'] }}" class="btn btn-sun" target="_blank" rel="noopener">Write a review</a>
            </div>
        </div>
        @if (count($g['reviews']))
            <ul class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach (array_slice($g['reviews'], 0, 6) as $r)
                    <li class="card flex flex-col p-6">
                        <div class="flex items-center gap-3">
                            @if ($r['photo'])
                                <img src="{{ $r['photo'] }}" alt="" class="size-10 rounded-full object-cover" loading="lazy" referrerpolicy="no-referrer" width="40" height="40">
                            @else
                                <span class="grid size-10 place-items-center rounded-full bg-navy-700 font-semibold text-white" aria-hidden="true">{{ mb_substr($r['author'], 0, 1) }}</span>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-navy-700">
                                    @if ($r['authorUrl'])<a href="{{ $r['authorUrl'] }}" class="no-underline hover:underline" target="_blank" rel="noopener nofollow">{{ $r['author'] }}</a>@else{{ $r['author'] }}@endif
                                </p>
                                <p class="flex items-center gap-1.5 text-xs text-ink-muted">
                                    <span class="flex text-sun-500" aria-hidden="true">@for ($i = 1; $i <= 5; $i++)<x-icon name="star" @class(['size-3.5', 'fill-current' => $i <= $r['rating']]) />@endfor</span>
                                    <span class="sr-only">{{ $r['rating'] }} out of 5 stars</span>
                                    @if ($r['when'])<span>· {{ $r['when'] }}</span>@endif
                                </p>
                            </div>
                        </div>
                        <blockquote x-data="{ more: false }" class="mt-4 flex-1 text-[0.9375rem] leading-relaxed text-ink">
                            <p :class="more ? '' : 'line-clamp-5'">{{ $r['text'] }}</p>
                            @if (mb_strlen($r['text']) > 260)<button type="button" class="mt-1 text-sm font-semibold text-action" @click="more = !more" x-text="more ? 'Show less' : 'Read more'">Read more</button>@endif
                        </blockquote>
                    </li>
                @endforeach
            </ul>
        @endif
        <p class="mt-5 text-xs text-ink-muted">Reviews from Google. Shown as written by reviewers; selected and ordered by Google.</p>
    </div>
</section>
@endif
