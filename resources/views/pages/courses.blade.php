@extends('layouts.app')
@section('title', 'IT Courses & Certification Training in Nepal | IT Security Nepal')
@section('description', 'Browse '.$courses->count().' hands-on IT courses in Kathmandu: Red Hat RHCSA/RHCE, ethical hacking, CCNA, Kubernetes, Azure, CompTIA and ISC2. Filter by track, level and training mode.')

@section('content')
<div x-data='courseDirectory({ names: @json($categories->pluck('short_name', 'key'), JSON_HEX_APOS) })' class="contents">
<section class="border-b border-line bg-grid-light">
    <div class="container-page pt-8 pb-10 lg:pt-10 lg:pb-14">
        <x-breadcrumbs :items="[['name' => 'Courses']]" />
        <div class="mt-6 grid gap-8 lg:grid-cols-[1.3fr_1fr] lg:items-end">
            <div>
                <h1 class="text-h1">{{ content('listings.courses_title') }}</h1>
                <p class="mt-4 max-w-2xl text-lead text-ink-muted">{{ $courses->count() }} {{ content('listings.courses_lead') }}</p>
            </div>
            <div role="search">
                <label for="dir-search" class="field-label">Search courses</label>
                <div class="relative">
                    <span class="pointer-events-none absolute top-1/2 left-4 -translate-y-1/2 text-ink-muted"><x-icon name="search" class="size-5" /></span>
                    <input id="dir-search" type="search" class="input h-14 pl-12 text-base" placeholder="e.g. RHCSA, CCNA, Kubernetes" x-model.debounce.200ms="q" value="{{ request('q') }}" autocomplete="off">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-tight" aria-label="Course results">
    <div class="container-page lg:grid lg:grid-cols-[16rem_1fr] lg:gap-10">
        <div class="fixed inset-0 z-[60] lg:static lg:z-auto" x-show="filtersOpen || isLg" x-cloak>
            <div class="absolute inset-0 bg-navy-900/50 lg:hidden" @click="filtersOpen = false"></div>
            <aside aria-label="Filters" class="absolute inset-y-0 left-0 flex w-[min(22rem,90vw)] flex-col overflow-y-auto bg-white p-5 shadow-panel lg:static lg:w-auto lg:overflow-visible lg:p-0 lg:shadow-none" x-trap.noscroll="filtersOpen && !isLg" @keydown.escape="filtersOpen = false">
                <div class="flex items-center justify-between lg:hidden">
                    <h2 class="text-h3">Filters</h2>
                    <button type="button" class="btn btn-ghost size-11 p-0" @click="filtersOpen = false" aria-label="Close filters"><x-icon name="x" class="size-5" /></button>
                </div>
                <div class="lg:sticky lg:top-36">
                    <div class="flex items-center justify-between py-2">
                        <p class="hidden font-display font-semibold text-navy-700 lg:block">Filter courses</p>
                        <button type="button" class="text-sm font-semibold text-action hover:underline" x-show="activeCount" @click="clear()">Clear all</button>
                    </div>
                    <fieldset class="mt-4 border-t border-line pt-4">
                        <legend class="mb-3 text-sm font-semibold text-navy-700">Training track</legend>
                        <div class="grid gap-1">
                            @foreach ($categories as $cat)
                                <label class="flex min-h-10 cursor-pointer items-center gap-3 rounded-md px-2 text-[0.9375rem] hover:bg-surface">
                                    <input type="checkbox" class="check" value="{{ $cat->key }}" x-model="cats">
                                    <span class="flex-1">{{ $cat->name }}</span><span class="text-xs text-ink-muted">{{ $cat->published_courses_count }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <fieldset class="mt-5 border-t border-line pt-4">
                        <legend class="mb-3 text-sm font-semibold text-navy-700">Skill level</legend>
                        <div class="flex flex-wrap gap-2">@foreach (\App\Models\Course::LEVELS as $l)<label class="chip"><input type="checkbox" class="sr-only" value="{{ $l }}" x-model="levels">{{ $l }}</label>@endforeach</div>
                    </fieldset>
                    <fieldset class="mt-5 border-t border-line pt-4">
                        <legend class="mb-3 text-sm font-semibold text-navy-700">Training mode</legend>
                        <div class="flex flex-wrap gap-2">@foreach (\App\Models\Course::MODES as $m)<label class="chip"><input type="checkbox" class="sr-only" value="{{ $m }}" x-model="modes">{{ $m }}</label>@endforeach</div>
                    </fieldset>
                    <div class="on-dark mt-6 rounded-lg bg-navy-700 p-5 text-white">
                        <p class="font-display font-semibold">Can't decide?</p>
                        <p class="mt-1 text-sm text-navy-100">Get a recommendation based on your goals.</p>
                        <a href="/course-finder/" class="btn btn-primary btn-sm mt-4"><x-icon name="compass" class="size-4" /> Course Finder</a>
                    </div>
                </div>
                <button type="button" class="btn btn-navy btn-block mt-6 lg:hidden" @click="filtersOpen = false">Show <span x-text="visible"></span> courses</button>
            </aside>
        </div>

        <div class="min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-line pb-4">
                <p class="text-[0.9375rem] text-ink-muted" aria-live="polite"><strong class="text-navy-700" x-text="visible">{{ $courses->count() }}</strong> <span x-text="visible === 1 ? 'course' : 'courses'">courses</span><span x-show="activeCount" x-cloak> match your filters</span></p>
                <div class="flex items-center gap-2">
                    <button type="button" class="btn btn-outline btn-sm lg:hidden" @click="filtersOpen = true"><x-icon name="sliders-horizontal" class="size-4" /> Filters <span x-show="activeCount" x-cloak class="rounded-full bg-sun-400 px-1.5 text-xs text-navy-900" x-text="activeCount"></span></button>
                    <label for="sort" class="sr-only">Sort courses</label>
                    <select id="sort" class="input min-h-10 w-auto py-0 text-sm" x-model="sort">
                        <option value="featured">Sort: Most popular</option>
                        <option value="level">Sort: Level (beginner first)</option>
                        <option value="az">Sort: A–Z</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2" x-show="activeCount" x-cloak>
                <template x-if="q"><button type="button" class="badge badge-navy min-h-8 gap-1.5 px-3" @click="q = ''"><span x-text="'“' + q + '”'"></span><x-icon name="x" class="size-3.5" /><span class="sr-only">Remove search</span></button></template>
                <template x-for="c in cats" :key="c"><button type="button" class="badge badge-navy min-h-8 gap-1.5 px-3" @click="toggle('cats', c)"><span x-text="names[c]"></span><x-icon name="x" class="size-3.5" /><span class="sr-only">Remove filter</span></button></template>
                <template x-for="l in levels" :key="l"><button type="button" class="badge badge-navy min-h-8 gap-1.5 px-3" @click="toggle('levels', l)"><span x-text="l"></span><x-icon name="x" class="size-3.5" /><span class="sr-only">Remove filter</span></button></template>
                <template x-for="m in modes" :key="m"><button type="button" class="badge badge-navy min-h-8 gap-1.5 px-3" @click="toggle('modes', m)"><span x-text="m"></span><x-icon name="x" class="size-3.5" /><span class="sr-only">Remove filter</span></button></template>
            </div>

            <ul x-ref="grid" class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($courses as $c)<li><x-course-card :course="$c" :heading-level="2" /></li>@endforeach
            </ul>

            <div x-show="visible === 0" x-cloak class="mt-6 rounded-xl border border-dashed border-line-strong bg-surface px-6 py-14 text-center">
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-white text-action shadow-card"><x-icon name="search" class="size-6" /></span>
                <h2 class="mt-5 text-h3">No courses match those filters</h2>
                <p class="mx-auto mt-2 max-w-md text-ink-muted">Try removing a filter or searching for a certification code. Our advisors can also tell you about upcoming courses not listed yet.</p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <button type="button" class="btn btn-navy" @click="clear()">Clear all filters</button>
                    <button type="button" class="btn btn-outline" @click="$dispatch('open-enquiry', {})">Ask an advisor</button>
                </div>
            </div>

            <nav aria-label="Pagination" class="mt-10 flex flex-col items-center gap-3" x-show="visible > perPage" x-cloak>
                <div class="h-1.5 w-48 overflow-hidden rounded-full bg-surface-2"><div class="h-full rounded-full bg-action transition-[width]" :style="'width:' + Math.min(100, page * perPage / visible * 100) + '%'"></div></div>
                <p class="text-sm text-ink-muted">Showing <span x-text="Math.min(page * perPage, visible)"></span> of <span x-text="visible"></span></p>
                <button type="button" class="btn btn-outline" x-show="page < pages" @click="loadMore()" :disabled="loading">
                    <span x-show="!loading">Load more courses</span>
                    <span x-show="loading" class="inline-flex items-center gap-2"><x-icon name="loader-circle" class="size-5 animate-spin" /> Loading…</span>
                </button>
            </nav>
        </div>
    </div>
</section>

<x-cta-band title="Training a group or a team?" text="Train together in a private batch, or ask about customised programmes for your organisation.">
    <a href="/group-training/" class="btn btn-primary btn-lg">Group training</a>
    <button type="button" class="btn btn-ghost-light btn-lg" @click="$dispatch('open-enquiry', { audience: 'Group' })">Talk to an advisor</button>
</x-cta-band>
</div>
@endsection
