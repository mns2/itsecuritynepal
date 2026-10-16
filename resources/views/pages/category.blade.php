@extends('layouts.app')
@section('title', $category->meta_title ?: $category->name.' Training in Kathmandu, Nepal | IT Security Nepal')
@section('description', $category->meta_description ?: \Illuminate\Support\Str::limit($category->intro, 155))

@section('content')
<section class="relative overflow-hidden bg-grid-light">
    <div class="container-page relative pt-8 pb-14 lg:pt-10 lg:pb-20">
        <x-breadcrumbs :items="[['name' => 'Courses', 'url' => '/course/'], ['name' => $category->name]]" />
        <div class="mt-8 grid gap-10 lg:grid-cols-[1.4fr_1fr] lg:items-center">
            <div>
                <span class="flex size-14 items-center justify-center rounded-lg bg-sun-400 text-navy-900"><x-icon :name="$category->icon" class="size-7" /></span>
                <h1 class="mt-6 text-h1">{{ $category->name }} training</h1>
                <p class="mt-5 max-w-2xl text-lead text-ink-muted">{{ $category->intro }}</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#courses" class="btn btn-primary btn-lg">View {{ $category->publishedCourses->count() }} courses <x-icon name="arrow-down" class="size-5" /></a>
                    <button type="button" class="btn btn-outline btn-lg" @click="$dispatch('open-enquiry', {})">Talk to an advisor</button>
                </div>
            </div>
            @if (! empty($category->objectives))
                <div class="rounded-xl border border-line bg-white p-6 lg:p-8">
                    <h2 class="font-display text-lg font-bold text-navy-700">What you'll learn</h2>
                    <ul class="mt-4 grid gap-3">
                        @foreach ($category->objectives as $o)<li class="flex gap-3 text-ink-muted"><span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-sun-400 text-navy-900"><x-icon name="check" class="size-3.5" /></span>{{ $o }}</li>@endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</section>

<section id="courses" class="section scroll-mt-40" aria-labelledby="cat-courses">
    <div class="container-page">
        <x-section-heading :title="$category->short_name.' courses'" intro="Every course combines instructor-led theory with hands-on labs and exam-focused practice." id="cat-courses" />
        @foreach (\App\Models\Course::LEVELS as $level)
            @php($lc = $category->publishedCourses->where('level', $level))
            @if ($lc->isNotEmpty())
                <h3 class="mt-12 flex items-center gap-3 text-sm font-semibold tracking-wide text-ink-muted uppercase"><span>{{ $level }}</span><span class="h-px flex-1 bg-line"></span></h3>
                <ul class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($lc as $c)<li><x-course-card :course="$c" :heading-level="4" /></li>@endforeach
                </ul>
            @endif
        @endforeach
    </div>
</section>

@if ($path && ($steps = $path->resolvedSteps())->isNotEmpty())
<section class="section bg-surface" aria-labelledby="cat-path">
    <div class="container-page">
        <x-section-heading eyebrow="Recommended learning path" :title="$path->name" :intro="'Goal: '.$path->goal.'. Follow these steps in order, or start wherever your experience fits.'" id="cat-path" />
        <ol class="mt-10 grid gap-4 md:grid-cols-{{ max(1, min(5, $steps->count())) }}">
            @foreach ($steps as $s)
                <li>
                    <a href="{{ $s['course']->url() }}" class="card card-interactive flex h-full flex-col p-5 no-underline">
                        <span class="flex items-center gap-3"><span class="flex size-8 items-center justify-center rounded-full bg-navy-700 font-display text-sm font-bold text-sun-300">{{ $loop->iteration }}</span><span class="text-xs font-semibold text-ink-muted">{{ $s['note'] }}</span></span>
                        <span class="mt-4 font-display text-sm font-bold text-action">{{ $s['course']->code }}</span>
                        <span class="mt-1 font-display font-semibold text-navy-700">{{ $s['course']->name }}</span>
                    </a>
                </li>
            @endforeach
        </ol>
    </div>
</section>
@endif

<section class="section" aria-labelledby="cat-faq">
    <div class="container-page grid gap-12 lg:grid-cols-[1.3fr_1fr]">
        <div>
            <x-section-heading eyebrow="FAQ" :title="$category->short_name.' questions'" id="cat-faq" />
            <div class="mt-8"><x-faq-list :faqs="$category->faqs ?? []" :schema="true" /></div>
        </div>
        <aside class="card p-6 sm:p-8 lg:self-start" aria-labelledby="cat-enq">
            <h2 id="cat-enq" class="text-h3">Ask about {{ $category->short_name }} training</h2>
            <p class="mt-2 mb-6 text-sm text-ink-muted">We'll recommend the right starting course and the next batch date.</p>
            <x-enquiry-form id="cat-enquiry" :compact="true" :default-course="$category->publishedCourses->first()?->slug ?? ''" />
        </aside>
    </div>
</section>
@endsection
