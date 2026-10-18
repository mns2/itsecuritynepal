@extends('layouts.app')
@php($a = content('about'))
@section('schema_type', 'AboutPage')
@section('title', $a['seo']['title'])
@section('description', $a['seo']['description'])

@section('content')
<section class="bg-grid-light">
    <div class="container-page pt-8 pb-14 lg:pb-20">
        <x-breadcrumbs :items="[['name' => 'About us']]" />
        <div class="mt-8 grid gap-10 lg:grid-cols-[1.3fr_1fr] lg:items-end">
            <div>
                <p class="text-eyebrow text-action">{{ $a['eyebrow'] }}</p>
                <h1 class="mt-3 text-h1">{{ $a['title'] }}</h1>
            </div>
            <p class="text-lead text-ink-muted">{{ $a['lead'] }}</p>
        </div>
    </div>
</section>

@php($m = $a['mission'])
<section class="section" aria-labelledby="mission">
    <div class="container-page grid gap-12 lg:grid-cols-2">
        <div>
            <x-section-heading :eyebrow="$m['eyebrow']" :title="$m['title']" id="mission" />
            <div class="mt-6 grid gap-4 text-[1.0625rem] leading-relaxed text-ink">@foreach ($m['paragraphs'] ?? [] as $p)<p>{{ $p }}</p>@endforeach</div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($m['cards'] ?? [] as $c)
                <div class="rounded-lg border border-line p-6"><span class="flex size-11 items-center justify-center rounded-md bg-navy-700 text-sun-300"><x-icon :name="$c['icon'] ?: 'check'" class="size-5" /></span><h3 class="mt-4 text-lg font-semibold">{{ $c['title'] }}</h3><p class="mt-2 text-[0.9375rem] text-ink-muted">{{ $c['text'] }}</p></div>
            @endforeach
        </div>
    </div>
</section>

@php($stats = collect($site['trust'] ?? [])->reject(fn ($t) => ! empty($t['verify']) && empty($t['verified'])))
@if ($stats->isNotEmpty())
<section class="on-dark section bg-grid-navy text-white" aria-labelledby="achievements">
    <div class="container-page">
        <x-section-heading eyebrow="Track record" :title="$a['stats_title']" :dark="true" id="achievements" />
        <ul class="mt-10 grid grid-cols-2 gap-5 lg:grid-cols-4">
            @foreach ($stats as $s)<li class="rounded-lg border border-white/15 bg-white/[0.05] p-6"><p class="font-display text-4xl font-bold text-sun-300">{{ $s['value'] }}</p><p class="mt-2 text-navy-100">{{ $s['label'] }}</p></li>@endforeach
        </ul>
    </div>
</section>
@endif

@php($cf = $a['certifications'])
<section class="section" aria-labelledby="partners">
    <div class="container-page">
        <x-section-heading :eyebrow="$cf['eyebrow']" :title="$cf['title']" :intro="$cf['intro']" id="partners" />
        <ul class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ($cf['vendors'] ?? [] as $v)
                <li class="flex flex-col items-center justify-center rounded-lg border border-line px-4 py-6 text-center"><span class="font-display text-lg font-bold text-navy-700">{{ $v['name'] }}</span><span class="mt-1 text-xs text-ink-muted">{{ $v['text'] }}</span></li>
            @endforeach
        </ul>
        <p class="mt-4 text-xs text-ink-muted">Trademarks belong to their respective owners.</p>
    </div>
</section>

@php($env = $a['environment'])
<section class="section bg-surface" aria-labelledby="environment">
    <div class="container-page grid gap-12 lg:grid-cols-2 lg:items-center">
        <div>
            <x-section-heading :eyebrow="$env['eyebrow']" :title="$env['title']" :intro="$env['intro']" id="environment" />
            <div class="mt-8 flex flex-wrap gap-3"><a href="/instructors/" class="btn btn-navy">Meet the instructors</a><a href="/mode-of-training/" class="btn btn-outline">Training methods</a></div>
        </div>
        <div class="relative aspect-[4/3] overflow-hidden rounded-xl bg-media">
            @if ($src = media($env['image']))<img src="{{ $src }}" alt="{{ $env['image_alt'] }}" class="absolute inset-0 size-full object-cover" loading="lazy" width="900" height="675">@endif
        </div>
    </div>
</section>

<x-cta-band :title="$a['cta_title']" :text="($site['contact']['address'] ?? '').' · '.($site['contact']['hoursShort'] ?? '')">
    <a href="/contact/" class="btn btn-primary btn-lg">Contact us</a>
    <a href="{{ $site['contact']['mapUrl'] ?? '#' }}" class="btn btn-ghost-light btn-lg" rel="noopener" target="_blank">Get directions</a>
</x-cta-band>
@if (\App\Support\GoogleReviews::settings()['show_about'])<x-google-reviews class="section bg-surface" />@endif
@endsection
