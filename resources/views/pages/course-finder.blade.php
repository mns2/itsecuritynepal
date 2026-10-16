@extends('layouts.app')
@section('title', 'Course Finder: Find the Right IT Course for You | IT Security Nepal')
@section('description', 'Answer four quick questions about your interests, experience and goals to get a recommended IT training path from IT Security Nepal.')

@php
    $qs = [
        ['key' => 'interest', 'label' => 'Interest', 'title' => 'What do you want to learn?', 'hint' => 'Pick the area that excites you most.', 'options' => [
            ['linux', 'terminal', 'Linux & servers', 'Run and automate enterprise Linux systems'],
            ['security', 'shield-check', 'Cybersecurity', 'Defend systems, hunt threats, ethical hacking'],
            ['network', 'network', 'Networking', 'Routers, switches and enterprise networks'],
            ['devops', 'workflow', 'DevOps & Kubernetes', 'CI/CD, containers and automation'],
            ['cloud', 'cloud', 'Cloud (Azure)', 'Administer and design cloud infrastructure'],
            ['unsure', 'compass', 'Not sure yet', 'Help me find a good starting point']]],
        ['key' => 'experience', 'label' => 'Experience', 'title' => 'What is your current experience?', 'hint' => 'Be honest: we will match the starting level.', 'options' => [
            ['beginner', 'sprout', 'New to IT', 'Basic computer skills, no professional IT experience'],
            ['some', 'signal-medium', 'Some experience', 'Studying IT or working in support / junior roles'],
            ['experienced', 'signal-high', 'Experienced professional', 'Working in IT and want advanced skills or certification']]],
        ['key' => 'goal', 'label' => 'Goal', 'title' => 'What is your main goal?', 'hint' => 'This shapes what we recommend first.', 'options' => [
            ['job', 'briefcase-business', 'Get my first IT job', 'Job-ready skills and a recognised certificate'],
            ['certify', 'badge-check', 'Earn a certification', 'Prepare for a specific exam'],
            ['upskill', 'trending-up', 'Grow in my current role', 'Move up or switch specialisation'],
            ['abroad', 'plane', 'Work abroad', 'Prepare for roles in Australia or New Zealand']]],
        ['key' => 'mode', 'label' => 'Learning mode', 'title' => 'How do you prefer to learn?', 'hint' => 'All modes use the same instructors and labs.', 'options' => [
            ['Classroom', 'presentation', 'Classroom', 'At our New Baneshwor centre'],
            ['Live Online', 'monitor-play', 'Live online', 'Real-time classes from anywhere'],
            ['One-on-One', 'user-round', 'One-on-one', 'Personal trainer, flexible schedule']]],
    ];
@endphp

