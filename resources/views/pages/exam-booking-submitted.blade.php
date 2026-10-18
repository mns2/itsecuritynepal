@extends('layouts.app')
@php($phone = $site['contact']['phonePrimary'] ?? null)
@section('title', 'Booking request received | IT Security Nepal')
@section('description', 'Your Red Hat exam booking request has been received and is pending payment verification.')
@section('noindex', '1')

@section('content')
<section class="bg-grid-light">
    <div class="container-page max-w-3xl pt-8 pb-16">
        <x-breadcrumbs :items="[['name' => 'Red Hat exam booking', 'url' => '/red-hat-exam-booking-nepal/'], ['name' => 'Request received']]" />
        <div class="card mt-8 p-6 sm:p-10" role="status">
            <span class="ic-tile"><x-icon name="clock" class="size-5" /></span>
            <h1 class="mt-5 text-h2">Thank you{{ session('exam_booking_name') ? ', '.session('exam_booking_name') : '' }}. Your request is in.</h1>
            <p class="mt-3 text-lead text-ink-muted">Your <strong class="text-navy-700">payment and exam booking are pending verification.</strong> Your exam is not booked yet.</p>

            @if (session('exam_booking_ref'))
                <dl class="mt-6 grid gap-4 rounded-lg border border-line bg-surface p-5 sm:grid-cols-2">
                    <div><dt class="text-sm text-ink-muted">Booking reference</dt><dd class="font-mono text-xl font-bold text-navy-700">{{ session('exam_booking_ref') }}</dd></div>
                    <div><dt class="text-sm text-ink-muted">Exam</dt><dd class="font-semibold text-navy-700">{{ session('exam_booking_exam') }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-sm text-ink-muted">Status</dt><dd><span class="badge badge-sun">Pending verification</span></dd></div>
                </dl>
                <p class="mt-3 text-sm text-ink-muted">Save or screenshot this reference and quote it when you contact us.</p>
            @endif

            <h2 class="mt-8 font-display text-lg font-bold text-navy-700">What happens next</h2>
            <ol class="mt-3 grid gap-3 text-[0.9375rem] text-ink-muted">
                <li class="flex gap-3"><span class="badge badge-navy shrink-0">1</span>Our team checks your payment screenshot against the payment received in our account.</li>
                <li class="flex gap-3"><span class="badge badge-navy shrink-0">2</span>Once the payment is verified, we book your exam using the Red Hat account details you sent.</li>
                <li class="flex gap-3"><span class="badge badge-navy shrink-0">3</span>We contact you by phone or email to confirm the booking.</li>
            </ol>

            <div class="mt-8 flex flex-wrap gap-2.5">
                @if ($phone)<a href="tel:{{ $phone['tel'] }}" class="btn btn-primary"><x-icon name="phone" class="size-5" /> {{ $phone['label'] }}</a>@endif
                <a href="/redhat/" class="btn btn-outline">Explore Red Hat courses</a>
            </div>
            <p class="mt-6 flex gap-2 text-sm text-ink-muted"><x-icon name="key-round" class="mt-0.5 size-4 shrink-0" />We will never ask for your Red Hat password or bank PIN. If anyone does, do not share it.</p>
        </div>
    </div>
</section>
@endsection
