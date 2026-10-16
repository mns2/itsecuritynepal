@extends('layouts.app')
@section('title', 'Upcoming Training Batches & Schedule | IT Security Nepal')
@section('description', 'Upcoming IT training batches at IT Security Nepal in Kathmandu: Linux Bootcamp, RHCSA, DevOps, ethical hacking and more. Check schedules and reserve a seat.')

@section('content')
<section class="bg-grid-light">
    <div class="container-page pt-8 pb-12">
        <x-breadcrumbs :items="[['name' => 'Upcoming batches']]" />
        <div class="mt-8 max-w-2xl">
            <h1 class="text-h1">Upcoming batches</h1>
            <p class="mt-4 text-lead text-ink-muted">Seats are limited so everyone gets real lab time. Reserve a seat, ask us to notify you when a new batch opens, or request a private batch for your group.</p>
        </div>
    </div>
</section>

<section class="section-tight" x-data="listFilter('[data-batch]')">
    <div class="container-page">
        @if ($batches->isEmpty())
            <div class="rounded-xl border border-dashed border-line-strong bg-surface p-10 text-center">
                <p class="font-semibold text-navy-700">New batches are being scheduled</p>
                <button type="button" class="btn btn-outline btn-sm mt-4" @click="$dispatch('open-enquiry', {})">Notify me of the next batch</button>
            </div>
        @else
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="no-scrollbar flex gap-2 overflow-x-auto pb-1" role="group" aria-label="Filter batches by track">
                    <button type="button" class="chip shrink-0" :aria-pressed="active === 'all'" @click="active = 'all'">All tracks</button>
                    @foreach ($categories as $cat)<button type="button" class="chip shrink-0" :aria-pressed="active === '{{ $cat->key }}'" @click="active = '{{ $cat->key }}'">{{ $cat->short_name }}</button>@endforeach
                </div>
                <p class="text-sm text-ink-muted" aria-live="polite"><strong class="text-navy-700" x-text="count">{{ $batches->count() }}</strong> batches</p>
            </div>
            <div class="mt-6"><x-batch-list :batches="$batches" /></div>
            <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm text-ink-muted" aria-label="Status legend">
                <li class="flex items-center gap-2"><span class="badge badge-success">Enrolling</span> seats available</li>
                <li class="flex items-center gap-2"><span class="badge badge-sun">Few seats left</span> book soon</li>
                <li class="flex items-center gap-2"><span class="badge badge-navy">Opening soon</span> date to be confirmed</li>
                <li class="flex items-center gap-2"><span class="badge badge-muted">Waitlist</span> batch full</li>
            </ul>
        @endif
    </div>
</section>

<x-cta-band title="Can't find your course?" text="Most courses run on demand, and groups can book a private batch. Tell us what you want to learn.">
    <button type="button" class="btn btn-primary btn-lg" @click="$dispatch('open-enquiry', {})">Ask about a batch</button>
    <a href="/group-training/" class="btn btn-ghost-light btn-lg">Group training</a>
</x-cta-band>
@endsection
