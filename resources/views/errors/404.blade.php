@extends('layouts.app')
@section('title', 'Page not found | IT Security Nepal')
@section('noindex', '1')

@section('content')
<section class="bg-grid-light">
    <div class="container-page flex min-h-[60vh] flex-col items-center justify-center py-20 text-center">
        <p class="font-display text-sm font-bold text-action">404 · page not found</p>
        <h1 class="mt-4 text-h1">We couldn't find that page</h1>
        <p class="mt-4 max-w-xl text-lead text-ink-muted">The link may be old or mistyped. Try searching for a course, or start from one of these pages.</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <button type="button" class="btn btn-primary btn-lg" @click="$dispatch('open-search')"><x-icon name="search" class="size-5" /> Search courses</button>
            <a href="/" class="btn btn-outline btn-lg">Go to homepage</a>
        </div>
        <ul class="mt-10 flex flex-wrap justify-center gap-2">
            @foreach (\App\Models\Category::orderBy('sort')->take(5)->get() as $cat)<li><a href="{{ $cat->url() }}" class="badge badge-navy min-h-9 px-4 no-underline hover:bg-surface-2">{{ $cat->short_name }}</a></li>@endforeach
        </ul>
    </div>
</section>
@endsection