@section('content')
<section class="bg-grid-light">
    <div class="container-page py-8 lg:py-12">
        <x-breadcrumbs :items="[['name' => 'Course Finder']]" />
        <div class="mx-auto mt-8 max-w-3xl" x-data="courseFinder">
            <div class="text-center">
                <p class="text-eyebrow text-action">Course Finder</p>
                <h1 class="mt-3 text-h1">Find the right course in 4 questions</h1>
                <p class="mt-3 text-lead text-ink-muted">Get a personalised recommendation from our catalogue. It takes under a minute.</p>
            </div>
            <div class="mt-10 rounded-xl border border-line bg-white p-5 shadow-card sm:p-8">
                <div class="flex items-center justify-between text-sm font-semibold">
                    <span class="text-navy-700" x-text="step < steps ? 'Question ' + (step + 1) + ' of ' + steps : 'Your recommendation'">Question 1 of 4</span>
                    <span class="text-ink-muted" x-text="progress + '%'">0%</span>
                </div>
                <div class="mt-3 h-2 overflow-hidden rounded-full bg-surface-2" role="progressbar" aria-label="Course Finder progress" :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
                    <div class="h-full rounded-full bg-sun-400 transition-[width] duration-300" :style="'width:' + progress + '%'" style="width:0%"></div>
                </div>
                <ol class="mt-4 hidden grid-cols-4 gap-2 sm:grid" aria-hidden="true">
                    @foreach ($qs as $q)<li class="text-xs font-medium" :class="step > {{ $loop->index }} ? 'text-success-600' : (step === {{ $loop->index }} ? 'text-navy-700' : 'text-ink-subtle')">{{ $q['label'] }}</li>@endforeach
                </ol>

                @foreach ($qs as $q)
                    <fieldset class="mt-8" x-show="step === {{ $loop->index }}" @unless ($loop->first) x-cloak @endunless>
                        <legend class="contents"><h2 class="text-h3 outline-none" tabindex="-1" data-step-heading :hidden="step !== {{ $loop->index }}">{{ $q['title'] }}</h2></legend>
                        <p class="mt-1 text-ink-muted">{{ $q['hint'] }}</p>
                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            @foreach ($q['options'] as [$value, $icon, $label, $desc])
                                <label class="option-card">
                                    <input type="radio" name="cf-{{ $q['key'] }}" value="{{ $value }}" class="sr-only" x-model="answers.{{ $q['key'] }}" @change="error = ''">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-md bg-surface text-action"><x-icon :name="$icon" class="size-5" /></span>
                                    <span><span class="block font-semibold text-navy-700">{{ $label }}</span><span class="block text-sm text-ink-muted">{{ $desc }}</span></span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach

                <p x-show="error" x-cloak role="alert" tabindex="-1" class="field-error mt-4"><x-icon name="circle-alert" class="size-4" /><span x-text="error"></span></p>

                <div class="mt-8 flex items-center justify-between gap-3 border-t border-line pt-6" x-show="step < steps">
                    <button type="button" class="btn btn-ghost" @click="back()" :disabled="step === 0"><x-icon name="arrow-left" class="size-5" /> Back</button>
                    <button type="button" class="btn btn-navy" @click="next()"><span x-text="step === steps - 1 ? 'See my recommendation' : 'Next'">Next</span> <x-icon name="arrow-right" class="size-5" /></button>
                </div>

                <div x-show="step === steps" x-cloak class="mt-8" aria-live="polite">
                    <template x-if="loading"><p class="flex items-center justify-center gap-3 py-12 text-ink-muted"><x-icon name="loader-circle" class="size-6 animate-spin" /> Matching courses…</p></template>
                    <div x-show="!loading">
                        <h2 class="text-h3 outline-none" tabindex="-1" data-step-heading>Your recommended path</h2>
                        <p class="mt-1 text-ink-muted">Based on your answers, start here and continue in order. Available as <strong x-text="answers.mode"></strong>, for individuals or groups.</p>
                        <ol class="mt-6 grid gap-4">
                            <template x-for="(c, i) in results" :key="c.slug">
                                <li class="relative flex flex-col gap-4 rounded-lg border-2 p-5 sm:flex-row sm:items-center" :class="i === 0 ? 'border-sun-400 bg-sun-50' : 'border-line'">
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full font-display font-bold" :class="i === 0 ? 'bg-sun-400 text-navy-900' : 'bg-surface text-action'" x-text="i + 1"></span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-semibold tracking-wide uppercase" :class="i === 0 ? 'text-sun-700' : 'text-ink-muted'" x-text="c.why"></p>
                                        <a :href="c.url" class="mt-1 block font-display text-lg font-semibold text-navy-700 no-underline hover:text-action"><span class="font-display text-sm font-extrabold text-action" x-text="c.code + ' · '"></span><span x-text="c.name"></span></a>
                                        <p class="mt-1 line-clamp-2 text-sm text-ink-muted" x-text="c.summary"></p>
                                    </div>
                                    <a :href="c.url" class="btn btn-outline btn-sm shrink-0">View course</a>
                                </li>
                            </template>
                        </ol>
                        <div class="on-dark mt-8 flex flex-col gap-4 rounded-lg bg-navy-700 p-6 text-white sm:flex-row sm:items-center sm:justify-between">
                            <div><p class="font-display font-semibold">Talk it through with an advisor</p><p class="text-sm text-navy-100">Confirm the right start, the next batch and fees.</p></div>
                            <button type="button" class="btn btn-primary shrink-0" @click="$dispatch('open-enquiry', { course: results[0]?.slug })">Contact an advisor</button>
                        </div>
                        <button type="button" class="btn btn-ghost mt-4" @click="restart()"><x-icon name="refresh-ccw" class="size-4" /> Start again</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
