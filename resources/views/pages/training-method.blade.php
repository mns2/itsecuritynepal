@extends('layouts.app')
@section('title', $method->name.' in Kathmandu | IT Security Nepal')
@section('description', \Illuminate\Support\Str::limit((string) $method->body, 155))

@section('content')
<section class="bg-grid-light">
    <div class="container-page pt-8 pb-14 lg:pb-20">
        <x-breadcrumbs :items="[['name' => 'Training methods', 'url' => '/mode-of-training/'], ['name' => $method->name]]" />
        <div class="mt-8 grid gap-10 lg:grid-cols-[1.3fr_1fr] lg:items-center">
            <div>
                <span class="flex size-14 items-center justify-center rounded-lg bg-sun-400 text-navy-900"><x-icon :name="$method->icon" class="size-7" /></span>
                <h1 class="mt-6 text-h1">{{ $method->name }}</h1>
                <p class="mt-4 text-lead text-ink-muted">{{ $method->body }}</p>
            </div>
            <ul class="grid gap-3 rounded-xl border border-line bg-white p-6">
                @foreach ($method->features ?? [] as $f)<li class="flex gap-3 text-ink-muted"><x-icon name="check" class="size-5 shrink-0 text-action" />{{ $f }}</li>@endforeach
                @if ($method->best_for)<li class="mt-2 border-t border-line pt-4 text-sm text-ink-muted"><strong class="text-navy-700">Best for:</strong> {{ $method->best_for }}</li>@endif
            </ul>
        </div>
    </div>
</section>
<section class="section">
    <div class="container-page grid gap-12 lg:grid-cols-[1.4fr_1fr]">
        <div>
            <h2 class="text-h2">Popular courses in this format</h2>
            <ul class="mt-8 grid gap-5 sm:grid-cols-2">@foreach ($courses as $c)<li><x-course-card :course="$c" /></li>@endforeach</ul>
        </div>
        <aside class="card h-fit p-6 sm:p-8 lg:sticky lg:top-36" aria-labelledby="tm-enq">
            <h2 id="tm-enq" class="text-h3">Enquire about {{ \Illuminate\Support\Str::lower($method->name) }}</h2>
            <p class="mt-2 mb-6 text-sm text-ink-muted">Tell us the course you're interested in and we'll confirm availability.</p>
            <x-enquiry-form id="tm-enquiry" :compact="true" />
        </aside>
    </div>
</section>
@endsection
