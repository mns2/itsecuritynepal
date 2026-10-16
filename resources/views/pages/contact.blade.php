@extends('layouts.app')
@php $contact = $site['contact'] ?? []; @endphp
@section('title', 'Contact IT Security Nepal | New Baneshwor, Kathmandu')
@section('description', 'Contact IT Security Nepal in New Baneshwor, Kathmandu. Call '.($contact['phonePrimary']['label'] ?? '').', email '.($contact['email'] ?? '').' or send an enquiry. Open '.($contact['hours'] ?? '').'.')

@section('content')
<section class="bg-grid-light">
    <div class="container-page pt-8 pb-12">
        <x-breadcrumbs :items="[['name' => 'Contact']]" />
        <h1 class="mt-8 text-h1">{{ content('listings.contact_title') }}</h1>
        <p class="mt-4 max-w-2xl text-lead text-ink-muted">{{ content('listings.contact_lead') }}</p>
    </div>
</section>

<section class="section-tight">
    <div class="container-page grid gap-10 lg:grid-cols-[1fr_1.35fr]">
        <div class="grid content-start gap-4">
            @php
                $cards = array_filter([
                    ! empty($contact['phonePrimary']) ? ['phone', 'Call admissions', $contact['phonePrimary']['label'], 'tel:'.$contact['phonePrimary']['tel']] : null,
                    ! empty($contact['phoneOffice']) ? ['phone', 'Office line', $contact['phoneOffice']['label'], 'tel:'.$contact['phoneOffice']['tel']] : null,
                    ! empty($contact['whatsapp']['number']) ? ['message-circle', 'WhatsApp', 'Chat with an advisor', 'https://wa.me/'.$contact['whatsapp']['number']] : null,
                    ! empty($contact['email']) ? ['mail', 'Email', $contact['email'], 'mailto:'.$contact['email']] : null,
                ]);
            @endphp
            @foreach ($cards as [$ic, $label, $value, $href])
                <a href="{{ $href }}" class="card card-interactive flex items-center gap-4 p-5 no-underline" @if (str_contains($href, 'wa.me')) rel="noopener" target="_blank" @endif>
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-md bg-navy-700 text-sun-300"><x-icon :name="$ic" class="size-5" /></span>
                    <span class="min-w-0"><span class="block text-sm text-ink-muted">{{ $label }}</span><span class="block truncate font-semibold text-navy-700">{{ $value }}</span></span>
                    <span class="ml-auto text-navy-400"><x-icon name="arrow-right" class="size-5" /></span>
                </a>
            @endforeach
            <div class="rounded-lg border border-line p-5">
                <p class="flex items-center gap-2 font-semibold text-navy-700"><x-icon name="map-pin" class="size-5 text-sun-500" /> Visit our training centre</p>
                <address class="mt-2 not-italic text-ink-muted">{{ $contact['address'] ?? '' }}</address>
                @if (! empty($contact['hours']))
                    <p class="mt-3 flex items-center gap-2 font-semibold text-navy-700"><x-icon name="clock" class="size-5 text-sun-500" /> Opening hours</p>
                    <p class="mt-1 text-ink-muted">{{ $contact['hours'] }}</p>
                @endif
            </div>
        </div>

        <div class="card p-6 sm:p-8">
            <h2 class="text-h3">Send us a message</h2>
            <p class="mt-1 mb-6 text-sm text-ink-muted">Fields are required unless marked optional.</p>
            <x-enquiry-form id="contact-form" form="contact" />
        </div>
    </div>
</section>

<section class="section-tight" aria-labelledby="map-title">
    <div class="container-page">
        <h2 id="map-title" class="sr-only">Map</h2>
        {{-- Static map card: an embedded Google Map would set third-party cookies before consent. --}}
        <div class="relative flex min-h-64 flex-col items-center justify-center gap-4 overflow-hidden rounded-xl bg-grid-light px-4 text-center sm:aspect-[21/9]">
            <span class="flex size-14 items-center justify-center rounded-full bg-sun-400 text-navy-900 shadow-panel"><x-icon name="map-pin" class="size-7" /></span>
            <p class="font-display text-lg font-semibold text-navy-700">IT Security Nepal · New Baneshwor, Kathmandu</p>
            <a href="{{ $contact['mapUrl'] ?? '#' }}" class="btn btn-navy" rel="noopener" target="_blank">Open in Google Maps<span class="sr-only"> (opens in a new tab)</span></a>
        </div>
    </div>
</section>

@if ($faqs->isNotEmpty())
<section class="section-tight" aria-labelledby="contact-faq">
    <div class="container-page max-w-4xl">
        <h2 id="contact-faq" class="text-h2">Before you get in touch</h2>
        <div class="mt-6"><x-faq-list :faqs="$faqs" /></div>
    </div>
</section>
@endif

@push('head')
<script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'ContactPage', 'name' => 'Contact IT Security Nepal', 'about' => ['@id' => rtrim(config('app.url'), '/').'/#organization']], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush
@endsection
