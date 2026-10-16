@extends('layouts.app')
@section('title', 'About IT Security Nepal | Red Hat Authorized Training Partner in Kathmandu')
@section('description', 'I.T. Security College of Computer Studies: a Kathmandu IT training institute and Red Hat Authorized Training Partner for 15+ years, specialising in cybersecurity, networking, cloud and Linux.')

@section('content')
<section class="bg-grid-light">
    <div class="container-page pt-8 pb-14 lg:pb-20">
        <x-breadcrumbs :items="[['name' => 'About us']]" />
        <div class="mt-8 grid gap-10 lg:grid-cols-[1.3fr_1fr] lg:items-end">
            <div>
                <p class="text-eyebrow text-action">About IT Security Nepal</p>
                <h1 class="mt-3 text-h1">Nepal's specialist institute for cybersecurity, network and system training</h1>
            </div>
            <p class="text-lead text-ink-muted">Welcome to I.T. Security College of Computer Studies, a leader in IT training and certification based in New Baneshwor, Kathmandu, and a Red Hat Authorized Training Partner for more than 15 years.</p>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="mission">
    <div class="container-page grid gap-12 lg:grid-cols-2">
        <div>
            <x-section-heading eyebrow="Our mission" title="Empowering people with the skills, knowledge and certifications to excel in IT" id="mission" />
            <div class="mt-6 grid gap-4 text-[1.0625rem] leading-relaxed text-ink">
                <p>Whether you are a beginner exploring the IT field or an experienced professional pursuing advanced certification, we offer the expertise and resources to help you reach your goals, on your own or as part of a group.</p>
                <p>Over the past decade and a half we have trained and certified thousands of students who now work in IT in Nepal and abroad.</p>
            </div>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ([['terminal', 'Training philosophy', 'Learn by doing. Every concept is practised on a real system, with an instructor beside you.'], ['award', 'Authorized partner', 'Red Hat Authorized Training Partner for over 15 years, using official courseware and labs.'], ['users', 'Individuals and groups', 'Join a public batch, train one-on-one, or bring your group for a private batch.'], ['graduation-cap', 'Certification-focused', 'Courses map to recognised exams such as RHCSA, RHCE, CCNA, CKA and AZ-104.']] as [$ic, $t, $d])
                <div class="rounded-lg border border-line p-6"><span class="flex size-11 items-center justify-center rounded-md bg-navy-700 text-sun-300"><x-icon :name="$ic" class="size-5" /></span><h3 class="mt-4 text-lg font-semibold">{{ $t }}</h3><p class="mt-2 text-[0.9375rem] text-ink-muted">{{ $d }}</p></div>
            @endforeach
        </div>
    </div>
</section>

@php($stats = collect($site['trust'] ?? [])->reject(fn ($t) => ! empty($t['verify']) && empty($t['verified'])))
@if ($stats->isNotEmpty())
<section class="on-dark section bg-grid-navy text-white" aria-labelledby="achievements">
    <div class="container-page">
        <x-section-heading eyebrow="Track record" title="At a glance" :dark="true" id="achievements" />
        <ul class="mt-10 grid grid-cols-2 gap-5 lg:grid-cols-4">
            @foreach ($stats as $s)<li class="rounded-lg border border-white/15 bg-white/[0.05] p-6"><p class="font-display text-4xl font-bold text-sun-300">{{ $s['value'] }}</p><p class="mt-2 text-navy-100">{{ $s['label'] }}</p></li>@endforeach
        </ul>
    </div>
</section>
@endif

<section class="section" aria-labelledby="partners">
    <div class="container-page">
        <x-section-heading eyebrow="Certifications we prepare you for" title="Globally recognised credentials" intro="Our courses prepare learners for certifications from these technology vendors and bodies. Red Hat is our formal training partnership; the others are exam-preparation courses." id="partners" />
        <ul class="mt-10 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @foreach ([['Red Hat', 'RHCSA · RHCE · OpenShift'], ['Cisco', 'CCNA · CCNP'], ['Microsoft', 'Azure · M365 · Security'], ['CompTIA', 'A+ · Security+ · CySA+'], ['ISC2', 'CISSP · CCSP'], ['CNCF', 'CKA · CKS']] as [$v, $d])
                <li class="flex flex-col items-center justify-center rounded-lg border border-line px-4 py-6 text-center"><span class="font-display text-lg font-bold text-navy-700">{{ $v }}</span><span class="mt-1 text-xs text-ink-muted">{{ $d }}</span></li>
            @endforeach
        </ul>
        <p class="mt-4 text-xs text-ink-muted">Trademarks belong to their respective owners.</p>
    </div>
</section>

<section class="section bg-surface" aria-labelledby="environment">
    <div class="container-page grid gap-12 lg:grid-cols-2 lg:items-center">
        <div>
            <x-section-heading eyebrow="Learning environment" title="A practical lab in the heart of Kathmandu" intro="Our New Baneshwor centre is set up for hands-on work: lab machines, networking equipment and instructors who help you troubleshoot in real time. Can't come in? Join the same classes live online." id="environment" />
            <div class="mt-8 flex flex-wrap gap-3"><a href="/instructors/" class="btn btn-navy">Meet the instructors</a><a href="/mode-of-training/" class="btn btn-outline">Training methods</a></div>
        </div>
        <figure class="photo-ph aspect-[4/3] rounded-xl" role="img" aria-label="New Baneshwor training centre (photo to be supplied)"><span><x-icon name="camera" class="size-3.5" />Photo: New Baneshwor training centre</span></figure>
    </div>
</section>

<x-cta-band title="Visit us or talk to an advisor" :text="($site['contact']['address'] ?? '').' · '.($site['contact']['hoursShort'] ?? '')">
    <a href="/contact/" class="btn btn-primary btn-lg">Contact us</a>
    <a href="{{ $site['contact']['mapUrl'] ?? '#' }}" class="btn btn-ghost-light btn-lg" rel="noopener" target="_blank">Get directions</a>
</x-cta-band>
@endsection
