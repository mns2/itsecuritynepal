@extends('layouts.app')
@php
    $h = content('home');
    $contact = $site['contact'] ?? [];
    $hero = $h['hero'];
    $img = fn ($v) => media($v);
@endphp
@section('title', $h['seo']['title'])
@section('description', $h['seo']['description'])
@push('head')
    @if ($img($hero['main_image']))<link rel="preload" as="image" href="{{ $img($hero['main_image']) }}" fetchpriority="high">@endif
@endpush

@section('content')
{{-- HERO (search-led) --}}
<section class="bg-grid-light" aria-labelledby="hero-title">
    <div class="container-page grid gap-10 pt-10 pb-14 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:gap-12 lg:pt-16 lg:pb-[4.5rem]">
        <div>
            @if ($hero['badge'])<p class="badge badge-solid gap-1.5 py-1.5"><x-icon name="award" class="size-3.5" />{{ $hero['badge'] }}</p>@endif
            <h1 id="hero-title" class="mt-5 text-display">{{ $hero['title'] }} <span class="text-action">{{ $hero['title_highlight'] }}</span></h1>
            <p class="mt-4 max-w-[56ch] text-[1.0625rem] text-ink-muted">{{ $hero['lead'] }}</p>
            <form action="/course/" method="get" role="search" class="mt-7 flex max-w-[37.5rem] gap-2 rounded-lg border-[1.5px] border-line bg-white p-2 shadow-[0_16px_36px_-24px_rgb(0_50_117/0.45)] focus-within:border-action">
                <label class="flex flex-1 items-center gap-2.5 pl-2.5 text-ink-muted">
                    <x-icon name="search" class="size-5 shrink-0" /><span class="sr-only">Search courses</span>
                    <input name="q" type="search" class="h-11 min-w-0 flex-1 bg-transparent text-[0.9375rem] text-ink outline-none placeholder:text-ink-subtle" placeholder="{{ $hero['search_placeholder'] }}" autocomplete="off">
                </label>
                <button type="submit" class="btn btn-primary shrink-0"><span class="hidden sm:inline">{{ $hero['search_button'] }}</span><span class="sm:hidden">Search</span></button>
            </form>
            @if (! empty($hero['popular']))
                <div class="mt-3.5 flex flex-wrap items-center gap-2 text-[0.8125rem] text-ink-muted">
                    <span>Popular:</span>
                    @foreach ($hero['popular'] as $p)<a href="{{ $p['url'] }}" class="rounded-full border border-line bg-white px-3 py-1.5 font-semibold text-navy-700 no-underline hover:border-action hover:text-action">{{ $p['label'] }}</a>@endforeach
                </div>
            @endif
            <div class="mt-7 flex flex-wrap gap-2.5">
                <button type="button" class="btn btn-outline" @click="$dispatch('open-enquiry', {})"><x-icon name="message-circle" class="size-[18px]" /> {{ $hero['advisor_button'] }}</button>
                <a href="/course-finder/" class="btn btn-ghost"><x-icon name="compass" class="size-[18px]" /> {{ $hero['finder_link'] }}</a>
            </div>
        </div>
        <div class="relative grid min-h-[25rem] grid-cols-[1.2fr_1fr] grid-rows-2 gap-3">
            @foreach ([['main_image', 'row-span-2', 'eager'], ['second_image', '', 'lazy'], ['third_image', '', 'lazy']] as [$key, $cls, $loading])
                <div class="{{ $cls }} relative overflow-hidden rounded-[14px] bg-media">
                    @if ($src = $img($hero[$key]))
                        <img src="{{ $src }}" alt="{{ $hero[$key.'_alt'] ?? '' }}" class="absolute inset-0 size-full object-cover" width="{{ $cls ? 900 : 600 }}" height="{{ $cls ? 1000 : 500 }}" loading="{{ $loading }}" @if ($loading === 'eager') fetchpriority="high" @endif>
                    @endif
                </div>
            @endforeach
            @if ($hero['callout_title'])
                <a href="{{ $hero['callout_url'] ?: '#' }}" class="absolute -bottom-5 -left-2 flex max-w-[18rem] items-center gap-3 rounded-lg border border-line bg-white px-4 py-3.5 no-underline shadow-panel sm:-left-4">
                    <span class="grid size-[42px] shrink-0 place-items-center rounded-[10px] bg-sun-400 text-navy-700"><x-icon name="calendar-days" class="size-5" /></span>
                    <span><span class="block font-display text-sm font-bold text-navy-700">{{ $hero['callout_title'] }}</span><span class="block text-[0.78125rem] text-ink-muted">{{ $hero['callout_text'] }}</span></span>
                </a>
            @endif
        </div>
    </div>
