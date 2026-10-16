@extends('layouts.app')
@section('title', 'Corporate IT Training in Nepal | Cybersecurity, DevOps & Cloud | IT Security Nepal')
@section('description', 'Customised corporate IT training for organisations in Nepal: cybersecurity, DevOps, cloud and infrastructure programmes delivered on-site, at our Kathmandu centre or online.')

@section('content')
<section class="relative overflow-hidden bg-grid-light">
    <div class="container-page relative grid gap-12 pt-8 pb-16 lg:grid-cols-[1.2fr_1fr] lg:items-center lg:pb-24">
        <div>
            <x-breadcrumbs :items="[['name' => 'Corporate training']]" />
            <p class="mt-10 text-eyebrow text-action">For organisations</p>
            <h1 class="mt-3 text-h1">Corporate IT training that strengthens your whole team</h1>
            <p class="mt-5 max-w-2xl text-lead text-ink-muted">Customised programmes in cybersecurity, DevOps, cloud and infrastructure, designed around your systems, your risks and your team's current skills.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#proposal" class="btn btn-primary btn-lg">Request a proposal</a>
                @if (! empty($site['contact']['phonePrimary']))<a href="tel:{{ $site['contact']['phonePrimary']['tel'] }}" class="btn btn-outline btn-lg"><x-icon name="phone" class="size-5" /> {{ $site['contact']['phonePrimary']['label'] }}</a>@endif
            </div>
        </div>
        <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
            @foreach ([['users', 'Training for teams of any size', 'From small IT teams to organisation-wide security awareness.'], ['sliders-horizontal', 'Customised content', 'Built around your technologies, workflows and goals.'], ['map-pin', 'On-site, at our centre or online', 'Flexible scheduling for busy professionals.']] as [$ic, $t, $d])
                <li class="flex gap-4 rounded-lg border border-line bg-white p-5"><span class="flex size-10 shrink-0 items-center justify-center rounded-md bg-sun-400 text-navy-900"><x-icon :name="$ic" class="size-5" /></span><div><p class="font-semibold text-navy-700">{{ $t }}</p><p class="text-sm text-ink-muted">{{ $d }}</p></div></li>
            @endforeach
        </ul>
    </div>
</section>

<section class="section" aria-labelledby="needs">
    <div class="container-page">
        <x-section-heading eyebrow="Why invest in training" title="Close skill gaps before they become incidents" align="center" id="needs" />
        <ul class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['shield-check', 'Reduce security risk', 'Train staff to recognise and respond to threats, and harden the systems they run.'], ['trending-up', 'Improve productivity', 'Teams that automate and troubleshoot confidently ship faster with fewer outages.'], ['badge-check', 'Certify your people', 'Prepare staff for recognised certifications that clients and regulators trust.'], ['refresh-ccw', 'Keep pace with change', 'Adopt containers, cloud and DevOps practices with structured, hands-on learning.']] as [$ic, $t, $d])
                <li class="reveal rounded-lg border border-line p-6"><span class="flex size-11 items-center justify-center rounded-md bg-navy-700 text-sun-300"><x-icon :name="$ic" class="size-5" /></span><h3 class="mt-4 text-lg font-semibold">{{ $t }}</h3><p class="mt-2 text-[0.9375rem] text-ink-muted">{{ $d }}</p></li>
            @endforeach
        </ul>
    </div>
</section>

<section class="section bg-surface" aria-labelledby="sector">
    <div class="container-page grid gap-12 lg:grid-cols-2 lg:items-center">
        <x-section-heading eyebrow="Sector focus" title="Training for banking & financial institutions" intro="Banks are prime targets for cyber threats. Our specialised programmes help banking teams safeguard customer data, maintain trust and meet regulatory requirements." id="sector" />
        <ul class="grid gap-3">
            @foreach (['Fraud prevention and detection', 'Compliance and regulatory standards', 'Secure software development', 'Incident response and security operations'] as $t)
                <li class="flex items-center gap-4 rounded-lg border border-line bg-white p-4"><span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-sun-100 text-sun-700"><x-icon name="check" class="size-5" /></span><span class="font-semibold text-navy-700">{{ $t }}</span></li>
            @endforeach
        </ul>
    </div>
</section>

<section class="section" aria-labelledby="areas">
    <div class="container-page">
        <x-section-heading eyebrow="Training areas" title="Programmes we can tailor for your team" id="areas" />
        <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($categories as $cat)<li><a href="{{ $cat->url() }}" class="card card-interactive flex h-full items-center gap-4 p-5 no-underline"><span class="flex size-11 shrink-0 items-center justify-center rounded-md bg-surface text-action"><x-icon :name="$cat->icon" class="size-5" /></span><span class="font-semibold text-navy-700">{{ $cat->name }}</span></a></li>@endforeach
        </ul>
    </div>
</section>

<section class="on-dark section bg-grid-navy text-white" aria-labelledby="approach">
    <div class="container-page">
        <x-section-heading eyebrow="Our approach" title="From skills assessment to certified team" :dark="true" id="approach" />
        <ol class="mt-12 grid gap-6 md:grid-cols-4">
            @foreach ([['Assess', "We review your team's current skills, systems and goals."], ['Design', 'We build a programme around your technologies and timeline.'], ['Deliver', 'Hands-on sessions on-site, at our centre or live online.'], ['Measure', 'Lab assessments and certification readiness reports.']] as [$t, $d])
                <li class="relative rounded-lg border border-white/15 bg-white/[0.05] p-6"><span class="font-display text-sm font-bold text-sun-300">0{{ $loop->iteration }}</span><h3 class="mt-3 text-lg font-semibold text-white">{{ $t }}</h3><p class="mt-2 text-[0.9375rem] text-navy-100">{{ $d }}</p></li>
            @endforeach
        </ol>
    </div>
</section>

<section id="proposal" class="section scroll-mt-40" aria-labelledby="proposal-title">
    <div class="container-page grid gap-12 lg:grid-cols-[1fr_1.3fr]">
        <div>
            <x-section-heading eyebrow="Request a proposal" title="Tell us about your team" intro="We usually reply within one working day with a suggested programme outline, format and next steps." id="proposal-title" />
            <ul class="mt-8 grid gap-4 text-[0.9375rem]">
                @if (! empty($site['contact']['phonePrimary']))<li class="flex gap-3"><x-icon name="phone" class="size-5 text-action" /><a href="tel:{{ $site['contact']['phonePrimary']['tel'] }}">{{ $site['contact']['phonePrimary']['label'] }}</a></li>@endif
                @if (! empty($site['contact']['email']))<li class="flex gap-3"><x-icon name="mail" class="size-5 text-action" /><a href="mailto:{{ $site['contact']['email'] }}?subject=Corporate%20training%20enquiry">{{ $site['contact']['email'] }}</a></li>@endif
            </ul>
        </div>
        <div class="card p-6 sm:p-8"><x-enquiry-form id="corp-enquiry" :corporate="true" title="Corporate training proposal request" /></div>
    </div>
</section>
@endsection
