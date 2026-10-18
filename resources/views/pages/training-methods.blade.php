@extends('layouts.app')
@section('title', 'Training Methods: Classroom, Live Online & One-on-One | IT Security Nepal')
@section('description', 'Classroom training in Kathmandu, live virtual classes, one-on-one sessions and flip classes at IT Security Nepal, for individuals, groups and corporate batches.')

@section('content')
<section class="bg-grid-light">
    <div class="container-page pt-8 pb-12 lg:pb-16">
        <x-breadcrumbs :items="[['name' => 'Training methods']]" />
        <div class="mt-8 max-w-3xl">
            <h1 class="text-h1">{{ content('training_methods.title') }}</h1>
            <p class="mt-4 text-lead text-ink-muted">{{ content('training_methods.lead') }}</p>
        </div>
    </div>
</section>

<section class="section-tight">
    <div class="container-page grid gap-6">
        @foreach ($methods as $m)
            <article class="reveal grid overflow-hidden rounded-xl border border-line lg:grid-cols-[18rem_1fr]" aria-labelledby="m-{{ $m->slug }}">
                <div @class(['on-dark flex flex-col justify-between gap-6 p-8 text-white', 'bg-navy-700' => $loop->even === false, 'bg-navy-800' => $loop->even])>
                    <span class="flex size-14 items-center justify-center rounded-lg bg-sun-400 text-navy-900"><x-icon :name="$m->icon" class="size-7" /></span>
                    <div><p class="text-eyebrow text-sun-300">Option {{ $loop->iteration }}</p><h2 id="m-{{ $m->slug }}" class="mt-2 text-h3 text-white">{{ $m->name }}</h2></div>
                </div>
                <div class="grid gap-6 p-6 sm:p-8 md:grid-cols-[1.2fr_1fr]">
                    <div>
                        <p class="text-[1.0625rem] leading-relaxed">{{ $m->body }}</p>
                        @if ($m->best_for)<p class="mt-4 text-[0.9375rem] text-ink-muted"><strong class="text-navy-700">Best for:</strong> {{ $m->best_for }}</p>@endif
                        <div class="mt-6 flex flex-wrap gap-3">
                            <a href="{{ $m->url() }}" class="btn btn-navy btn-sm">Learn more<span class="sr-only"> about {{ $m->name }}</span></a>
                            <button type="button" class="btn btn-outline btn-sm" @click="$dispatch('open-enquiry', {})">Enquire</button>
                        </div>
                    </div>
                    <ul class="grid content-start gap-2.5">@foreach ($m->features ?? [] as $f)<li class="flex gap-2.5 text-[0.9375rem]"><x-icon name="check" class="mt-0.5 size-5 shrink-0 text-success-600" />{{ $f }}</li>@endforeach</ul>
                </div>
            </article>
        @endforeach
        <div class="grid gap-6 md:grid-cols-2">
            <article class="reveal flex flex-col gap-5 rounded-xl border border-line bg-surface p-8 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-5"><span class="flex size-14 shrink-0 items-center justify-center rounded-lg bg-sun-400 text-navy-900"><x-icon name="users" class="size-7" /></span><div><h2 class="text-h3">Group training</h2><p class="mt-1 text-ink-muted">A private batch for your friends, classmates or team.</p></div></div>
                <a href="/group-training/" class="btn btn-primary shrink-0">Group training</a>
            </article>
            <article class="reveal on-dark flex flex-col gap-5 rounded-xl bg-grid-navy p-8 text-white sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-5"><span class="flex size-14 shrink-0 items-center justify-center rounded-lg bg-sun-400 text-navy-900"><x-icon name="building-2" class="size-7" /></span><div><h2 class="text-h3 text-white">Corporate training</h2><p class="mt-1 text-navy-100">Customised programmes for organisations.</p></div></div>
                <a href="/corporate-training/" class="btn btn-primary shrink-0">Corporate</a>
            </article>
        </div>
    </div>
</section>

<x-cta-band :title="content('training_methods.cta_title')" :text="content('training_methods.cta_text')">
    <button type="button" class="btn btn-primary btn-lg" @click="$dispatch('open-enquiry', {})">Talk to an advisor</button>
    <a href="/batches/" class="btn btn-ghost-light btn-lg">Upcoming batches</a>
</x-cta-band>
@endsection
