@extends('layouts.app')
@section('title', $page->title.' | IT Security Nepal')
@section('description', $page->meta_description ?: $page->title.' of IT Security Nepal, New Baneshwor, Kathmandu, covering itsecuritynepal.com and our IT training services.')
{{-- Until approved legal text is entered in the admin panel, the page is kept out of search results. --}}
@if (blank($page->body))
    @section('noindex', '1')
@endif

@section('content')
<section class="bg-grid-light">
    <div class="container-page pt-8 pb-10">
        <x-breadcrumbs :items="[['name' => $page->title]]" />
        <h1 class="mt-8 text-h1">{{ $page->title }}</h1>
        @if ($page->last_updated)<p class="mt-3 text-sm text-ink-muted">Last updated: {{ $page->last_updated->format('j F Y') }}</p>@endif
    </div>
</section>
<div class="container-page py-12 lg:py-16">
    <div class="prose-article">
        @if (filled($page->body))
            {!! \Illuminate\Support\Str::markdown($page->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
        @else
            <p>This page is being updated. For any question about how we handle your information or about our terms, please contact us.</p>
        @endif
        <p>Questions? Email <a href="mailto:{{ $site['contact']['email'] ?? '' }}">{{ $site['contact']['email'] ?? '' }}</a> or call <a href="tel:{{ $site['contact']['phonePrimary']['tel'] ?? '' }}">{{ $site['contact']['phonePrimary']['label'] ?? '' }}</a>.</p>
    </div>
</div>
@endsection
