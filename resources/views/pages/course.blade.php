@extends('layouts.app')
@section('title', $course->seoTitle())
@section('description', $course->meta_description ?: \Illuminate\Support\Str::limit($course->summary, 155))

@php
    $contact = $site['contact'] ?? [];
    $next = $course->nextBatch();
    $modes = $course->modes ?? [];
    $waText = 'Hello, I would like to know about the '.$course->name.' course.';
    $courseLd = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Course',
        'name' => $course->name,
        'description' => $course->summary,
        'courseCode' => $course->official_code ? $course->code : null,
        'url' => rtrim(config('app.url'), '/').$course->url(),
        'educationalLevel' => $course->level,
        'provider' => ['@type' => 'EducationalOrganization', '@id' => rtrim(config('app.url'), '/').'/#organization', 'name' => $site['brand']['name'] ?? 'IT Security Nepal'],
        'hasCourseInstance' => collect($modes)->map(fn ($m) => ['@type' => 'CourseInstance', 'courseMode' => $m === 'Live Online' ? 'online' : 'onsite', 'location' => $m === 'Live Online' ? null : 'New Baneshwor, Kathmandu'])->map(fn ($i) => array_filter($i))->values()->all(),
    ]);
@endphp
@push('head')
<script type="application/ld+json">{!! json_encode($courseLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<section class="relative overflow-hidden bg-grid-light">
    <div class="container-page relative pt-8 pb-12 lg:pt-10 lg:pb-16">
        <x-breadcrumbs :items="[['name' => 'Courses', 'url' => '/course/'], ['name' => $course->category->name, 'url' => $course->category->url()], ['name' => $course->official_code ? $course->code : $course->name]]" />
        <div class="mt-8 max-w-3xl lg:max-w-[calc(100%-25rem)]">
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-md bg-sun-400 px-2.5 py-1 font-display text-sm font-bold text-navy-900">{{ $course->code }}</span>
                <a href="{{ $course->category->url() }}" class="badge badge-navy no-underline hover:bg-surface-2">{{ $course->category->name }}</a>
                <span class="badge badge-navy">{{ $course->level }}</span>
            </div>
            <h1 class="mt-5 text-h1">{{ $course->name }}</h1>
            <p class="mt-5 text-lead text-ink-muted">{{ $course->summary }}</p>
            <ul class="mt-7 flex flex-wrap gap-x-6 gap-y-2 text-[0.9375rem] text-ink-muted">
                @if ($course->certification)<li class="flex items-center gap-2"><x-icon name="award" class="size-5 text-action" />{{ $course->certification }}</li>@endif
                <li class="flex items-center gap-2"><x-icon name="monitor-play" class="size-5 text-action" />{{ implode(' · ', $modes) }}</li>
                @if ($course->duration)<li class="flex items-center gap-2"><x-icon name="timer" class="size-5 text-action" />{{ $course->duration }}</li>@endif
            </ul>
        </div>
    </div>
</section>

<div class="container-page lg:grid lg:grid-cols-[1fr_22rem] lg:gap-12">
    <aside class="relative z-10 -mt-6 lg:order-2 lg:-mt-48" aria-labelledby="panel-title">
        <div class="rounded-xl border border-line bg-white p-6 shadow-panel lg:sticky lg:top-36">
            <h2 id="panel-title" class="font-display text-lg font-semibold">Course at a glance</h2>
            <dl class="mt-4 divide-y divide-line text-[0.9375rem]">
                <div class="flex justify-between gap-4 py-2.5"><dt class="text-ink-muted">Level</dt><dd class="font-semibold text-navy-700">{{ $course->level }}</dd></div>
                @if ($course->certification)<div class="flex justify-between gap-4 py-2.5"><dt class="text-ink-muted">Certification</dt><dd class="text-right font-semibold text-navy-700">{{ $course->certification }}</dd></div>@endif
                <div class="flex justify-between gap-4 py-2.5"><dt class="text-ink-muted">Duration</dt><dd class="text-right font-semibold text-navy-700">{{ $course->duration ?: 'Ask an advisor' }}</dd></div>
                <div class="flex justify-between gap-4 py-2.5"><dt class="text-ink-muted">Fees</dt><dd class="text-right font-semibold text-navy-700">{{ content('course_page.fees_text') }}</dd></div>
                <div class="flex justify-between gap-4 py-2.5"><dt class="text-ink-muted">Training for</dt><dd class="text-right font-semibold text-navy-700">Individuals, groups &amp; companies</dd></div>
                <div class="flex justify-between gap-4 py-2.5"><dt class="text-ink-muted">Next batch</dt><dd @class(['text-right font-semibold', 'text-success-600' => $next, 'text-navy-700' => ! $next])>{{ $next ? ($next->starts_on?->format('j M Y') ?? $next->start_label) : 'Contact us' }}</dd></div>
            </dl>
            <div class="mt-5 grid gap-2">
                <button type="button" class="btn btn-primary btn-block" @click="$dispatch('open-enquiry', { course: '{{ $course->slug }}' })">{{ $next ? 'Join next batch' : 'Enquire now' }}</button>
                <div class="grid grid-cols-2 gap-2">
                    <a href="tel:{{ $contact['phonePrimary']['tel'] ?? '' }}" class="btn btn-outline btn-sm"><x-icon name="phone" class="size-4" /> Call</a>
                    @if (! empty($contact['whatsapp']['number']))
                        <a href="https://wa.me/{{ $contact['whatsapp']['number'] }}?text={{ rawurlencode($waText) }}" class="btn btn-outline btn-sm" rel="noopener" target="_blank"><x-icon name="message-circle" class="size-4" /> WhatsApp<span class="sr-only"> (opens in a new tab)</span></a>
                    @endif
                </div>
            </div>
            <p class="mt-4 flex items-center gap-2 text-xs text-ink-muted"><x-icon name="shield-check" class="size-4 text-success-600" /> {{ content('course_page.panel_note') }}</p>
        </div>
    </aside>

    <div class="min-w-0 lg:order-1">
        @php($toc = array_filter(['overview' => 'Overview', 'outcomes' => $course->outcomes ? 'Outcomes' : null, 'curriculum' => $course->modules ? 'Curriculum' : null, 'prerequisites' => $course->prerequisites ? 'Prerequisites' : null, 'careers' => $course->careers ? 'Careers' : null, 'faq' => $course->faqs ? 'FAQ' : null]))
        <nav aria-label="On this page" class="no-scrollbar sticky top-[7.6rem] z-20 -mx-4 mt-8 overflow-x-auto border-b border-line bg-white/95 px-4 backdrop-blur sm:mx-0 sm:px-0">
            <ul class="flex gap-1 text-sm font-semibold whitespace-nowrap">
                @foreach ($toc as $anchor => $label)<li><a href="#{{ $anchor }}" class="inline-flex min-h-12 items-center border-b-2 border-transparent px-3 text-ink-muted no-underline hover:border-sun-400 hover:text-navy-700">{{ $label }}</a></li>@endforeach
            </ul>
        </nav>

        <section id="overview" class="scroll-mt-52 pt-10" aria-labelledby="h-overview">
            <h2 id="h-overview" class="text-h2">Course overview</h2>
            <div class="mt-5 grid gap-4 text-[1.0625rem] leading-relaxed">@foreach ($course->overview ?? [] as $p)<p>{{ $p }}</p>@endforeach</div>
            @if (! empty($course->benefits))
                <ul class="mt-6 flex flex-wrap gap-2" aria-label="Course highlights">@foreach ($course->benefits as $b)<li class="badge badge-sun min-h-8 px-3 text-sm"><x-icon name="check" class="size-3.5" />{{ $b }}</li>@endforeach</ul>
            @endif
        </section>

        @if (! empty($course->outcomes))
            <section id="outcomes" class="scroll-mt-52 pt-14" aria-labelledby="h-outcomes">
                <h2 id="h-outcomes" class="text-h2">What you'll be able to do</h2>
                <ul class="mt-6 grid gap-4 sm:grid-cols-2">
                    @foreach ($course->outcomes as $o)<li class="flex gap-3 rounded-lg border border-line p-4"><span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-success-50 text-success-600"><x-icon name="check" class="size-4" /></span><span>{{ $o }}</span></li>@endforeach
                </ul>
            </section>
        @endif

        @if (! empty($course->modules))
            <section id="curriculum" class="scroll-mt-52 pt-14" aria-labelledby="h-curriculum" x-data="{ all: false }">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 id="h-curriculum" class="text-h2">Curriculum</h2>
                        <p class="mt-2 text-ink-muted">{{ count($course->modules) }} modules, each with hands-on lab work</p>
                    </div>
                    <button type="button" class="btn btn-ghost btn-sm" @click="all = !all; $root.querySelectorAll('details').forEach(d => d.open = all)" x-text="all ? 'Collapse all' : 'Expand all'">Expand all</button>
                </div>
                <div class="accordion mt-6">
                    @foreach ($course->modules as $m)
                        <details @if ($loop->first) open @endif>
                            <summary>
                                <span class="flex items-center gap-4">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-md bg-surface font-display text-sm font-bold text-action">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span>{{ preg_replace('/^Module \d+:\s*/', '', $m['title'] ?? '') }}</span>
                                </span>
                                <span class="acc-icon"><x-icon name="plus" class="size-4" /></span>
                            </summary>
                            <div class="acc-body pl-[4.5rem]"><ul class="grid gap-2">@foreach ($m['topics'] ?? [] as $t)<li class="flex gap-2.5"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-sun-500"></span><span>{{ $t }}</span></li>@endforeach</ul></div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif

        @foreach ($course->extra_sections ?? [] as $e)
            <section class="pt-14" aria-labelledby="h-extra-{{ $loop->iteration }}">
                <h2 id="h-extra-{{ $loop->iteration }}" class="text-h2">{{ $e['heading'] }}</h2>
                <ul class="mt-5 grid gap-3">@foreach ($e['items'] ?? [] as $i)<li class="flex gap-3"><span class="mt-2.5 size-1.5 shrink-0 rounded-full bg-sun-500"></span><span>{{ $i }}</span></li>@endforeach</ul>
            </section>
        @endforeach

        <section class="pt-14" aria-labelledby="h-labs">
            <div class="rounded-xl bg-surface p-6 sm:p-8">
                <h2 id="h-labs" class="text-h3">{{ content('course_page.learn_title') }}</h2>
                <ul class="mt-5 grid gap-5 sm:grid-cols-3">
                    @foreach (collect(content('course_page.learn_items', []))->map(fn ($i) => [$i['icon'] ?: 'check', $i['title'], $i['text']]) as [$ic, $t, $d])
                        <li><span class="flex size-10 items-center justify-center rounded-md bg-navy-700 text-sun-300"><x-icon :name="$ic" class="size-5" /></span><p class="mt-3 font-semibold text-navy-700">{{ $t }}</p><p class="mt-1 text-sm text-ink-muted">{{ $d }}</p></li>
                    @endforeach
                </ul>
            </div>
        </section>

        @if (! empty($course->prerequisites))
            <section id="prerequisites" class="scroll-mt-52 pt-14" aria-labelledby="h-pre">
                <h2 id="h-pre" class="text-h2">Prerequisites</h2>
                <ul class="mt-5 grid gap-3 sm:grid-cols-2">@foreach ($course->prerequisites as $p)<li class="flex items-start gap-3 rounded-lg bg-surface px-4 py-3"><x-icon name="circle-check" class="mt-0.5 size-5 shrink-0 text-action" /><span>{{ $p }}</span></li>@endforeach</ul>
                @if ($course->level !== 'Beginner' && ($start = $related->firstWhere('level', 'Beginner') ?? $related->first()))
                    <p class="mt-4 text-[0.9375rem] text-ink-muted">Not there yet? Start with <a href="{{ $start->url() }}">{{ $start->name }}</a>.</p>
                @endif
            </section>
        @endif

        <section class="pt-14" aria-labelledby="h-modes">
            <h2 id="h-modes" class="text-h2">Training options</h2>
            <ul class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <li class="card card-interactive group relative p-5"><span class="text-action"><x-icon name="user-round" class="size-6" /></span><h3 class="mt-3 font-semibold"><a href="/course/" class="stretched-link text-navy-700 no-underline">Individual</a></h3><p class="mt-1 text-sm text-ink-muted">Join a public batch: classroom, live online or one-on-one.</p></li>
                <li class="card card-interactive group relative p-5"><span class="text-action"><x-icon name="users" class="size-6" /></span><h3 class="mt-3 font-semibold"><a href="/group-training/" class="stretched-link text-navy-700 no-underline">Group</a></h3><p class="mt-1 text-sm text-ink-muted">Private batch for your friends, classmates or team.</p></li>
                <li class="card card-interactive group relative p-5"><span class="text-action"><x-icon name="building-2" class="size-6" /></span><h3 class="mt-3 font-semibold"><a href="/corporate-training/" class="stretched-link text-navy-700 no-underline">Corporate batch</a></h3><p class="mt-1 text-sm text-ink-muted">Private batch for your organisation's staff.</p></li>
                <li class="card card-interactive group relative p-5"><span class="text-action"><x-icon name="route" class="size-6" /></span><h3 class="mt-3 font-semibold"><a href="/mode-of-training/" class="stretched-link text-navy-700 no-underline">Training methods</a></h3><p class="mt-1 text-sm text-ink-muted">Classroom, live virtual, one-on-one and flip class.</p></li>
            </ul>
        </section>

        <section class="pt-14" aria-labelledby="h-batches">
            <h2 id="h-batches" class="text-h2">Upcoming batches</h2>
            @if ($course->batches->isNotEmpty())
                <ul class="mt-6 grid gap-3">
                    @foreach ($course->batches as $b)
                        <li class="flex flex-col gap-3 rounded-lg border border-line p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-semibold text-navy-700">{{ $b->starts_on?->format('j M Y') ?? $b->start_label }}{{ $b->schedule ? ' · '.$b->schedule : '' }}</p>
                                <p class="text-sm text-ink-muted">{{ $b->mode }}</p>
                            </div>
                            <div class="flex items-center gap-3"><x-status-badge :status="$b->status" /><button type="button" class="btn btn-navy btn-sm" @click="$dispatch('open-enquiry', { course: '{{ $course->slug }}' })">Reserve a seat</button></div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="mt-6 flex flex-col gap-4 rounded-lg border border-dashed border-line-strong bg-surface p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex gap-4"><x-icon name="calendar-days" class="size-6 shrink-0 text-action" /><div><p class="font-semibold text-navy-700">{{ content('course_page.no_batch_title') }}</p><p class="text-sm text-ink-muted">{{ content('course_page.no_batch_text') }}</p></div></div>
                    <button type="button" class="btn btn-outline btn-sm shrink-0" @click="$dispatch('open-enquiry', { course: '{{ $course->slug }}' })">Notify me</button>
                </div>
            @endif
        </section>

        @if (! empty($course->careers))
            <section id="careers" class="scroll-mt-52 pt-14" aria-labelledby="h-careers">
                <h2 id="h-careers" class="text-h2">Career opportunities</h2>
                <p class="mt-2 text-ink-muted">Roles this course prepares you for:</p>
                <ul class="mt-5 flex flex-wrap gap-2">@foreach ($course->careers as $c)<li class="inline-flex min-h-10 items-center gap-2 rounded-full border border-line px-4 text-[0.9375rem] font-medium text-navy-700"><x-icon name="briefcase-business" class="size-4 text-sun-500" />{{ $c }}</li>@endforeach</ul>
            </section>
        @endif

        @if (! empty($course->faqs))
            <section id="faq" class="scroll-mt-52 pt-14" aria-labelledby="h-faq">
                <h2 id="h-faq" class="text-h2">Frequently asked questions</h2>
                <div class="mt-6"><x-faq-list :faqs="$course->faqs" :schema="true" /></div>
            </section>
        @endif
        <div class="pb-16"></div>
    </div>
</div>

@if (\App\Support\GoogleReviews::settings()['show_course'])<x-google-reviews />@endif

@if ($related->isNotEmpty())
<section class="section bg-surface" aria-labelledby="h-related">
    <div class="container-page">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <x-section-heading eyebrow="Keep going" :title="'Related '.$course->category->name.' courses'" id="h-related" />
            <a href="{{ $course->category->url() }}" class="link-arrow shrink-0">All {{ $course->category->name }} courses <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        <ul class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">@foreach ($related as $c)<li><x-course-card :course="$c" /></li>@endforeach</ul>
    </div>
</section>
@endif
@endsection
