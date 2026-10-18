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
            <span class="ic-tile"><x-icon name="mail" class="size-5" /></span>
            <h1 class="mt-5 text-h2">Thank you{{ session('exam_booking_name') ? ', '.session('exam_booking_name') : '' }}. Now confirm your email.</h1>
            @if (session('exam_booking_mailed') === false)
                <p class="mt-3 text-lead text-ink-muted">We saved your request, but <strong class="text-navy-700">we could not send the confirmation email</strong> right now. Please call us to confirm your booking, or try again from the tracking page later.</p>
            @elseif (session('exam_booking_email'))
                <p class="mt-3 text-lead text-ink-muted">We sent a confirmation link to <strong class="text-navy-700">{{ session('exam_booking_email') }}</strong>. Click it so our team can start checking your payment. Your payment and exam booking stay <strong class="text-navy-700">pending verification</strong> until then.</p>
            @else
                <p class="mt-3 text-lead text-ink-muted">Your <strong class="text-navy-700">payment and exam booking are pending verification.</strong> Your exam is not booked yet.</p>
            @endif

            @if (session('exam_booking_ref'))
                <dl class="mt-6 grid gap-4 rounded-lg border border-line bg-surface p-5 sm:grid-cols-2">
                    <div><dt class="text-sm text-ink-muted">Booking reference</dt><dd class="font-mono text-xl font-bold text-navy-700">{{ session('exam_booking_ref') }}</dd></div>
                    <div><dt class="text-sm text-ink-muted">Exam</dt><dd class="font-semibold text-navy-700">{{ session('exam_booking_exam') }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-sm text-ink-muted">Status</dt><dd><span class="badge badge-sun">Email not confirmed · payment pending verification</span></dd></div>
                </dl>
                <p class="mt-3 text-sm text-ink-muted">Save this reference. With it and your email address you can <a href="{{ route('exam-booking.track') }}/">track your booking</a> any time.</p>
            @endif

            <h2 class="mt-8 font-display text-lg font-bold text-navy-700">What happens next</h2>
            <ol class="mt-3 grid gap-3 text-[0.9375rem] text-ink-muted">
                <li class="flex gap-3"><span class="badge badge-navy shrink-0">1</span>You click the link in our email to confirm your email address. No email? Check your spam folder.</li>
                <li class="flex gap-3"><span class="badge badge-navy shrink-0">2</span>Our team checks your payment screenshot against the payment received in our account, and emails you when it is verified.</li>
                <li class="flex gap-3"><span class="badge badge-navy shrink-0">3</span>We book your exam using the Red Hat account details you sent. This can take some time.</li>
                <li class="flex gap-3"><span class="badge badge-navy shrink-0">4</span>Once your exam is booked, we email you the confirmation and exam date.</li>
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
