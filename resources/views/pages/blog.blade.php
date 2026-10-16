@extends('layouts.app')
@section('title', 'IT Career & Certification Guides | Blog | IT Security Nepal')
@section('description', 'Guides on RHCSA, RHCE, cybersecurity careers, Linux and IT certifications in Nepal, plus news and offers from IT Security Nepal.')

@section('content')
<section class="bg-grid-light">
    <div class="container-page pt-8 pb-12">
        <x-breadcrumbs :items="[['name' => 'Blog']]" />
        <h1 class="mt-8 text-h1">{{ content('listings.blog_title') }}</h1>
        <p class="mt-4 max-w-2xl text-lead text-ink-muted">{{ content('listings.blog_lead') }}</p>
    </div>
</section>

@if ($featuredPost)
<section class="section-tight" aria-labelledby="featured-post">
    <div class="container-page">
        <h2 id="featured-post" class="sr-only">Latest article</h2>
        <article class="card group relative grid overflow-hidden lg:grid-cols-2">
            <div class="on-dark relative flex min-h-64 flex-col justify-end overflow-hidden bg-media p-8" aria-hidden="true">
                <span class="badge w-fit bg-sun-400 text-navy-900">Latest</span>
                <p class="mt-4 max-w-md font-display text-2xl font-semibold text-white">{{ $featuredPost->title }}</p>
            </div>
            <div class="flex flex-col justify-center p-6 sm:p-10">
                <p class="text-sm text-ink-muted"><span class="font-semibold text-action">{{ $featuredPost->category }}</span> · <time datetime="{{ $featuredPost->published_at->toDateString() }}">{{ $featuredPost->published_at->format('j M Y') }}</time> · {{ $featuredPost->readingMinutes() }} min read</p>
                <h3 class="mt-3 text-h2"><a href="{{ $featuredPost->url() }}" class="stretched-link text-navy-700 no-underline group-hover:text-action">{{ $featuredPost->title }}</a></h3>
                <p class="mt-4 text-ink-muted">{{ $featuredPost->excerptText() }}</p>
                <p class="mt-6 flex items-center gap-3 text-sm"><span class="flex size-9 items-center justify-center rounded-full bg-navy-700 font-semibold text-white">IT</span><span><span class="block font-semibold text-navy-700">{{ $featuredPost->author }}</span><span class="text-ink-muted">Author</span></span></p>
            </div>
        </article>
    </div>
</section>
@endif

<section class="section-tight" aria-labelledby="all-posts" x-data="listFilter('[data-post-item]')">
    <div class="container-page">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <h2 id="all-posts" class="text-h2">All articles</h2>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <div class="no-scrollbar flex gap-2 overflow-x-auto" role="group" aria-label="Filter by category">
                    <button type="button" class="chip shrink-0" :aria-pressed="active === 'all'" @click="active = 'all'">All</button>
                    @foreach ($posts->pluck('category')->filter()->unique() as $cat)<button type="button" class="chip shrink-0" :aria-pressed="active === '{{ $cat }}'" @click="active = '{{ $cat }}'">{{ $cat }}</button>@endforeach
                </div>
                <label class="relative block sm:w-60"><span class="sr-only">Search articles</span><span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-ink-muted"><x-icon name="search" class="size-4" /></span><input type="search" class="input min-h-10 pl-9 text-sm" placeholder="Search articles" x-model.debounce.150ms="q"></label>
            </div>
        </div>
        <p class="sr-only" aria-live="polite" x-text="count + ' articles shown'"></p>
        <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($posts as $p)<li data-post-item data-category="{{ $p->category }}"><x-post-card :post="$p" /></li>@endforeach
        </ul>
        <div x-show="count === 0" x-cloak class="mt-8 rounded-xl border border-dashed border-line-strong bg-surface p-10 text-center">
            <p class="font-semibold text-navy-700">No articles found</p>
            <p class="mt-1 text-sm text-ink-muted">Try another search or category.</p>
            <button type="button" class="btn btn-outline btn-sm mt-4" @click="active = 'all'; q = ''">Show all articles</button>
        </div>
    </div>
</section>
@endsection