</section>

{{-- KEY FACTS --}}
@php($facts = collect($site['trust'] ?? [])->reject(fn ($t) => ! empty($t['verify']) && empty($t['verified']))->values()->push($h['extra_fact'])->filter(fn ($t) => ! empty($t['value'])))
<section aria-label="Key facts" class="border-y border-line bg-white">
    <ul class="container-page grid grid-cols-2 lg:grid-cols-4">
        @foreach ($facts->take(4) as $t)
            <li @class(['flex items-center gap-3.5 border-line px-2 py-5 sm:px-4', 'border-l' => $loop->index % 2 === 1, 'lg:border-l' => $loop->index > 0])>
                <span class="ic-tile"><x-icon :name="$t['icon'] ?? 'award'" class="size-5" /></span>
                <div>
                    <p class="font-display text-[1.625rem] leading-none font-extrabold tracking-tight text-navy-700">{{ $t['value'] }}</p>
                    <p class="mt-1 text-[0.8125rem] text-ink-muted">{{ $t['label'] }}</p>
                </div>
            </li>
        @endforeach
    </ul>
</section>

{{-- COURSES BY TRACK --}}
@php($shown = max(4, (int) $h['tracks']['courses_shown']))
<section class="section" aria-labelledby="tracks-title" x-data="listFilter('[data-home-course]', {{ $shown }})">
    <div class="container-page">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <x-section-heading :eyebrow="$h['tracks']['eyebrow']" :title="$h['tracks']['title']" :intro="$h['tracks']['intro']" id="tracks-title" />
            <a href="/course/" class="link-arrow">View all {{ $courses->count() }} courses <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6" role="group" aria-label="Filter by track">
            @foreach ($homeTracks as $cat)
                <button type="button" class="track-tile" :aria-pressed="active === '{{ $cat->key }}'" @click="set('{{ $cat->key }}')">
                    <span class="ic"><x-icon :name="$cat->icon" class="size-5" /></span><b>{{ $cat->name }}</b><small>{{ $cat->published_courses_count }} courses</small>
                </button>
            @endforeach
        </div>
        <div class="no-scrollbar mt-9 mb-5 flex gap-2 overflow-x-auto pb-1" role="group" aria-label="Filter courses">
            <button type="button" class="chip shrink-0" :aria-pressed="active === 'all'" @click="active = 'all'">All</button>
            @foreach ($categories as $cat)<button type="button" class="chip shrink-0" :aria-pressed="active === '{{ $cat->key }}'" @click="active = '{{ $cat->key }}'">{{ $cat->short_name }}</button>@endforeach
        </div>
        <p class="sr-only" aria-live="polite" x-text="count + ' of ' + total + ' courses shown'"></p>
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($courses as $c)
                <li data-home-course data-category="{{ $c->category->key }}" @if ($loop->index >= $shown) hidden @endif><x-course-card :course="$c" /></li>
            @endforeach
        </ul>
        <div class="mt-8 flex justify-center">
            <a :href="active === 'all' ? '/course/' : '/course/?category=' + active" href="/course/" class="btn btn-outline">
                <span x-text="total > count ? 'See all ' + total + ' courses' : 'Browse the full catalogue'">Browse the full catalogue</span> <x-icon name="arrow-right" class="size-[18px]" />
            </a>
        </div>
    </div>
</section>

