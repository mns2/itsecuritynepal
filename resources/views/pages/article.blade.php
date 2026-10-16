@extends('layouts.app')
@section('title', $post->meta_title ?: $post->title.' | IT Security Nepal')
@section('description', $post->meta_description ?: \Illuminate\Support\Str::limit($post->excerptText(), 155))
@section('og_type', 'article')

@push('head')
<script type="application/ld+json">{!! json_encode(array_filter([
    '@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => $post->title,
    'description' => $post->excerptText(), 'datePublished' => $post->published_at?->toAtomString(), 'dateModified' => $post->updated_at?->toAtomString(),
    'author' => ['@type' => 'Organization', 'name' => $post->author], 'publisher' => ['@id' => rtrim(config('app.url'), '/').'/#organization'],
    'mainEntityOfPage' => rtrim(config('app.url'), '/').$post->url(),
    'image' => $post->featured_image ? media($post->featured_image) : null,
]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
<meta property="article:published_time" content="{{ $post->published_at?->toAtomString() }}">
@endpush

@section('content')
<article>
    <header class="bg-grid-light">
        <div class="container-page pt-8 pb-12 lg:pb-16">
            <x-breadcrumbs :items="[['name' => 'Blog', 'url' => '/blog/'], ['name' => \Illuminate\Support\Str::limit($post->title, 48)]]" />
            <div class="mt-8 max-w-4xl">
                <p class="badge badge-navy">{{ $post->category }}</p>
                <h1 class="mt-4 text-h1">{{ $post->title }}</h1>
                <div class="mt-6 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-ink-muted">
                    <span class="flex items-center gap-3"><span class="flex size-10 items-center justify-center rounded-full bg-navy-700 font-semibold text-white">IT</span><span><span class="block font-semibold text-navy-700">{{ $post->author }}</span>Author</span></span>
                    <span class="flex items-center gap-2"><x-icon name="calendar-days" class="size-4" /><time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j M Y') }}</time></span>
                    <span class="flex items-center gap-2"><x-icon name="clock" class="size-4" />{{ $post->readingMinutes() }} min read</span>
                </div>
            </div>
        </div>
    </header>

    @if ($post->featured_image)
        <div class="container-page"><img src="{{ media($post->featured_image) }}" alt="" class="aspect-[21/9] w-full rounded-xl object-cover" width="1216" height="521"></div>
    @endif

    <div class="container-page grid gap-12 py-12 lg:grid-cols-[1fr_17rem] lg:py-16">
        <div class="min-w-0">
            @if (count($toc) > 3)
                <details class="mb-10 rounded-lg border border-line bg-surface p-5 lg:hidden">
                    <summary class="font-semibold text-navy-700">On this page</summary>
                    <ol class="mt-4 grid gap-2 text-[0.9375rem]">@foreach ($toc as $t)<li><a href="#{{ $t['id'] }}" class="no-underline hover:underline">{{ $t['text'] }}</a></li>@endforeach</ol>
                </details>
            @endif
            <div class="prose-article">{!! $html !!}</div>

            @if ($relatedCourses->isNotEmpty())
                <aside class="mt-14 rounded-xl border border-line bg-surface p-6 sm:p-8" aria-labelledby="rec-courses">
                    <h2 id="rec-courses" class="text-h3">Courses mentioned in this article</h2>
                    <ul class="mt-6 grid gap-4 md:grid-cols-2">@foreach ($relatedCourses->take(2) as $c)<li><x-course-card :course="$c" /></li>@endforeach</ul>
                </aside>
            @endif
        </div>
        <aside class="hidden lg:block">
            <div class="sticky top-36 grid gap-6">
                @if (count($toc) > 3)
                    <nav aria-label="Table of contents" class="rounded-lg border border-line p-5">
                        <p class="text-xs font-semibold tracking-wide text-ink-muted uppercase">On this page</p>
                        <ol class="mt-3 grid gap-2 text-sm">@foreach ($toc as $t)<li><a href="#{{ $t['id'] }}" class="text-ink no-underline hover:text-action">{{ $t['text'] }}</a></li>@endforeach</ol>
                    </nav>
                @endif
                <div class="on-dark rounded-lg bg-navy-700 p-5 text-white">
                    <p class="font-display font-semibold">Ready to start?</p>
                    <p class="mt-1 text-sm text-navy-100">Free counselling on the right course and next batch.</p>
                    <button type="button" class="btn btn-primary btn-sm btn-block mt-4" @click="$dispatch('open-enquiry', { course: '{{ $relatedCourses->first()?->slug }}' })">Talk to an advisor</button>
                </div>
            </div>
        </aside>
    </div>
</article>

@if ($related->isNotEmpty())
<section class="section bg-surface" aria-labelledby="more-articles">
    <div class="container-page">
        <h2 id="more-articles" class="text-h2">More articles</h2>
        <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach ($related as $p)<li><x-post-card :post="$p" /></li>@endforeach</ul>
    </div>
</section>
@endif
@endsection
