@extends('layouts.app')
@section('title', ($path->meta_title ?: $path->name.' Career Pathway: Courses & Certifications').' | IT Security Nepal')
@section('description', $path->meta_description ?: ($path->intro ?: 'How to become a '.$path->name.': the courses to take in order, skills you will build and typical job titles.'))
@if ($img = media($path->image))@section('og_image', $img)@endif

@section('content')
<section class="bg-grid-light">
    <div class="container-page grid gap-10 pt-8 pb-12 lg:grid-cols-[1.4fr_1fr] lg:items-end">
        <div>
            <x-breadcrumbs :items="[['name' => 'Career pathways', 'url' => '/career-pathways/'], ['name' => $path->name]]" />
            <p class="mt-8 text-eyebrow text-action">Career pathway</p>
            <h1 class="mt-2 text-h1">{{ $path->name }}</h1>
            @if ($path->intro)<p class="mt-4 max-w-2xl text-lead text-ink-muted">{{ $path->intro }}</p>@endif
            @if ($path->goal)<p class="mt-4 text-ink-muted">Goal: <strong class="text-navy-700">{{ $path->goal }}</strong></p>@endif
            <div class="mt-6 flex flex-wrap gap-2.5">
                <button type="button" class="btn btn-sun" @click="$dispatch('open-enquiry', { course: @js($steps->first()['course']->slug ?? '') })">Plan my pathway</button>
                <a href="#steps" class="btn btn-outline">See the steps</a>
            </div>
        </div>
        @if ($img)
            <img src="{{ $img }}" alt="{{ media_alt($path->image) }}" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-panel" fetchpriority="high">
        @endif
    </div>
</section>

<div class="container-page grid gap-12 py-12 lg:grid-cols-[1fr_20rem] lg:gap-16 lg:py-16">
    <div>
        @if ($path->role_text)
            <section aria-labelledby="h-role">
                <h2 id="h-role" class="text-h2">What the job involves</h2>
                <p class="mt-4 text-[1.0625rem] leading-relaxed text-ink">{{ $path->role_text }}</p>
            </section>
        @endif

        <section id="steps" class="scroll-mt-32 pt-12" aria-labelledby="h-steps">
            <h2 id="h-steps" class="text-h2">Your steps</h2>
            <ol class="relative mt-6 grid gap-5 border-l-2 border-line pl-8">
                @foreach ($steps as $s)
                    @php($c = $s['course'])
                    <li class="relative">
                        <span @class(['absolute -left-[3.05rem] grid size-10 place-items-center rounded-full font-display font-extrabold ring-4 ring-white', 'bg-sun-400 text-navy-900' => $loop->last, 'bg-navy-700 text-white' => ! $loop->last]) aria-hidden="true">{{ $loop->iteration }}</span>
                        <div class="card card-interactive group relative p-5 sm:p-6">
                            <p class="text-xs font-semibold tracking-wide text-ink-muted uppercase"><span class="sr-only">Step {{ $loop->iteration }}: </span>{{ $s['note'] }}</p>
                            <h3 class="mt-1.5 font-display text-lg font-bold"><a href="{{ $c->url() }}" class="stretched-link text-navy-700 no-underline group-hover:text-action"><span class="text-action">{{ $c->code }}</span> · {{ $c->name }}</a></h3>
                            @if ($c->summary)<p class="mt-2 text-[0.9375rem] text-ink-muted">{{ \Illuminate\Support\Str::limit($c->summary, 180) }}</p>@endif
                            <p class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-ink-muted">
                                <span>{{ $c->level }}</span>
                                @if ($c->duration)<span>{{ $c->duration }}</span>@endif
                                @if ($c->certification)<span class="font-medium text-navy-700">Certification: {{ $c->certification }}</span>@endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        @if (! empty($path->skills))
            <section class="pt-12" aria-labelledby="h-skills">
                <h2 id="h-skills" class="text-h2">Skills you will build</h2>
                <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach ($path->skills as $skill)<li class="flex gap-2.5"><x-icon name="check" class="mt-0.5 size-5 shrink-0 text-success-600" />{{ $skill }}</li>@endforeach
                </ul>
            </section>
        @endif
    </div>

    <aside class="grid content-start gap-6">
        @if (! empty($path->job_titles))
            <section class="card p-6" aria-labelledby="h-jobs">
                <h2 id="h-jobs" class="font-display text-lg font-bold text-navy-700">Typical job titles</h2>
                <ul class="mt-4 grid gap-2">@foreach ($path->job_titles as $j)<li class="flex items-center gap-2.5 text-[0.9375rem]"><x-icon name="briefcase" class="size-4 shrink-0 text-action" />{{ $j }}</li>@endforeach</ul>
                @if ($path->salary_note)<p class="mt-4 border-t border-line pt-4 text-sm text-ink-muted">{{ $path->salary_note }}</p>@endif
            </section>
        @endif
        <section class="on-dark rounded-xl bg-navy-700 p-6 text-navy-100">
            <h2 class="font-display text-lg font-bold text-white">Not sure where to start?</h2>
            <p class="mt-2 text-sm">Talk to an advisor about your background and goals. Career counselling is free.</p>
            <button type="button" class="btn btn-sun mt-4 w-full" @click="$dispatch('open-enquiry')">Book free counselling</button>
        </section>
        @if ($others->isNotEmpty())
            <nav aria-label="Other career pathways" class="card p-6">
                <h2 class="font-display text-lg font-bold text-navy-700">Other pathways</h2>
                <ul class="mt-3 grid gap-2">@foreach ($others as $o)<li><a href="{{ $o->url() }}" class="flex items-center gap-2 text-[0.9375rem] no-underline hover:underline"><x-icon :name="$o->icon" class="size-4 text-action" />{{ $o->name }}</a></li>@endforeach</ul>
            </nav>
        @endif
    </aside>
</div>
@endsection
