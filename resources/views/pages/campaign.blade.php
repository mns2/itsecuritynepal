@extends('layouts.app')
@php
    $title = $campaign->headline ?: $campaign->name;
    $img = media($campaign->image);
    $mobileImg = media($campaign->mobile_image);
@endphp
@section('title', ($campaign->meta_title ?: $title).' | IT Security Nepal')
@section('description', $campaign->meta_description ?: ($campaign->text ?: $campaign->image_alt ?: $title))
@if ($img)@section('og_image', $img)@endif
@if ($ended || $preview)@section('noindex', '1')@endif

@section('content')
@if ($preview && ! $campaign->isLive())
    <p class="bg-sun-400 px-4 py-2 text-center text-sm font-semibold text-navy-900">Preview: this campaign is not live, so visitors cannot open this page yet.</p>
@endif
<section class="bg-grid-light">
    <div class="container-page grid gap-10 pt-8 pb-14 lg:grid-cols-[1.1fr_1fr] lg:items-start lg:gap-14">
        <div>
            <x-breadcrumbs :items="[['name' => 'Offers'], ['name' => $campaign->name]]" />
            @if ($img)
                <picture class="mt-8 block overflow-hidden rounded-2xl border border-line bg-white shadow-panel">
                    @if ($mobileImg)<source media="(max-width: 639px)" srcset="{{ $mobileImg }}">@endif
                    <img src="{{ $img }}" alt="{{ $campaign->image_alt }}" class="block h-auto w-full" fetchpriority="high">
                </picture>
            @endif
            <div class="mt-8">
                @if ($campaign->badge)<span class="badge bg-sun-400 text-navy-900">{{ $campaign->badge }}</span>@endif
                <h1 class="mt-3 text-h1">{{ $title }}</h1>
                @if ($campaign->text)<p class="mt-4 max-w-2xl text-lead text-ink-muted">{{ $campaign->text }}</p>@endif
                @if ($ended)
                    <div class="mt-6 rounded-xl border border-line bg-surface p-5">
                        <p class="font-semibold text-navy-700">This offer ended on {{ $campaign->ends_at->format('j F Y') }}.</p>
                        <p class="mt-1 text-sm text-ink-muted">Ask us about current offers and upcoming batches.</p>
                    </div>
                @else
                    <div class="mt-6 grid gap-4">
                        @include('partials.campaign-extras', ['campaign' => $campaign])
                        @if ($campaign->ends_at)<p class="text-sm text-ink-muted">Valid until {{ $campaign->ends_at->format('j F Y') }}.</p>@endif
                    </div>
                @endif
                @if ($body)
                    <div class="prose-article mt-8">{!! $body !!}</div>
                @endif
                @if ($campaign->course)
                    <p class="mt-8"><a href="{{ $campaign->course->url() }}" class="link-arrow">About the {{ $campaign->course->name }} course <x-icon name="arrow-right" class="size-4" /></a></p>
                @endif
            </div>
        </div>

        <aside class="card p-6 sm:p-8 lg:sticky lg:top-28" aria-labelledby="offer-form-title">
            <h2 id="offer-form-title" class="text-h3">{{ $ended ? 'Ask about current offers' : 'Claim this offer' }}</h2>
            <p class="mt-1 mb-6 text-sm text-ink-muted">Leave your details and our team will call you back.</p>
            <x-enquiry-form id="offer-enquiry" :compact="true" :default-course="$campaign->course?->slug ?? ''" :campaign="$ended ? null : $campaign->id" />
        </aside>
    </div>
</section>
@endsection