{{-- TRAINING FOR: INDIVIDUAL / GROUP / CORPORATE BATCH --}}
<section class="section bg-surface" aria-labelledby="who-title">
    <div class="container-page">
        <x-section-heading :eyebrow="$h['audiences']['eyebrow']" :title="$h['audiences']['title']" :intro="$h['audiences']['intro']" id="who-title" />
        <ul class="mt-8 grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($site['audiences'] ?? [] as $a)
                @php($photo = $img($h['audiences'][($a['id'] ?? '').'_image'] ?? null))
                <li class="flex flex-col overflow-hidden rounded-xl border border-line bg-white">
                    @if ($photo)
                        <div class="relative aspect-[16/7] overflow-hidden"><img src="{{ $photo }}" alt="" class="absolute inset-0 size-full object-cover" loading="lazy" width="900" height="394"></div>
                    @endif
                    <div class="flex flex-1 flex-col gap-5 p-6 sm:p-7">
                        <span @class(['grid size-14 shrink-0 place-items-center rounded-xl', 'bg-navy-700 text-sun-400' => $loop->first, 'bg-sun-400 text-navy-700' => ! $loop->first])><x-icon :name="$a['icon']" class="size-7" /></span>
                        <div class="flex flex-1 flex-col">
                            <h3 class="text-h3">{{ $a['title'] }}</h3>
                            <p class="mt-1.5 text-ink-muted">{{ $a['body'] }}</p>
                            <ul class="mt-4 grid gap-2 text-[0.9375rem]">@foreach ($a['points'] ?? [] as $pt)<li class="flex gap-2.5"><x-icon name="check" class="mt-0.5 size-[18px] shrink-0 text-success-600" />{{ $pt }}</li>@endforeach</ul>
                            <div class="mt-6 flex flex-wrap gap-2.5">
                                <a href="{{ $a['url'] }}" @class(['btn', 'btn-primary' => $loop->first, 'btn-sun' => ! $loop->first])>{{ $a['cta'] }}</a>
                                <button type="button" class="btn btn-outline" @click="$dispatch('open-enquiry', { audience: '{{ \App\Models\Enquiry::AUDIENCES[$a['id'] ?? ''] ?? 'Individual' }}' })">Enquire</button>
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- FEATURED --}}
@php($f = $h['featured'])
@if ($fw = $featured[$f['main_course']] ?? null)
<section class="section" aria-labelledby="feat-title">
    <div class="container-page">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <x-section-heading :eyebrow="$f['eyebrow']" :title="$f['title']" id="feat-title" />
            <button type="button" class="link-arrow" @click="$dispatch('open-enquiry', {})">Ask about the next batch <x-icon name="arrow-right" class="size-4" /></button>
        </div>
        <div class="grid gap-4 lg:grid-cols-[1.3fr_1fr] lg:grid-rows-2">
            <article class="card card-interactive group relative grid overflow-hidden rounded-xl lg:row-span-2">
                <div class="relative min-h-[16rem] overflow-hidden bg-media">@if ($src = $img($f['main_image']))<img src="{{ $src }}" alt="" class="absolute inset-0 size-full object-cover" loading="lazy" width="900" height="500">@endif</div>
                <div class="grid content-start gap-2.5 p-6">
                    @if ($f['main_badge'])<span class="badge w-fit bg-sun-100 text-sun-700"><x-icon name="award" class="size-3.5" />{{ $f['main_badge'] }}</span>@endif
                    <h3 class="text-[1.3125rem] leading-tight font-extrabold"><a href="{{ $fw->url() }}" class="stretched-link text-navy-700 no-underline group-hover:text-action">{{ $fw->code }} · {{ $fw->name }}</a></h3>
                    <p class="text-[0.90625rem] text-ink-muted">{{ $fw->summary }}</p>
                    <ul class="flex flex-wrap gap-2">
                        @if ($fw->certification)<li class="inline-flex items-center gap-1.5 rounded-md bg-surface px-2.5 py-1.5 text-[0.8125rem] font-semibold text-navy-700"><x-icon name="badge-check" class="size-4 text-action" />{{ $fw->certification }}</li>@endif
                        <li class="inline-flex items-center gap-1.5 rounded-md bg-surface px-2.5 py-1.5 text-[0.8125rem] font-semibold text-navy-700"><x-icon name="monitor-play" class="size-4 text-action" />{{ implode(' · ', $fw->modes ?? []) }}</li>
                    </ul>
                    <div class="relative z-10 mt-1 flex flex-wrap gap-2.5"><a href="{{ $fw->url() }}" class="btn btn-primary">View course</a><button type="button" class="btn btn-outline" @click="$dispatch('open-enquiry', { course: '{{ $fw->slug }}' })">Enquire</button></div>
                </div>
            </article>
            @foreach ($f['cards'] ?? [] as $card)
                @if ($c = $featured[$card['course'] ?? ''] ?? null)
                    <article class="card card-interactive group relative grid overflow-hidden rounded-xl sm:grid-cols-[12.5rem_1fr]">
                        <div class="relative min-h-[10.5rem] overflow-hidden bg-media">@if ($src = $img($card['image'] ?? null))<img src="{{ $src }}" alt="" class="absolute inset-0 size-full object-cover" loading="lazy" width="400" height="300">@endif</div>
                        <div class="grid content-start gap-2.5 p-5">
                            @if (! empty($card['badge']))<span class="badge badge-navy w-fit">{{ $card['badge'] }}</span>@endif
                            <h3 class="text-[1.125rem] leading-tight font-extrabold"><a href="{{ $c->url() }}" class="stretched-link text-navy-700 no-underline group-hover:text-action">{{ $c->name }}</a></h3>
                            <ul class="flex flex-wrap gap-2">
                                @if ($c->duration)<li class="inline-flex items-center gap-1.5 rounded-md bg-surface px-2.5 py-1.5 text-[0.8125rem] font-semibold text-navy-700"><x-icon name="calendar-days" class="size-4 text-action" />{{ $c->duration }}</li>@endif
                                @if (! empty($card['extra']))<li class="inline-flex items-center gap-1.5 rounded-md bg-surface px-2.5 py-1.5 text-[0.8125rem] font-semibold text-navy-700"><x-icon name="flask-conical" class="size-4 text-action" />{{ $card['extra'] }}</li>@endif
                            </ul>
                            <span class="link-arrow text-sm" aria-hidden="true">View course →</span>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- HOW WE TRAIN --}}
