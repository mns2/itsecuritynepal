@extends('layouts.app')
@php($g = content('group'))
@section('title', $g['seo']['title'])
@section('description', $g['seo']['description'])

@section('content')
<section class="bg-grid-light">
    <div class="container-page grid gap-10 pt-8 pb-14 lg:grid-cols-[1.25fr_1fr] lg:items-center lg:pb-20">
        <div>
            <x-breadcrumbs :items="[['name' => 'Group training']]" />
            <p class="badge badge-solid mt-8 gap-1.5 py-1.5"><x-icon name="users" class="size-3.5" />{{ $g['badge'] }}</p>
            <h1 class="mt-4 text-h1">{{ $g['title'] }}</h1>
            <p class="mt-4 max-w-2xl text-lead text-ink-muted">{{ $g['lead'] }}</p>
            <div class="mt-7 flex flex-wrap gap-2.5">
                <a href="#plan" class="btn btn-primary btn-lg">Plan your group training</a>
                @if (! empty($site['contact']['phonePrimary']))<a href="tel:{{ $site['contact']['phonePrimary']['tel'] }}" class="btn btn-outline btn-lg"><x-icon name="phone" class="size-5" /> {{ $site['contact']['phonePrimary']['label'] }}</a>@endif
            </div>
        </div>
        <div class="relative aspect-[4/3] overflow-hidden rounded-xl bg-media">
            @if ($src = media($g['image']))<img src="{{ $src }}" alt="{{ $g['image_alt'] }}" class="absolute inset-0 size-full object-cover" width="900" height="675" fetchpriority="high">@endif
        </div>
    </div>
</section>

<section class="section-tight" aria-label="Why train as a group">
    <div class="container-page">
        <ul class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($g['features'] ?? [] as $f)
                <li class="flex gap-4 rounded-lg border border-line bg-white p-5"><span class="ic-tile"><x-icon :name="$f['icon'] ?: 'check'" class="size-5" /></span><div><p class="font-display font-bold text-navy-700">{{ $f['title'] }}</p><p class="text-sm text-ink-muted">{{ $f['text'] }}</p></div></li>
            @endforeach
        </ul>
    </div>
</section>

<section class="section" aria-labelledby="g-compare">
    <div class="container-page">
        <x-section-heading eyebrow="Individual or group?" :title="$g['compare_title']" align="center" id="g-compare" />
        <div class="mx-auto mt-8 max-w-4xl overflow-x-auto rounded-lg border border-line">
            <table class="w-full min-w-[34rem] text-[0.9375rem]">
                <thead class="bg-surface text-left"><tr><th class="px-5 py-3.5"><span class="sr-only">Feature</span></th><th class="px-5 py-3.5 font-display text-navy-700">Individual</th><th class="px-5 py-3.5 font-display text-navy-700">Group</th></tr></thead>
                <tbody>
                    @foreach ($g['compare_rows'] ?? [] as $r)
                        <tr class="border-t border-line"><th scope="row" class="px-5 py-3.5 text-left font-semibold text-navy-700">{{ $r['label'] }}</th><td class="px-5 py-3.5 text-ink-muted">{{ $r['individual'] }}</td><td class="px-5 py-3.5 text-ink-muted">{{ $r['group'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

@if ($popular->isNotEmpty())
<section class="section bg-surface" aria-labelledby="g-popular">
    <div class="container-page">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <x-section-heading eyebrow="Popular for groups" :title="$g['popular_title']" id="g-popular" />
            <a href="/course/" class="link-arrow">Any course can run as a group <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">@foreach ($popular as $c)<li><x-course-card :course="$c" /></li>@endforeach</ul>
    </div>
</section>
@endif

<section id="plan" class="section scroll-mt-40" aria-labelledby="g-plan">
    <div class="container-page grid gap-12 lg:grid-cols-[1fr_1.2fr]">
        <div>
            <x-section-heading eyebrow="Plan your batch" :title="$g['plan_title']" :intro="$g['plan_intro']" id="g-plan" />
            <div class="mt-8"><x-faq-list :faqs="collect($g['faqs'] ?? [])->map(fn ($f) => ['q' => $f['question'] ?? '', 'a' => $f['answer'] ?? ''])" /></div>
        </div>
        <div class="card p-6 sm:p-8">
            <h2 class="text-h3">Group training enquiry</h2>
            <p class="mt-1 mb-6 text-sm text-ink-muted">{{ content('global.enquiry_success') }}</p>
            <x-enquiry-form id="group-enquiry" audience="Group" />
        </div>
    </div>
</section>
@endsection
