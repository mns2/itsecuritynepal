@extends('layouts.app')
@section('title', 'Our Instructors | Certified IT Trainers | IT Security Nepal')
@section('description', 'Meet the certified instructors who teach Red Hat, cybersecurity, networking, cloud and DevOps courses at IT Security Nepal.')

@section('content')
<section class="bg-grid-light">
    <div class="container-page pt-8 pb-12 lg:pb-16">
        <x-breadcrumbs :items="[['name' => 'About', 'url' => '/about/'], ['name' => 'Instructors']]" />
        <div class="mt-8 max-w-3xl">
            <h1 class="text-h1">Learn from certified practitioners</h1>
            <p class="mt-4 text-lead text-ink-muted">Our instructors hold the certifications they teach and bring real industry experience into every lab session.</p>
        </div>
    </div>
</section>

<section class="section-tight" aria-label="Instructor directory">
    <div class="container-page">
        @if ($instructors->isEmpty())
            <div class="rounded-xl border border-dashed border-line-strong bg-surface p-10 text-center">
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-white text-action shadow-card"><x-icon name="graduation-cap" class="size-6" /></span>
                <h2 class="mt-5 text-h3">Instructor profiles are coming soon</h2>
                <p class="mx-auto mt-2 max-w-lg text-ink-muted">Want to know who teaches a course? Ask an advisor and we'll introduce you to the instructor for your batch.</p>
                <button type="button" class="btn btn-navy mt-6" @click="$dispatch('open-enquiry', {})">Ask an advisor</button>
            </div>
        @else
            <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($instructors as $i)
                    <li>
                        <article class="card card-interactive group relative flex h-full flex-col p-6">
                            <div class="flex items-center gap-4">
                                @if ($i->photo)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($i->photo) }}" alt="{{ $i->name }}" class="size-16 shrink-0 rounded-full object-cover" width="64" height="64" loading="lazy">
                                @else
                                    <span class="flex size-16 shrink-0 items-center justify-center rounded-full bg-surface font-display text-lg font-bold text-action">{{ collect(explode(' ', $i->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join('') }}</span>
                                @endif
                                <div><h2 class="text-lg font-semibold"><a href="/instructors/{{ $i->slug }}/" class="stretched-link text-navy-700 no-underline">{{ $i->name }}</a></h2><p class="text-sm text-ink-muted">{{ $i->role }}</p></div>
                            </div>
                            <p class="mt-4 line-clamp-3 text-[0.9375rem] text-ink-muted">{{ $i->bio }}</p>
                            <ul class="mt-4 flex flex-wrap gap-2" aria-label="Expertise">@foreach ($i->expertise ?? [] as $t)<li class="badge badge-navy">{{ $t }}</li>@endforeach</ul>
                        </article>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>

<x-cta-band title="Want to teach with us?" text="We are always interested in hearing from certified professionals who love teaching.">
    <a href="/contact/" class="btn btn-primary btn-lg">Get in touch</a>
</x-cta-band>
@endsection