@php($j = $h['journey'])
<section class="section bg-surface" aria-labelledby="journey-title">
    <div class="container-page grid gap-9 lg:grid-cols-2 lg:items-center lg:gap-14">
        <div class="relative min-h-[18rem] overflow-hidden rounded-xl bg-media lg:min-h-[26rem]">
            @if ($src = $img($j['image']))<img src="{{ $src }}" alt="{{ $j['image_alt'] }}" class="absolute inset-0 size-full object-cover" loading="lazy" width="900" height="900">@endif
        </div>
        <div>
            <x-section-heading :eyebrow="$j['eyebrow']" :title="$j['title']" id="journey-title" />
            <ul class="mt-4 mb-6 flex flex-wrap gap-2">
                @foreach ($j['pillars'] ?? [] as $p)<li class="inline-flex items-center gap-1.5 rounded-md bg-sun-100 px-3 py-2 font-display text-[0.84375rem] font-bold text-navy-700"><x-icon :name="$p['icon'] ?: 'check'" class="size-4" />{{ $p['label'] }}</li>@endforeach
            </ul>
            <ol class="grid">
                @foreach ($j['steps'] ?? [] as $s)
                    <li class="grid grid-cols-[2.75rem_1fr] gap-x-4 gap-y-1 border-t border-line py-4">
                        <span @class(['row-span-2 grid size-11 place-items-center rounded-full border-2 font-display text-[0.9375rem] font-extrabold', 'border-sun-400 bg-sun-400 text-navy-700' => $loop->last, 'border-action text-action' => ! $loop->last])>{{ $loop->iteration }}</span>
                        <p class="font-display text-[1.0625rem] font-bold text-navy-700">{{ $s['title'] }}</p>
                        <p class="text-[0.90625rem] text-ink-muted">{{ $s['text'] }}</p>
                    </li>
                @endforeach
            </ol>
            <a href="/mode-of-training/" class="link-arrow mt-3">See all training methods <x-icon name="arrow-right" class="size-4" /></a>
        </div>
    </div>
