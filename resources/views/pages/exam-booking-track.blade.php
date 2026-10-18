@extends('layouts.app')
@php $phone = $site['contact']['phonePrimary'] ?? null; @endphp
@section('title', 'Track your Red Hat exam booking | IT Security Nepal')
@section('description', 'Check the progress of your Red Hat exam booking with IT Security Nepal using your booking reference and email address.')
@section('noindex', '1')

@section('content')
<section class="bg-grid-light">
    <div class="container-page max-w-3xl pt-8 pb-16">
        <x-breadcrumbs :items="[['name' => 'Red Hat exam booking', 'url' => '/red-hat-exam-booking-nepal/'], ['name' => 'Track your booking']]" />
        <h1 class="mt-8 text-h1">Track your exam booking</h1>
        <p class="mt-3 text-lead text-ink-muted">See where your Red Hat exam booking is, from payment check to confirmed exam.</p>

        @if (session('exam_track_notice'))
            <div class="mt-6 flex gap-3 rounded-lg border border-success-600/30 bg-success-50 p-4 text-sm font-medium text-navy-700" role="status">
                <x-icon name="circle-check" class="mt-0.5 size-5 shrink-0 text-success-600" />{{ session('exam_track_notice') }}
            </div>
        @endif

        @foreach ($bookings as $b)
            @php
                $paid = in_array($b->status, ['verified', 'booked'], true);
                $steps = [
                    ['Request received', 'Payment screenshot and details sent.', true, $b->created_at],
                    ['Email confirmed', $b->emailVerified() ? 'Your booking is with our team.' : 'Click the link we emailed to '.$b->email.'.', $b->emailVerified(), $b->email_verified_at],
                    ['Payment verified', $paid ? 'We found your payment of NPR '.number_format($b->fee_npr).'.' : 'Our team checks your payment against our account.', $paid, null],
                    // After payment, booking with Red Hat can take a while: its own phase, so candidates see it is in hand.
                    ['Exam booking in progress', $b->status === 'booked' ? 'Booked with your Red Hat account details.' : 'We book your exam with your Red Hat account details. This can take some time; we email you as soon as it is confirmed.', $b->status === 'booked', null],
                    ['Exam booked', $b->status === 'booked' ? ($b->exam_at ? 'Exam on '.$b->exam_at->format('l, j F Y \a\t g:i a').'. Confirmation emailed to '.$b->email.'.' : 'Your exam is booked. Confirmation emailed to '.$b->email.'; check your Red Hat account for the details.') : 'You get a confirmation email with your exam date.', $b->status === 'booked', null],
                ];
                $current = collect($steps)->search(fn ($s) => ! $s[2]);
            @endphp
            <article class="card mt-8 p-6 sm:p-8" aria-labelledby="bk-{{ $b->id }}">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-ink-muted">Booking reference</p>
                        <h2 id="bk-{{ $b->id }}" class="font-mono text-2xl font-bold text-navy-700">{{ $b->reference }}</h2>
                    </div>
                    <span @class(['badge', 'badge-success' => $b->status === 'booked', 'badge-sun' => in_array($b->status, ['unverified', 'pending', 'verified'], true), 'badge-muted' => in_array($b->status, ['rejected', 'cancelled'], true)])>{{ \App\Models\ExamBooking::STATUSES[$b->status] ?? $b->status }}</span>
                </div>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                    <div class="sm:col-span-2"><dt class="text-ink-muted">Exam</dt><dd class="font-semibold text-navy-700">{{ $b->exam }}</dd></div>
                    <div><dt class="text-ink-muted">Fee</dt><dd class="font-semibold text-navy-700">NPR {{ number_format($b->fee_npr) }}</dd></div>
                </dl>

                @if (in_array($b->status, ['rejected', 'cancelled'], true))
                    <div class="mt-6 rounded-lg border border-error-600/30 bg-error-50 p-4 text-sm text-error-600" role="alert">
                        <p class="font-semibold">{{ $b->status === 'rejected' ? 'We could not match your payment with our account.' : 'This booking was cancelled.' }}</p>
                        <p class="mt-1">Please call @if ($phone)<a href="tel:{{ $phone['tel'] }}">{{ $phone['label'] }}</a>@else us @endif with your booking reference{{ $b->status === 'rejected' ? ', payment screenshot and transaction ID' : '' }}.</p>
                    </div>
                @else
                    <ol class="mt-6 grid gap-0">
                        @foreach ($steps as $i => [$title, $text, $done, $at])
                            <li class="relative flex gap-4 pb-6 last:pb-0" @if ($i === $current) aria-current="step" @endif>
                                @unless ($loop->last)<span class="absolute top-8 left-4 h-[calc(100%-2rem)] w-0.5 {{ $done ? 'bg-success-600' : 'bg-line' }}" aria-hidden="true"></span>@endunless
                                <span @class(['relative flex size-8 shrink-0 items-center justify-center rounded-full text-sm font-bold', 'bg-success-600 text-white' => $done, 'bg-sun-400 text-navy-900' => ! $done && $i === $current, 'border border-line bg-white text-ink-muted' => ! $done && $i !== $current])>
                                    @if ($done)<x-icon name="check" class="size-4" /><span class="sr-only">Done:</span>@else {{ $i + 1 }} @endif
                                </span>
                                <div class="pt-1">
                                    <p @class(['font-semibold', 'text-navy-700' => $done || $i === $current, 'text-ink-muted' => ! $done && $i !== $current])>{{ $title }}@if ($i === $current && ! str_contains($title, 'in progress')) <span class="text-sm font-normal text-ink-muted">(in progress)</span>@endif</p>
                                    <p class="text-sm text-ink-muted">{{ $text }}@if ($done && $at) <span class="whitespace-nowrap">· {{ $at->format('j M Y') }}</span>@endif</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif

                @unless ($b->emailVerified())
                    <form method="post" action="{{ route('exam-booking.resend', $b) }}" class="mt-6 border-t border-line pt-5">
                        @csrf
                        <p class="text-sm text-ink-muted">No email from us? Check your spam folder, or</p>
                        <button type="submit" class="btn btn-outline btn-sm mt-2"><x-icon name="mail" class="size-4" /> Send the confirmation email again</button>
                    </form>
                @endunless
            </article>
        @endforeach

        <div class="card mt-8 p-6 sm:p-8">
            <h2 class="text-h3">{{ $bookings->isEmpty() ? 'Find your booking' : 'Track another booking' }}</h2>
            <p class="mt-1 mb-5 text-sm text-ink-muted">Enter the reference from your confirmation page or email, and the email address you booked with.</p>
            <form method="post" action="{{ route('exam-booking.lookup') }}" class="grid gap-4 sm:grid-cols-[1fr_1.4fr_auto] sm:items-end" novalidate>
                @csrf
                <div>
                    <label for="trk-ref" class="field-label">Booking reference</label>
                    <input id="trk-ref" name="reference" class="input font-mono uppercase" placeholder="RHX-XXXXXX" maxlength="20" value="{{ old('reference') }}" required autocomplete="off" spellcheck="false" @error('reference') aria-invalid="true" aria-describedby="trk-err" @enderror>
                </div>
                <div>
                    <label for="trk-email" class="field-label">Email address</label>
                    <input id="trk-email" name="email" type="email" class="input" placeholder="you@example.com" maxlength="160" value="{{ old('email') }}" required autocomplete="email" @error('email') aria-invalid="true" @enderror>
                </div>
                <button type="submit" class="btn btn-primary">Track</button>
            </form>
            @if ($errors->any())
                <p id="trk-err" class="field-error mt-3" role="alert"><x-icon name="circle-alert" class="size-4" />{{ $errors->first() }}</p>
            @endif
        </div>

        <p class="mt-6 text-sm text-ink-muted">Need help? @if ($phone)Call <a href="tel:{{ $phone['tel'] }}">{{ $phone['label'] }}</a> or @endif<a href="/contact/">contact us</a>. Not booked yet? <a href="/red-hat-exam-booking-nepal/">Book your Red Hat exam</a>.</p>
    </div>
</section>
@endsection
