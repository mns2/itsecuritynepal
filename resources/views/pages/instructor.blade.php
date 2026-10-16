@extends('layouts.app')
@section('title', $instructor->name.' | Instructor | IT Security Nepal')
@section('description', \Illuminate\Support\Str::limit((string) $instructor->bio, 155))

@push('head')
<script type="application/ld+json">{!! json_encode(array_filter(['@context' => 'https://schema.org', '@type' => 'Person', 'name' => $instructor->name, 'jobTitle' => $instructor->role, 'description' => $instructor->bio, 'worksFor' => ['@id' => rtrim(config('app.url'), '/').'/#organization'], 'sameAs' => array_filter([$instructor->linkedin_url])]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<section class="bg-grid-light">
    <div class="container-page pt-8 pb-14">
        <x-breadcrumbs :items="[['name' => 'Instructors', 'url' => '/instructors/'], ['name' => $instructor->name]]" />
        <div class="mt-8 flex flex-col gap-8 sm:flex-row sm:items-center">
            @if ($instructor->photo)
                <img src="{{ media($instructor->photo) }}" alt="{{ $instructor->name }}" class="size-32 shrink-0 rounded-full object-cover ring-4 ring-sun-400" width="128" height="128">
            @endif
            <div>
                <h1 class="text-h1">{{ $instructor->name }}</h1>
                @if ($instructor->role)<p class="mt-2 text-lead text-ink-muted">{{ $instructor->role }}</p>@endif
                <ul class="mt-4 flex flex-wrap gap-2">@foreach ($instructor->expertise ?? [] as $t)<li class="badge badge-navy">{{ $t }}</li>@endforeach</ul>
            </div>
        </div>
    </div>
</section>
<div class="container-page grid gap-12 py-14 lg:grid-cols-[1fr_20rem]">
    <div class="prose-article">
        <h2>About</h2>
        <p>{{ $instructor->bio }}</p>
        @if (! empty($instructor->certifications))
            <h3>Certifications</h3>
            <ul>@foreach ($instructor->certifications as $c)<li>{{ $c }}</li>@endforeach</ul>
        @endif
    </div>
    <aside class="card h-fit p-6">
        <h2 class="font-display text-lg font-semibold">Learn with {{ \Illuminate\Support\Str::before($instructor->name, ' ') }}</h2>
        <p class="mt-2 text-sm text-ink-muted">Ask about upcoming batches taught by this instructor.</p>
        <button type="button" class="btn btn-primary btn-block mt-5" @click="$dispatch('open-enquiry', {})">Enquire</button>
        @if ($instructor->linkedin_url)<a href="{{ $instructor->linkedin_url }}" class="btn btn-outline btn-block mt-2" rel="noopener" target="_blank"><x-icon name="linkedin" class="size-5" /> LinkedIn</a>@endif
    </aside>
</div>
@endsection