</section>

{{-- LEARNING PATHS --}}
@if ($paths->isNotEmpty())
<section class="section" aria-labelledby="paths-title" x-data="{ tab: '{{ $paths->first()->key }}' }">
    <div class="container-page">
        <x-section-heading :eyebrow="$h['paths']['eyebrow']" :title="$h['paths']['title']" :intro="$h['paths']['intro']" id="paths-title" />
        <div class="no-scrollbar mt-7 flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Career paths">
            @foreach ($paths as $p)
                <button type="button" role="tab" id="tab-{{ $p->key }}" aria-controls="panel-{{ $p->key }}" :aria-selected="tab === '{{ $p->key }}'" :aria-pressed="tab === '{{ $p->key }}'" :tabindex="tab === '{{ $p->key }}' ? 0 : -1" @click="tab = '{{ $p->key }}'"
                    @keydown.right.prevent="$el.nextElementSibling?.click(); $el.nextElementSibling?.focus()" @keydown.left.prevent="$el.previousElementSibling?.click(); $el.previousElementSibling?.focus()"
                    class="chip shrink-0"><x-icon :name="$p->icon" class="size-4" />{{ $p->name }}</button>
            @endforeach
        </div>
        @foreach ($paths as $p)
            @php($steps = $p->resolvedSteps())
            <div role="tabpanel" id="panel-{{ $p->key }}" aria-labelledby="tab-{{ $p->key }}" x-show="tab === '{{ $p->key }}'" @unless ($loop->first) x-cloak @endunless class="mt-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-ink-muted">Goal: <strong class="text-navy-700">{{ $p->goal }}</strong></p>
                    @if ($p->slug)<a href="{{ $p->url() }}" class="link-arrow">Career pathway: {{ $p->name }} <x-icon name="arrow-right" class="size-4" /></a>@endif
                </div>
                <ol class="mt-5 grid gap-3 md:grid-cols-{{ max(1, min(5, $steps->count())) }}">
                    @foreach ($steps as $s)
                        <li>
                            <a href="{{ $s['course']->url() }}" class="track-tile h-full content-start">
                                <span class="flex items-center gap-2.5"><span @class(['grid size-8 place-items-center rounded-full font-display text-sm font-extrabold', 'bg-sun-400 text-navy-700' => $loop->last, 'bg-navy-700 text-white' => ! $loop->last])>{{ $loop->iteration }}</span><small>{{ $s['note'] }}</small></span>
                                <span class="font-display text-sm font-extrabold text-action">{{ $s['course']->code }}</span>
                                <b>{{ $s['course']->name }}</b>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- UPCOMING BATCHES --}}
@if ($batches->isNotEmpty())
<section class="section bg-surface" aria-labelledby="batches-title">
    <div class="container-page">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <x-section-heading :eyebrow="$h['batches']['eyebrow']" :title="$h['batches']['title']" :intro="$h['batches']['intro']" id="batches-title" />
            <a href="/batches/" class="link-arrow">Full training calendar <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        <x-batch-list :batches="$batches" />
    </div>
</section>
@endif

