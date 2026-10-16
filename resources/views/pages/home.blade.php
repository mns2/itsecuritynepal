@extends('layouts.app')
@section('title', 'IT Training Institute in Kathmandu | Red Hat, Cybersecurity, CCNA & DevOps | IT Security Nepal')
@section('description', 'Hands-on IT training in Kathmandu from a Red Hat Authorized Training Partner. RHCSA, RHCE, ethical hacking, CCNA, Kubernetes and Azure courses for individuals and groups: classroom or live online.')

@section('content')
@php
    $pop = fn ($slug) => $featured[$slug] ?? null;
    $contact = $site['contact'] ?? [];
@endphp

{{-- HERO (search-led) --}}
<section class="bg-grid-light" aria-labelledby="hero-title">
    <div class="container-page grid gap-10 pt-10 pb-14 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:gap-12 lg:pt-16 lg:pb-[4.5rem]">
        <div>
            <p class="badge badge-solid gap-1.5 py-1.5"><x-icon name="award" class="size-3.5" />Red Hat Authorized Training Partner · 15+ years</p>
            <h1 id="hero-title" class="mt-5 text-display">Build your future in IT with <span class="text-action">industry-driven training</span></h1>
            <p class="mt-4 max-w-[56ch] text-[1.0625rem] text-ink-muted">Develop practical skills in cybersecurity, Linux, networking, cloud and DevOps through hands-on, certification-focused training, in Kathmandu or live online.</p>
            <form action="/course/" method="get" role="search" class="mt-7 flex max-w-[37.5rem] gap-2 rounded-lg border-[1.5px] border-line bg-white p-2 shadow-[0_16px_36px_-24px_rgb(0_50_117/0.45)] focus-within:border-action">
                <label class="flex flex-1 items-center gap-2.5 pl-2.5 text-ink-muted">
                    <x-icon name="search" class="size-5 shrink-0" /><span class="sr-only">Search courses</span>
                    <input name="q" type="search" class="h-11 min-w-0 flex-1 bg-transparent text-[0.9375rem] text-ink outline-none placeholder:text-ink-subtle" placeholder="What do you want to learn?" autocomplete="off">
                </label>
                <button type="submit" class="btn btn-primary shrink-0"><span class="hidden sm:inline">Explore Courses</span><span class="sm:hidden">Search</span></button>
            </form>
            <div class="mt-3.5 flex flex-wrap items-center gap-2 text-[0.8125rem] text-ink-muted">
                <span>Popular:</span>
                @foreach (['red-hat-system-administration-ii' => 'RHCSA', 'ethical-hacking-expert' => 'Ethical Hacking', 'cisco-certified-network-associate' => 'CCNA', 'certified-kubernetes-administrator' => 'CKA', 'microsoft-azure-administrator' => 'AZ-104'] as $slug => $label)
                    @if ($c = $pop($slug))<a href="{{ $c->url() }}" class="rounded-full border border-line bg-white px-3 py-1.5 font-semibold text-navy-700 no-underline hover:border-action hover:text-action">{{ $label }}</a>@endif
                @endforeach
            </div>
            <div class="mt-7 flex flex-wrap gap-2.5">
                <button type="button" class="btn btn-outline" @click="$dispatch('open-enquiry', {})"><x-icon name="message-circle" class="size-[18px]" /> Talk to a Training Advisor</button>
                <a href="/course-finder/" class="btn btn-ghost"><x-icon name="compass" class="size-[18px]" /> Not sure? Try the Course Finder</a>
            </div>
        </div>
        <div class="relative grid min-h-[25rem] grid-cols-[1.2fr_1fr] grid-rows-2 gap-3">
            {{-- Replace with real photography: storage/app/public/home/*.jpg (see docs/DEPLOYMENT.md) --}}
            <figure class="photo-ph row-span-2 place-items-start rounded-[14px]" role="img" aria-label="Classroom session (photo to be supplied)"><span><x-icon name="users" class="size-3.5" />Photo: classroom session</span></figure>
            <figure class="photo-ph rounded-[14px]" role="img" aria-label="Linux lab (photo to be supplied)"><span>Photo: Linux lab</span></figure>
            <figure class="photo-ph rounded-[14px]" role="img" aria-label="Certification day (photo to be supplied)"><span>Photo: certification day</span></figure>
            @if ($bootcamp = $pop('linux-bootcamp'))
                <a href="{{ $bootcamp->url() }}" class="absolute -bottom-5 -left-2 flex max-w-[18rem] items-center gap-3 rounded-lg border border-line bg-white px-4 py-3.5 no-underline shadow-panel sm:-left-4">
                    <span class="grid size-[42px] shrink-0 place-items-center rounded-[10px] bg-sun-400 text-navy-700"><x-icon name="calendar-days" class="size-5" /></span>
                    <span><span class="block font-display text-sm font-bold text-navy-700">Linux Bootcamp</span><span class="block text-[0.78125rem] text-ink-muted">Mon–Fri, 1–5 PM · New batch every Monday</span></span>
                </a>
            @endif
        </div>
    </div>
</section>

{{-- STATS --}}
<section aria-label="Key facts" class="border-y border-line bg-white">
    <ul class="container-page grid grid-cols-2 lg:grid-cols-4">
        @foreach (collect($site['trust'] ?? [])->reject(fn ($t) => ! empty($t['verify']) && empty($t['verified'])) as $t)
            <li @class(['flex items-center gap-3.5 border-line px-2 py-5 sm:px-4', 'border-l' => $loop->index % 2 === 1, 'lg:border-l' => $loop->index > 0])>
                <span class="ic-tile"><x-icon :name="$t['icon']" class="size-5" /></span>
                <div>
                    <p class="font-display text-[1.625rem] leading-none font-extrabold tracking-tight text-navy-700">{{ $t['value'] }}</p>
                    <p class="mt-1 text-[0.8125rem] text-ink-muted">{{ $t['label'] }}</p>
                </div>
            </li>
        @endforeach
        <li class="flex items-center gap-3.5 border-l border-line px-2 py-5 sm:px-4">
            <span class="ic-tile"><x-icon name="users" class="size-5" /></span>
            <div><p class="font-display text-[1.625rem] leading-none font-extrabold tracking-tight text-navy-700">2 ways</p><p class="mt-1 text-[0.8125rem] text-ink-muted">to train: individually or as a group</p></div>
        </li>
    </ul>
</section>

{{-- COURSES BY TRACK --}}
<section class="section" aria-labelledby="tracks-title" x-data="listFilter('[data-home-course]', 8)">
    <div class="container-page">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <x-section-heading eyebrow="Course categories" title="Explore courses by track" intro="Pick a track to filter the catalogue, or browse everything." id="tracks-title" />
            <a href="/course/" class="link-arrow">View all {{ $courses->count() }} courses <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6" role="group" aria-label="Filter by track">
            @foreach ($homeTracks as $cat)
                <button type="button" class="track-tile" :aria-pressed="active === '{{ $cat->key }}'" @click="set('{{ $cat->key }}')">
                    <span class="ic"><x-icon :name="$cat->icon" class="size-5" /></span><b>{{ $cat->name }}</b><small>{{ $cat->published_courses_count }} courses</small>
                </button>
            @endforeach
        </div>
        <div class="no-scrollbar mt-9 mb-5 flex gap-2 overflow-x-auto pb-1" role="group" aria-label="Filter courses">
            <button type="button" class="chip shrink-0" :aria-pressed="active === 'all'" @click="active = 'all'">All</button>
            @foreach ($categories as $cat)<button type="button" class="chip shrink-0" :aria-pressed="active === '{{ $cat->key }}'" @click="active = '{{ $cat->key }}'">{{ $cat->short_name }}</button>@endforeach
        </div>
        <p class="sr-only" aria-live="polite" x-text="count + ' of ' + total + ' courses shown'"></p>
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($courses as $c)
                <li data-home-course data-category="{{ $c->category->key }}" @if ($loop->index >= 8) hidden @endif><x-course-card :course="$c" /></li>
            @endforeach
        </ul>
        <div class="mt-8 flex justify-center">
            <a :href="active === 'all' ? '/course/' : '/course/?category=' + active" href="/course/" class="btn btn-outline">
                <span x-text="total > count ? 'See all ' + total + ' courses' : 'Browse the full catalogue'">Browse the full catalogue</span> <x-icon name="arrow-right" class="size-[18px]" />
            </a>
        </div>
    </div>
</section>

{{-- TRAINING FOR: INDIVIDUAL / GROUP --}}
<section class="section bg-surface" aria-labelledby="who-title">
    <div class="container-page">
        <x-section-heading eyebrow="Training for" title="Learn on your own or as a group" intro="Every course is available to individual learners and to groups such as classmates, friends or a whole team." id="who-title" />
        <ul class="mt-8 grid gap-4 md:grid-cols-2">
            @foreach ($site['audiences'] ?? [] as $a)
                <li class="flex flex-col gap-5 rounded-xl border border-line bg-white p-6 sm:flex-row sm:p-7">
                    <span @class(['grid size-14 shrink-0 place-items-center rounded-xl', 'bg-navy-700 text-sun-400' => $loop->first, 'bg-sun-400 text-navy-700' => ! $loop->first])><x-icon :name="$a['icon']" class="size-7" /></span>
                    <div class="flex flex-1 flex-col">
                        <h3 class="text-h3">{{ $a['title'] }}</h3>
                        <p class="mt-1.5 text-ink-muted">{{ $a['body'] }}</p>
                        <ul class="mt-4 grid gap-2 text-[0.9375rem]">@foreach ($a['points'] as $pt)<li class="flex gap-2.5"><x-icon name="check" class="mt-0.5 size-[18px] shrink-0 text-success-600" />{{ $pt }}</li>@endforeach</ul>
                        <div class="mt-6 flex flex-wrap gap-2.5">
                            <a href="{{ $a['url'] }}" @class(['btn', 'btn-primary' => $loop->first, 'btn-sun' => ! $loop->first])>{{ $a['cta'] }}</a>
                            <button type="button" class="btn btn-outline" @click="$dispatch('open-enquiry', { audience: '{{ $loop->first ? 'Individual' : 'Group' }}' })">Enquire</button>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- FEATURED --}}
@php($fw = $pop('red-hat-system-administration'))
@if ($fw)
<section class="section" aria-labelledby="feat-title">
    <div class="container-page">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <x-section-heading eyebrow="Featured courses" title="Popular right now" id="feat-title" />
            <button type="button" class="link-arrow" @click="$dispatch('open-enquiry', {})">Ask about the next batch <x-icon name="arrow-right" class="size-4" /></button>
        </div>
        <div class="grid gap-4 lg:grid-cols-[1.3fr_1fr] lg:grid-rows-2">
            <article class="card card-interactive group relative grid overflow-hidden rounded-xl lg:row-span-2">
                <figure class="photo-ph min-h-[16rem]" role="img" aria-label="Red Hat lab session (photo to be supplied)"><span><x-icon name="terminal" class="size-3.5" />Photo: Red Hat lab session</span></figure>
                <div class="grid content-start gap-2.5 p-6">
                    <span class="badge w-fit bg-sun-100 text-sun-700"><x-icon name="award" class="size-3.5" />Official Red Hat training</span>
                    <h3 class="text-[1.3125rem] leading-tight font-extrabold"><a href="{{ $fw->url() }}" class="stretched-link text-navy-700 no-underline group-hover:text-action">{{ $fw->code }} · {{ $fw->name }}</a></h3>
                    <p class="text-[0.90625rem] text-ink-muted">{{ $fw->summary }}</p>
                    <ul class="flex flex-wrap gap-2">
                        <li class="inline-flex items-center gap-1.5 rounded-md bg-surface px-2.5 py-1.5 text-[0.8125rem] font-semibold text-navy-700"><x-icon name="badge-check" class="size-4 text-action" />{{ $fw->certification }}</li>
                        <li class="inline-flex items-center gap-1.5 rounded-md bg-surface px-2.5 py-1.5 text-[0.8125rem] font-semibold text-navy-700"><x-icon name="monitor-play" class="size-4 text-action" />Classroom · Live online</li>
                    </ul>
                    <div class="relative z-10 mt-1 flex flex-wrap gap-2.5"><a href="{{ $fw->url() }}" class="btn btn-primary">View course</a><button type="button" class="btn btn-outline" @click="$dispatch('open-enquiry', { course: '{{ $fw->slug }}' })">Enquire</button></div>
                </div>
            </article>
            @foreach ([['junior-security-analyst-track', 'Photo: SOC lab', 'Cybersecurity', '80% practical'], ['linux-bootcamp', 'Photo: bootcamp', 'Bootcamp', 'Mon–Fri 1–5 PM']] as [$slug, $ph, $badge, $extra])
                @if ($c = $pop($slug))
                    <article class="card card-interactive group relative grid overflow-hidden rounded-xl sm:grid-cols-[12.5rem_1fr]">
                        <figure class="photo-ph min-h-[10.5rem]" role="img" aria-label="{{ $ph }} (to be supplied)"><span>{{ $ph }}</span></figure>
                        <div class="grid content-start gap-2.5 p-5">
                            <span class="badge badge-navy w-fit">{{ $badge }}</span>
                            <h3 class="text-[1.125rem] leading-tight font-extrabold"><a href="{{ $c->url() }}" class="stretched-link text-navy-700 no-underline group-hover:text-action">{{ $c->name }}</a></h3>
                            <ul class="flex flex-wrap gap-2">
                                @if ($c->duration)<li class="inline-flex items-center gap-1.5 rounded-md bg-surface px-2.5 py-1.5 text-[0.8125rem] font-semibold text-navy-700"><x-icon name="calendar-days" class="size-4 text-action" />{{ $c->duration }}</li>@endif
                                <li class="inline-flex items-center gap-1.5 rounded-md bg-surface px-2.5 py-1.5 text-[0.8125rem] font-semibold text-navy-700"><x-icon name="flask-conical" class="size-4 text-action" />{{ $extra }}</li>
                            </ul>
                            <span class="link-arrow text-sm" aria-hidden="true">View course →</span>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- HOW WE TRAIN --}}
<section class="section bg-surface" aria-labelledby="journey-title">
    <div class="container-page grid gap-9 lg:grid-cols-2 lg:items-center lg:gap-14">
        <figure class="photo-ph min-h-[18rem] rounded-xl lg:min-h-[26rem]" role="img" aria-label="Instructor guiding students in the lab (photo to be supplied)"><span><x-icon name="users" class="size-3.5" />Photo: instructor guiding students in the lab</span></figure>
        <div>
            <x-section-heading eyebrow="How we train" title="From first enquiry to certified professional" id="journey-title" />
            <ul class="mt-4 mb-6 flex flex-wrap gap-2">
                @foreach ([['flask-conical', 'Practical learning'], ['graduation-cap', 'Expert guidance'], ['target', 'Career-focused skills']] as [$ic, $label])<li class="inline-flex items-center gap-1.5 rounded-md bg-sun-100 px-3 py-2 font-display text-[0.84375rem] font-bold text-navy-700"><x-icon :name="$ic" class="size-4" />{{ $label }}</li>@endforeach
            </ul>
            <ol class="grid">
                @foreach ([['Free career counselling', 'Talk to an advisor about your background and goals to find the right track, alone or as a group.'], ['Hands-on training', 'Instructor-led classes with practical labs: classroom, live virtual, one-on-one or flip class.'], ['Certification-focused preparation', 'Official courseware and exam-style practice for Red Hat and other certifications.'], ['Job-ready skills', 'Leave ready to apply your skills in real IT roles, with lifetime support from our mentors.']] as [$t, $d])
                    <li class="grid grid-cols-[2.75rem_1fr] gap-x-4 gap-y-1 border-t border-line py-4">
                        <span @class(['row-span-2 grid size-11 place-items-center rounded-full border-2 font-display text-[0.9375rem] font-extrabold', 'border-sun-400 bg-sun-400 text-navy-700' => $loop->last, 'border-action text-action' => ! $loop->last])>{{ $loop->iteration }}</span>
                        <p class="font-display text-[1.0625rem] font-bold text-navy-700">{{ $t }}</p>
                        <p class="text-[0.90625rem] text-ink-muted">{{ $d }}</p>
                    </li>
                @endforeach
            </ol>
            <a href="/mode-of-training/" class="link-arrow mt-3">See all training methods <x-icon name="arrow-right" class="size-4" /></a>
        </div>
    </div>
</section>

{{-- LEARNING PATHS --}}
@if ($paths->isNotEmpty())
<section class="section" aria-labelledby="paths-title" x-data="{ tab: '{{ $paths->first()->key }}' }">
    <div class="container-page">
        <x-section-heading eyebrow="Learning paths" title="Know exactly what to learn next" intro="Pick a career goal to see a proven sequence of courses from our catalogue." id="paths-title" />
        <div class="no-scrollbar mt-7 flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Career paths">
            @foreach ($paths as $p)
                <button type="button" role="tab" id="tab-{{ $p->key }}" aria-controls="panel-{{ $p->key }}" :aria-selected="tab === '{{ $p->key }}'" :aria-pressed="tab === '{{ $p->key }}'" :tabindex="tab === '{{ $p->key }}' ? 0 : -1" @click="tab = '{{ $p->key }}'"
                    @keydown.right.prevent="$el.nextElementSibling?.click(); $el.nextElementSibling?.focus()" @keydown.left.prevent="$el.previousElementSibling?.click(); $el.previousElementSibling?.focus()"
                    class="chip shrink-0"><x-icon :name="$p->icon" class="size-4" />{{ $p->name }}</button>
            @endforeach
        </div>
        @foreach ($paths as $p)
            @php($steps = $p->resolvedSteps())
            <div role="tabpanel" id="panel-{{ $p->key }}" aria-labelledby="tab-{{ $p->key }}" x-show="tab === '{{ $p->key }}'" @unless ($loop->first) x-cloak @endunless class="mt-6">
                <p class="text-ink-muted">Goal: <strong class="text-navy-700">{{ $p->goal }}</strong></p>
                <ol class="mt-5 grid gap-3 md:grid-cols-{{ max(1, min(5, $steps->count())) }}">
                    @foreach ($steps as $s)
                        <li>
                            <a href="{{ $s['course']->url() }}" class="track-tile h-full content-start">
                                <span class="flex items-center gap-2.5"><span @class(['grid size-8 place-items-center rounded-full font-display text-sm font-extrabold', 'bg-sun-400 text-navy-700' => $loop->last, 'bg-navy-700 text-white' => ! $loop->last])>{{ $loop->iteration }}</span><small>{{ $s['note'] }}</small></span>
                                <span class="font-display text-sm font-extrabold text-action">{{ $s['course']->code }}</span>
                                <b>{{ $s['course']->name }}</b>
                            </a>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endforeach
    </div>
</section>
@endif

{{-- UPCOMING BATCHES --}}
@if ($batches->isNotEmpty())
<section class="section bg-surface" aria-labelledby="batches-title">
    <div class="container-page">
        <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
            <x-section-heading eyebrow="Upcoming batches" title="Join the next batch" intro="Seats are limited so every learner gets lab time." id="batches-title" />
            <a href="/batches/" class="link-arrow">Full training calendar <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        <x-batch-list :batches="$batches" />
    </div>
</section>
@endif

{{-- TRUST --}}
<section class="section" aria-labelledby="trust-title">
    <div class="container-page">
        <x-section-heading eyebrow="Trust & credibility" title="Certification tracks we teach" id="trust-title" />
        <ul class="mt-7 grid grid-cols-2 gap-3 md:grid-cols-6">
            @foreach (['Red Hat', 'Microsoft', 'Cisco', 'CompTIA', 'ISC2', 'Kubernetes'] as $v)<li class="grid h-[4.5rem] place-items-center rounded-[10px] border border-line bg-white font-display text-[1.0625rem] font-extrabold tracking-tight text-navy-700">{{ $v }}</li>@endforeach
        </ul>
        <div class="mt-6 grid gap-4 lg:grid-cols-[1.2fr_1fr]">
            <div class="on-dark flex items-center gap-4 rounded-lg bg-navy-700 p-6 text-white">
                <span class="grid size-[3.375rem] shrink-0 place-items-center rounded-lg bg-sun-400 text-navy-700"><x-icon name="award" class="size-6" /></span>
                <div><p class="font-display text-lg leading-snug font-extrabold">Red Hat Authorized Training Partner for 15+ years</p><p class="mt-1 text-sm text-white/80">Official courseware, labs and exam-focused content, including RHCSA and RHCE programmes.</p></div>
            </div>
            @if ($testimonials->isNotEmpty())
                @php($t = $testimonials->first())
                <figure class="flex flex-col justify-center gap-3 rounded-lg border border-line bg-white p-5">
                    <blockquote class="text-[0.9375rem] text-navy-700">“{{ $t->quote }}”</blockquote>
                    <figcaption class="text-sm text-ink-muted">{{ $t->name }}{{ $t->course ? ' · '.$t->course->name : '' }}</figcaption>
                </figure>
            @else
                <div class="flex flex-col justify-center gap-3 rounded-lg border border-line bg-white p-5">
                    <p class="text-sm text-ink">Read independent reviews from our students on Google.</p>
                    <a href="{{ $contact['mapUrl'] ?? '#' }}" class="link-arrow text-sm" rel="noopener" target="_blank"><x-icon name="star" class="size-4" /> Read Google reviews<span class="sr-only"> (opens in a new tab)</span></a>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- WHY CHOOSE US --}}
<section class="section bg-surface" aria-labelledby="why-title">
    <div class="container-page">
        <x-section-heading eyebrow="Why choose us" title="Practical training, recognised certifications" id="why-title" />
        <ul class="mt-7 grid gap-3.5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([['award', 'Red Hat Authorized Partner', 'Up-to-date, official Red Hat training materials and labs.'], ['flask-conical', 'Hands-on training', 'Around 80% of class time is practical work on real systems.'], ['briefcase-business', 'Job-ready skills', 'Resume, interview and career guidance alongside the technical skills.'], ['laptop', 'Flexible learning options', 'Classroom, live virtual, one-on-one and flip-class modes.'], ['graduation-cap', 'Certified industry experts', 'Learn from certified professionals with real-world experience.'], ['users', 'Individuals and groups', 'Join a public batch, or train privately with your group or team.']] as [$ic, $t, $d])
                <li class="reveal flex gap-3.5 rounded-lg border border-line bg-white p-5">
                    <span class="ic-tile"><x-icon :name="$ic" class="size-5" /></span>
                    <div><h3 class="font-display text-[1.03125rem] font-bold text-navy-700">{{ $t }}</h3><p class="mt-1 text-sm text-ink-muted">{{ $d }}</p></div>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- FAQ --}}
@if ($faqs->isNotEmpty())
<section class="section" aria-labelledby="faq-title">
    <div class="container-page grid gap-10 lg:grid-cols-[1fr_1.6fr] lg:gap-16">
        <div>
            <x-section-heading eyebrow="FAQ" title="Questions, answered" intro="Everything you need to know before you enrol." id="faq-title" />
            <a href="/faq/" class="link-arrow mt-6">See all FAQs <x-icon name="arrow-right" class="size-4" /></a>
        </div>
        <x-faq-list :faqs="$faqs" />
    </div>
</section>
@endif

{{-- CTA WITH INQUIRY FORM --}}
<section class="section pt-0" aria-labelledby="cta-title">
    <div class="container-page">
        <div class="on-dark relative grid gap-8 overflow-hidden rounded-xl bg-grid-navy p-6 text-white sm:p-10 lg:grid-cols-2 lg:items-center lg:gap-12 lg:p-12">
            <div class="relative">
                <h2 id="cta-title" class="text-h2 text-white">Not sure which course is right for you?</h2>
                <p class="mt-3 text-lead text-navy-100">Tell us your career goals and current experience. A training advisor will call you back to recommend a track and batch, for you or your group.</p>
                <ul class="mt-6 grid gap-2.5">
                    @foreach (array_filter(['Free career counselling', $contact['hoursShort'] ?? null, $contact['address'] ?? null]) as $line)<li class="flex items-center gap-2.5"><x-icon name="check" class="size-5 text-sun-400" />{{ $line }}</li>@endforeach
                </ul>
                <a href="/course/" class="btn btn-ghost-light mt-7">Browse courses</a>
            </div>
            <div class="relative rounded-lg bg-white p-6 text-ink shadow-panel sm:p-7">
                <h3 class="text-h3">Send Inquiry</h3>
                <p class="mt-1 mb-5 text-sm text-ink-muted">An advisor replies the same working day.</p>
                <x-enquiry-form id="home-enquiry" :compact="true" />
            </div>
        </div>
    </div>
</section>
@endsection
