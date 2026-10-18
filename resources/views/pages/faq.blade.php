@extends('layouts.app')
@section('title', 'Frequently Asked Questions | IT Security Nepal')
@section('description', 'Answers about beginner eligibility, course selection, online classes, schedules, certification exams, fees, enrolment and group training at IT Security Nepal.')

@php
    $labels = \App\Models\Faq::GROUPS;
    $allFaqs = $groups->flatten();
@endphp
@push('head')
<script type="application/ld+json">{!! \App\Support\Seo::jsonLd(['@type' => 'FAQPage', 'mainEntity' => $allFaqs->map(fn ($f) => ['@type' => 'Question', 'name' => $f->question, 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f->answer]])->values()->all()]) !!}</script>
@endpush

@section('content')
<section class="bg-grid-light">
    <div class="container-page pt-8 pb-12">
        <x-breadcrumbs :items="[['name' => 'FAQ']]" />
        <h1 class="mt-8 text-h1">{{ content('listings.faq_title') }}</h1>
        <p class="mt-4 max-w-2xl text-lead text-ink-muted">{{ content('listings.faq_lead') }} <a href="/contact/">Contact us</a></p>
    </div>
</section>

<section class="section-tight" x-data="faqSearch">
    <div class="container-page grid grid-cols-1 gap-10 lg:grid-cols-[15rem_1fr]">
        <nav aria-label="FAQ categories" class="min-w-0 lg:sticky lg:top-36 lg:self-start">
            <ul class="no-scrollbar flex gap-2 overflow-x-auto lg:flex-col lg:gap-1">
                <li><button type="button" class="flex min-h-11 w-full shrink-0 items-center rounded-md px-3 text-left text-[0.9375rem] font-semibold whitespace-nowrap" :class="group === 'all' ? 'bg-navy-700 text-white' : 'text-navy-700 hover:bg-surface'" :aria-pressed="group === 'all'" @click="group = 'all'">All questions</button></li>
                @foreach ($groups as $key => $items)
                    <li><button type="button" class="flex min-h-11 w-full shrink-0 items-center rounded-md px-3 text-left text-[0.9375rem] font-semibold whitespace-nowrap" :class="group === '{{ $key }}' ? 'bg-navy-700 text-white' : 'text-navy-700 hover:bg-surface'" :aria-pressed="group === '{{ $key }}'" @click="group = '{{ $key }}'">{{ $labels[$key] ?? \Illuminate\Support\Str::headline($key) }}</button></li>
                @endforeach
            </ul>
        </nav>
        <div class="min-w-0">
            <div class="relative" role="search">
                <label for="faq-q" class="sr-only">Search questions</label>
                <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-ink-muted"><x-icon name="search" class="size-5" /></span>
                <input id="faq-q" type="search" class="input h-14 pl-12" placeholder="Search questions, e.g. online, fees, RHCSA" x-model.debounce.150ms="q">
            </div>
            <p class="mt-3 text-sm text-ink-muted" aria-live="polite"><span x-text="count">{{ $allFaqs->count() }}</span> questions</p>
            @foreach ($groups as $key => $items)
                <section class="mt-8" data-faq-group aria-labelledby="fg-{{ $key }}">
                    <h2 id="fg-{{ $key }}" class="text-h3">{{ $labels[$key] ?? \Illuminate\Support\Str::headline($key) }}</h2>
                    <div class="accordion mt-4">
                        @foreach ($items as $f)
                            <details data-faq data-group="{{ $key }}">
                                <summary><span>{{ $f->question }}</span><span class="acc-icon"><x-icon name="plus" class="size-4" /></span></summary>
                                <div class="acc-body"><p>{{ $f->answer }}</p></div>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach
            <div x-show="count === 0" x-cloak class="mt-8 rounded-xl border border-dashed border-line-strong bg-surface p-10 text-center">
                <p class="font-semibold text-navy-700">No questions match "<span x-text="q"></span>"</p>
                <p class="mt-1 text-sm text-ink-muted">Ask us directly and we'll answer.</p>
                <button type="button" class="btn btn-navy btn-sm mt-4" @click="$dispatch('open-enquiry', {})">Ask a question</button>
            </div>
        </div>
    </div>
</section>
@endsection