{{-- TRUST --}}
@php($tr = $h['trust'])
<section class="section" aria-labelledby="trust-title">
    <div class="container-page">
        <x-section-heading :eyebrow="$tr['eyebrow']" :title="$tr['title']" id="trust-title" />
        <ul class="mt-7 grid grid-cols-2 gap-3 md:grid-cols-6">
            @foreach ($tr['vendors'] ?? [] as $v)<li class="grid h-[4.5rem] place-items-center rounded-[10px] border border-line bg-white px-2 text-center font-display text-[1.0625rem] font-extrabold tracking-tight text-navy-700">{{ $v }}</li>@endforeach
        </ul>
        <div class="mt-6 grid gap-4 lg:grid-cols-[1.2fr_1fr]">
            <div class="on-dark flex items-center gap-4 rounded-lg bg-navy-700 p-6 text-white">
                <span class="grid size-[3.375rem] shrink-0 place-items-center rounded-lg bg-sun-400 text-navy-700"><x-icon name="award" class="size-6" /></span>
                <div><p class="font-display text-lg leading-snug font-extrabold">{{ $tr['partner_title'] }}</p><p class="mt-1 text-sm text-white/80">{{ $tr['partner_text'] }}</p></div>
            </div>
            @if ($testimonials->isNotEmpty())
                @php($t = $testimonials->first())
                <figure class="flex flex-col justify-center gap-3 rounded-lg border border-line bg-white p-5">
                    <blockquote class="text-[0.9375rem] text-navy-700">“{{ $t->quote }}”</blockquote>
                    <figcaption class="text-sm text-ink-muted">{{ $t->name }}{{ $t->course ? ' · '.$t->course->name : '' }}</figcaption>
                </figure>
            @else
                <div class="flex flex-col justify-center gap-3 rounded-lg border border-line bg-white p-5">
                    <p class="text-sm text-ink">{{ $tr['reviews_text'] }}</p>
                    <a href="{{ $contact['mapUrl'] ?? '#' }}" class="link-arrow text-sm" rel="noopener" target="_blank"><x-icon name="star" class="size-4" /> Read Google reviews<span class="sr-only"> (opens in a new tab)</span></a>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- GOOGLE REVIEWS (only when configured in Site settings) --}}
@if (\App\Support\GoogleReviews::settings()['show_home'])<x-google-reviews />@endif

{{-- WHY CHOOSE US --}}
<section class="section bg-surface" aria-labelledby="why-title">
    <div class="container-page">
        <x-section-heading :eyebrow="$h['why']['eyebrow']" :title="$h['why']['title']" id="why-title" />
        <ul class="mt-7 grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($h['why']['items'] ?? [] as $w)
                <li class="reveal flex gap-3.5 rounded-lg border border-line bg-white p-5">
                    <span class="ic-tile"><x-icon :name="$w['icon'] ?: 'check'" class="size-5" /></span>
                    <div><h3 class="font-display text-[1.03125rem] font-bold text-navy-700">{{ $w['title'] }}</h3><p class="mt-1 text-sm text-ink-muted">{{ $w['text'] }}</p></div>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- FAQ --}}
@if ($faqs->isNotEmpty())
<section class="section" aria-labelledby="faq-title">
    <div class="container-page grid gap-10 lg:grid-cols-[1fr_1.6fr] lg:gap-16">
        <div>
            <x-section-heading :eyebrow="$h['faq']['eyebrow']" :title="$h['faq']['title']" :intro="$h['faq']['intro']" id="faq-title" />
            <a href="/faq/" class="link-arrow mt-6">See all FAQs <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        <x-faq-list :faqs="$faqs" />
    </div>
</section>
@endif

{{-- CTA WITH INQUIRY FORM --}}
@php($cta = $h['cta'])
<section class="section pt-0" aria-labelledby="cta-title">
    <div class="container-page">
        <div class="on-dark relative grid gap-8 overflow-hidden rounded-xl bg-grid-navy p-6 text-white sm:p-10 lg:grid-cols-2 lg:items-center lg:gap-12 lg:p-12">
            <div class="relative">
                <h2 id="cta-title" class="text-h2 text-white">{{ $cta['title'] }}</h2>
                <p class="mt-3 text-lead text-navy-100">{{ $cta['text'] }}</p>
                <ul class="mt-6 grid gap-2.5">@foreach ($cta['checklist'] ?? [] as $line)<li class="flex items-center gap-2.5"><x-icon name="check" class="size-5 text-sun-400" />{{ $line }}</li>@endforeach</ul>
                <a href="/course/" class="btn btn-ghost-light mt-7">Browse courses</a>
            </div>
            <div class="relative rounded-lg bg-white p-6 text-ink shadow-panel sm:p-7">
                <h3 class="text-h3">{{ $cta['form_title'] }}</h3>
                <p class="mt-1 mb-5 text-sm text-ink-muted">{{ $cta['form_intro'] }}</p>
                <x-enquiry-form id="home-enquiry" :compact="true" />
            </div>
        </div>
    </div>
</section>
@endsection
