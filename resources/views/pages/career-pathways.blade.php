@extends('layouts.app')
@section('title', 'IT Career Pathways in Nepal: Linux, Cybersecurity, Networking, DevOps & Cloud | IT Security Nepal')
@section('description', 'Step-by-step IT career pathways: which courses to take, in what order, the skills you build and the jobs they lead to. Linux, cybersecurity, networking, DevOps and Azure cloud.')

@section('content')
<section class="bg-grid-light">
    <div class="container-page pt-8 pb-12">
        <x-breadcrumbs :items="[['name' => 'Career pathways']]" />
        <h1 class="mt-8 text-h1">Career pathways</h1>
        <p class="mt-4 max-w-2xl text-lead text-ink-muted">Pick the job you want, then follow the courses in order, from your first steps to professional certification.</p>
        <div class="mt-6 flex flex-wrap gap-2.5">
            <a href="/course-finder/" class="btn btn-sun"><x-icon name="compass" class="size-5" /> Not sure? Try the Course Finder</a>
            <button type="button" class="btn btn-outline" @click="$dispatch('open-enquiry')">Free career counselling</button>
        </div>
    </div>
</section>

<section class="section-tight" aria-label="All career pathways">
    <div class="container-page">
        <ul class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($paths as $p)
                @php($steps = $p->resolvedSteps())
                <li class="card card-interactive group relative flex flex-col overflow-hidden">
                    @if ($img = media($p->image))
                        <img src="{{ $img }}" alt="" class="aspect-[16/9] w-full object-cover" loading="lazy">
                    @endif
                    <div class="flex flex-1 flex-col p-6">
                        <span class="grid size-12 place-items-center rounded-xl bg-navy-700 text-sun-400"><x-icon :name="$p->icon" class="size-6" /></span>
                        <h2 class="mt-4 text-h3"><a href="{{ $p->url() }}" class="stretched-link text-navy-700 no-underline group-hover:text-action">{{ $p->name }}</a></h2>
                        @if ($p->intro)<p class="mt-2 text-[0.9375rem] text-ink-muted">{{ $p->intro }}</p>@endif
                        <p class="mt-4 text-sm font-semibold text-navy-700">{{ $steps->count() }} steps: {{ $steps->pluck('course.code')->join(' → ') }}</p>
                        @if (! empty($p->job_titles))
                            <ul class="mt-4 flex flex-wrap gap-1.5">@foreach (array_slice($p->job_titles, 0, 3) as $j)<li class="badge bg-surface text-navy-700">{{ $j }}</li>@endforeach</ul>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endsection
