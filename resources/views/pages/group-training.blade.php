@extends('layouts.app')
@section('title', 'Group IT Training in Kathmandu | Private Batches | IT Security Nepal')
@section('description', 'Train together in a private batch: Red Hat, cybersecurity, CCNA, DevOps and cloud courses for groups of friends, classmates or teams, in Kathmandu or live online.')

@php($g = collect($site['audiences'] ?? [])->firstWhere('id', 'group') ?? [])

@section('content')
<section class="bg-grid-light">
    <div class="container-page grid gap-10 pt-8 pb-14 lg:grid-cols-[1.25fr_1fr] lg:items-center lg:pb-20">
        <div>
            <x-breadcrumbs :items="[['name' => 'Group training']]" />
            <p class="badge badge-solid mt-8 gap-1.5 py-1.5"><x-icon name="users" class="size-3.5" />Training for groups</p>
            <h1 class="mt-4 text-h1">Group training: learn together in a private batch</h1>
            <p class="mt-4 max-w-2xl text-lead text-ink-muted">{{ $g['body'] ?? '' }}</p>
            <div class="mt-7 flex flex-wrap gap-2.5">
                <a href="#plan" class="btn btn-primary btn-lg">Plan your group training</a>
                @if (! empty($site['contact']['phonePrimary']))<a href="tel:{{ $site['contact']['phonePrimary']['tel'] }}" class="btn btn-outline btn-lg"><x-icon name="phone" class="size-5" /> {{ $site['contact']['phonePrimary']['label'] }}</a>@endif
            </div>
        </div>
        <ul class="grid gap-3">
            @foreach ([['users', 'Any group', 'Friends, classmates, colleagues or a whole team from your organisation.'], ['calendar-days', 'Your schedule', 'Batch days and times agreed with the group.'], ['map-pin', 'Your location', 'Our New Baneshwor centre, on-site at your office, or live online.'], ['badge-check', 'Same quality', 'The same certified instructors, labs and certification preparation.']] as [$ic, $t, $d])
                <li class="flex gap-4 rounded-lg border border-line bg-white p-5"><span class="ic-tile"><x-icon :name="$ic" class="size-5" /></span><div><p class="font-display font-bold text-navy-700">{{ $t }}</p><p class="text-sm text-ink-muted">{{ $d }}</p></div></li>
            @endforeach
        </ul>
    </div>
</section>

<section class="section" aria-labelledby="g-compare">
    <div class="container-page">
        <x-section-heading eyebrow="Individual or group?" title="Choose how you want to train" align="center" id="g-compare" />
        <div class="mx-auto mt-8 max-w-4xl overflow-x-auto rounded-lg border border-line">
            <table class="w-full min-w-[34rem] text-[0.9375rem]">
                <thead class="bg-surface text-left"><tr><th class="px-5 py-3.5"><span class="sr-only">Feature</span></th><th class="px-5 py-3.5 font-display text-navy-700">Individual</th><th class="px-5 py-3.5 font-display text-navy-700">Group</th></tr></thead>
                <tbody>
                    @foreach ([['Batch', 'Join a public batch (or one-on-one)', 'Private batch for your group'], ['Schedule', 'Fixed batch timings', 'Agreed with your group'], ['Location', 'Centre or live online', 'Centre, on-site or live online'], ['Curriculum', 'Standard course outline', 'Standard outline; focus areas can be adjusted'], ['Best for', 'Individual learners and job seekers', 'Friends, classmates, colleagues and teams']] as [$k, $a, $b])
                        <tr class="border-t border-line"><th scope="row" class="px-5 py-3.5 text-left font-semibold text-navy-700">{{ $k }}</th><td class="px-5 py-3.5 text-ink-muted">{{ $a }}</td><td class="px-5 py-3.5 text-ink-muted">{{ $b }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

@if ($popular->isNotEmpty())
<section class="section bg-surface" aria-labelledby="g-popular">
    <div class="container-page">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <x-section-heading eyebrow="Popular for groups" title="Courses groups often take together" id="g-popular" />
            <a href="/course/" class="link-arrow">Any course can run as a group <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">@foreach ($popular as $c)<li><x-course-card :course="$c" /></li>@endforeach</ul>
    </div>
</section>
@endif

<section id="plan" class="section scroll-mt-40" aria-labelledby="g-plan">
    <div class="container-page grid gap-12 lg:grid-cols-[1fr_1.2fr]">
        <div>
            <x-section-heading eyebrow="Plan your batch" title="Tell us about your group" intro="Share the course, how many people and your preferred timing. We will suggest a schedule and send the details." id="g-plan" />
            <div class="mt-8"><x-faq-list :faqs="[
                ['q' => 'How many people make a group?', 'a' => 'Group size and any group terms are confirmed by our advisors when you enquire.'],
                ['q' => 'Can a company book group training?', 'a' => 'Yes. Teams from organisations can train as a private group at our centre, on-site or online. See also corporate training for customised programmes.'],
                ['q' => 'Can we choose the timing?', 'a' => 'Yes. Group batches are scheduled around the group, subject to instructor availability.'],
            ]" /></div>
        </div>
        <div class="card p-6 sm:p-8">
            <h2 class="text-h3">Group training enquiry</h2>
            <p class="mt-1 mb-6 text-sm text-ink-muted">An advisor will contact you within one working day.</p>
            <x-enquiry-form id="group-enquiry" audience="Group" />
        </div>
    </div>
</section>
@endsection
