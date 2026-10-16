@extends('layouts.app')
@php($c = content('corporate'))
@section('title', $c['seo']['title'])
@section('description', $c['seo']['description'])

@section('content')
<section class="relative overflow-hidden bg-grid-light">
    <div class="container-page relative grid gap-12 pt-8 pb-16 lg:grid-cols-[1.2fr_1fr] lg:items-center lg:pb-24">
        <div>
            <x-breadcrumbs :items="[['name' => 'Corporate training']]" />
            <p class="mt-10 text-eyebrow text-action">{{ $c['eyebrow'] }}</p>
            <h1 class="mt-3 text-h1">{{ $c['title'] }}</h1>
            <p class="mt-5 max-w-2xl text-lead text-ink-muted">{{ $c['lead'] }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#proposal" class="btn btn-primary btn-lg">Request a proposal</a>
                @if (! empty($site['contact']['phonePrimary']))<a href="tel:{{ $site['contact']['phonePrimary']['tel'] }}" class="btn btn-outline btn-lg"><x-icon name="phone" class="size-5" /> {{ $site['contact']['phonePrimary']['label'] }}</a>@endif
            </div>
        </div>
        <div class="relative aspect-[4/3] overflow-hidden rounded-xl bg-media">
            @if ($src = media($c['image']))<img src="{{ $src }}" alt="{{ $c['image_alt'] }}" class="absolute inset-0 size-full object-cover" width="900" height="675" fetchpriority="high">@endif
        </div>
    </div>
</section>

<section class="section-tight" aria-label="Highlights">
    <div class="container-page">
        <ul class="grid gap-4 md:grid-cols-3">
            @foreach ($c['highlights'] ?? [] as $hl)
                <li class="flex gap-4 rounded-lg border border-line bg-white p-5"><span class="flex size-10 shrink-0 items-center justify-center rounded-md bg-sun-400 text-navy-900"><x-icon :name="$hl['icon'] ?: 'check'" class="size-5" /></span><div><p class="font-semibold text-navy-700">{{ $hl['title'] }}</p><p class="text-sm text-ink-muted">{{ $hl['text'] }}</p></div></li>
            @endforeach
        </ul>
    </div>
</section>

<section class="section" aria-labelledby="needs">
    <div class="container-page">
        <x-section-heading eyebrow="Why invest in training" :title="$c['benefits_title']" align="center" id="needs" />
        <ul class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($c['benefits'] ?? [] as $b)
                <li class="reveal rounded-lg border border-line p-6"><span class="flex size-11 items-center justify-center rounded-md bg-navy-700 text-sun-300"><x-icon :name="$b['icon'] ?: 'check'" class="size-5" /></span><h3 class="mt-4 text-lg font-semibold">{{ $b['title'] }}</h3><p class="mt-2 text-[0.9375rem] text-ink-muted">{{ $b['text'] }}</p></li>
            @endforeach
        </ul>
    </div>
</section>

<section class="section bg-surface" aria-labelledby="sector">
    <div class="container-page grid gap-12 lg:grid-cols-2 lg:items-center">
        <x-section-heading eyebrow="Sector focus" :title="$c['sector_title']" :intro="$c['sector_intro']" id="sector" />
        <ul class="grid gap-3">
            @foreach ($c['sector_points'] ?? [] as $t)
                <li class="flex items-center gap-4 rounded-lg border border-line bg-white p-4"><span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-sun-100 text-sun-700"><x-icon name="check" class="size-5" /></span><span class="font-semibold text-navy-700">{{ $t }}</span></li>
            @endforeach
        </ul>
    </div>
</section>

<section class="section" aria-labelledby="areas">
    <div class="container-page">
        <x-section-heading eyebrow="Training areas" title="Programmes we can tailor for your team" id="areas" />
        <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($categories as $cat)<li><a href="{{ $cat->url() }}" class="card card-interactive flex h-full items-center gap-4 p-5 no-underline"><span class="flex size-11 shrink-0 items-center justify-center rounded-md bg-surface text-action"><x-icon :name="$cat->icon" class="size-5" /></span><span class="font-semibold text-navy-700">{{ $cat->name }}</span></a></li>@endforeach
        </ul>
    </div>
</section>

<section class="on-dark section bg-grid-navy text-white" aria-labelledby="approach">
    <div class="container-page">
        <x-section-heading eyebrow="Our approach" :title="$c['approach_title']" :dark="true" id="approach" />
        <ol class="mt-12 grid gap-6 md:grid-cols-4">
            @foreach ($c['approach_steps'] ?? [] as $s)
                <li class="relative rounded-lg border border-white/15 bg-white/[0.05] p-6"><span class="font-display text-sm font-bold text-sun-300">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3 class="mt-3 text-lg font-semibold text-white">{{ $s['title'] }}</h3><p class="mt-2 text-[0.9375rem] text-navy-100">{{ $s['text'] }}</p></li>
            @endforeach
        </ol>
    </div>
</section>

<section id="proposal" class="section scroll-mt-40" aria-labelledby="proposal-title">
    <div class="container-page grid gap-12 lg:grid-cols-[1fr_1.3fr]">
        <div>
            <x-section-heading eyebrow="Request a proposal" :title="$c['proposal_title']" :intro="$c['proposal_intro']" id="proposal-title" />
            <ul class="mt-8 grid gap-4 text-[0.9375rem]">
                @if (! empty($site['contact']['phonePrimary']))<li class="flex gap-3"><x-icon name="phone" class="size-5 text-action" /><a href="tel:{{ $site['contact']['phonePrimary']['tel'] }}">{{ $site['contact']['phonePrimary']['label'] }}</a></li>@endif
                @if (! empty($site['contact']['email']))<li class="flex gap-3"><x-icon name="mail" class="size-5 text-action" /><a href="mailto:{{ $site['contact']['email'] }}?subject=Corporate%20training%20enquiry">{{ $site['contact']['email'] }}</a></li>@endif
            </ul>
        </div>
        <div class="card p-6 sm:p-8"><x-enquiry-form id="corp-enquiry" :corporate="true" title="Corporate training proposal request" /></div>
    </div>
</section>
@endsection
