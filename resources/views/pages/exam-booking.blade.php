@extends('layouts.app')
@php
    $fee = (int) $settings['fee_npr'];
    $feeLabel = 'NPR '.number_format($fee);
    $qr = media($settings['qr_image']);
    $phone = $site['contact']['phonePrimary'] ?? null;
    $base = rtrim(config('app.url'), '/');
    $examNames = collect($exams)->reject(fn ($e) => str_starts_with($e, 'Other'))->values();

    // Plain, factual answers only (fee and process from the owner; nothing about timing, refunds or results).
    $faqs = [
        ['q' => 'How much does it cost to book a Red Hat exam in Nepal through IT Security Nepal?', 'a' => "The booking fee is {$feeLabel} per exam, paid in Nepali Rupees by scanning the IT Security Nepal payment QR code on this page."],
        ['q' => 'Which Red Hat exams can I book?', 'a' => 'You can request '.($examNames->isNotEmpty() ? $examNames->join(', ', ' and ') : 'Red Hat exams').'. If you need a different Red Hat exam, choose "Other" and our team will confirm it with you.'],
        ['q' => 'Do you need my Red Hat password?', 'a' => 'No. We only ask for your Red Hat login (username) and the email address on your Red Hat account. Never share your Red Hat password with anyone, including our staff.'],
        ['q' => 'What happens after I submit the booking form?', 'a' => 'You get a booking reference straight away. Your payment and exam booking stay pending until our team checks the payment against our account. We then contact you by phone or email to confirm the exam booking.'],
        ['q' => 'What should the payment screenshot show?', 'a' => "A clear screenshot or PDF receipt that shows the amount ({$feeLabel}), the date and time, and the transaction ID or reference from your bank or wallet app. JPG, PNG, WebP or PDF, up to 5 MB."],
        ['q' => 'Do I need a Red Hat account before booking?', 'a' => 'Yes. Create a free Red Hat account at redhat.com first, then enter its login and email address in the booking form.'],
    ];

    $ld = [
        '@type' => 'Service',
        'name' => 'Red Hat Exam Booking in Nepal',
        'serviceType' => 'Certification exam booking',
        'description' => "Book Red Hat certification exams such as RHCSA (EX200) and RHCE (EX294) in Nepal through IT Security Nepal. Booking fee {$feeLabel} per exam, paid by QR code.",
        'url' => $base.'/red-hat-exam-booking-nepal/',
        'areaServed' => ['@type' => 'Country', 'name' => 'Nepal'],
        'provider' => ['@type' => 'EducationalOrganization', 'name' => $site['brand']['name'] ?? 'IT Security Nepal', 'url' => $base.'/'],
        'offers' => ['@type' => 'Offer', 'price' => $fee, 'priceCurrency' => 'NPR', 'availability' => $open ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock', 'url' => $base.'/red-hat-exam-booking-nepal/#book'],
    ];
    $field = 'exam-booking';
@endphp
@section('title', 'Red Hat Exam Booking in Nepal: RHCSA, RHCE | IT Security Nepal')
@section('description', "Book your Red Hat exam (RHCSA EX200, RHCE EX294) in Nepal. Fee {$feeLabel}: pay by QR, upload your payment screenshot and we confirm your booking.")
@push('head')
<script type="application/ld+json">{!! \App\Support\Seo::jsonLd($ld) !!}</script>
@endpush

@section('content')
<section class="bg-grid-light">
    <div class="container-page grid gap-10 pt-8 pb-12 lg:grid-cols-[1.3fr_1fr] lg:items-center lg:pb-16">
        <div>
            <x-breadcrumbs :items="[['name' => 'Red Hat & Linux', 'url' => '/redhat/'], ['name' => 'Red Hat exam booking']]" />
            <p class="badge badge-solid mt-8 gap-1.5 py-1.5"><x-icon name="badge-check" class="size-3.5" /> Red Hat certification exams</p>
            <h1 class="mt-4 text-h1">Red Hat Exam Booking in Nepal</h1>
            <p class="mt-4 max-w-2xl text-lead text-ink-muted">
                Book your RHCSA, RHCE or other Red Hat certification exam through IT Security Nepal in Kathmandu.
                Pay {{ $feeLabel }} by QR code, send us your Red Hat account details and payment screenshot, and our team confirms your booking.
            </p>
            <div class="mt-7 flex flex-wrap gap-2.5">
                <a href="#book" class="btn btn-primary btn-lg">Book your exam</a>
                @if ($phone)<a href="tel:{{ $phone['tel'] }}" class="btn btn-outline btn-lg"><x-icon name="phone" class="size-5" /> {{ $phone['label'] }}</a>@endif
            </div>
        </div>
        <div class="card p-6 sm:p-8">
            <p class="text-eyebrow text-action">Exam booking fee</p>
            <p class="mt-2 font-display text-[2.5rem] leading-none font-extrabold text-navy-700">{{ $feeLabel }}</p>
            <p class="mt-2 text-sm text-ink-muted">Per exam, paid in Nepali Rupees by QR code.</p>
            <ul class="mt-5 grid gap-2.5 text-[0.9375rem]">
                @foreach ($examNames as $e)
                    <li class="flex gap-2"><x-icon name="circle-check" class="mt-0.5 size-5 shrink-0 text-success-600" />{{ $e }}</li>
                @endforeach
                <li class="flex gap-2"><x-icon name="lock" class="mt-0.5 size-5 shrink-0 text-action" />We never ask for your Red Hat password</li>
            </ul>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="xb-steps">
    <div class="container-page">
        <x-section-heading eyebrow="How it works" title="How to book your Red Hat exam" id="xb-steps" align="center" />
        <ol class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['user-round', 'Have a Red Hat account', 'Create a free account at redhat.com if you do not have one. You will need its login and email.'],
                ['scan-qr-code', 'Pay '.$feeLabel, 'Scan the IT Security Nepal QR code below with your bank or wallet app and pay the exact fee.'],
                ['upload', 'Submit the form', 'Enter your details and Red Hat account, choose the exam and upload your payment screenshot.'],
                ['calendar-check', 'We verify and confirm', 'Our team checks your payment, then contacts you to confirm your exam booking.'],
            ] as $i => [$icon, $title, $text])
                <li class="card p-5">
                    <div class="flex items-center gap-3"><span class="ic-tile"><x-icon :name="$icon" class="size-5" /></span><span class="font-mono text-sm font-semibold text-ink-muted">Step {{ $i + 1 }}</span></div>
                    <h3 class="mt-4 font-display text-lg font-bold text-navy-700">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-ink-muted">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<section id="book" class="section bg-surface scroll-mt-40" aria-labelledby="xb-book">
    <div class="container-page grid gap-8 lg:grid-cols-[0.85fr_1.4fr] lg:items-start">
        {{-- Payment --}}
        <div class="card p-6 sm:p-8 lg:sticky lg:top-40">
            <h2 id="xb-book" class="text-h3">1. Pay by QR code</h2>
            <p class="mt-1 text-sm text-ink-muted">Amount: <strong class="text-navy-700">{{ $feeLabel }}</strong></p>
            @if ($qr)
                <figure class="mt-5">
                    <img src="{{ $qr }}" alt="IT Security Nepal official payment QR code" width="320" height="320" class="mx-auto aspect-square w-full max-w-[18rem] rounded-lg border border-line bg-white object-contain p-3">
                    @if (filled($settings['payee_note']))<figcaption class="mt-3 text-center text-sm font-medium text-navy-700">{{ $settings['payee_note'] }}</figcaption>@endif
                </figure>
                <a href="{{ $qr }}" download="itsecuritynepal-payment-qr" class="btn btn-outline btn-block mt-4"><x-icon name="image" class="size-5" /> Save QR to your phone</a>
                <p class="mt-2 text-xs text-ink-muted">Paying on this phone? Save the QR, then open it from your gallery in your bank or wallet app.</p>
            @else
                <div class="mt-5 rounded-lg border border-sun-400 bg-sun-50 p-5 text-sm" role="status">
                    <p class="flex items-center gap-2 font-semibold text-navy-700"><x-icon name="info" class="size-5" /> Payment QR code coming soon</p>
                    <p class="mt-1 text-ink-muted">Online booking opens once our official payment QR code is published here. Please call us to book in the meantime.</p>
                </div>
            @endif
            <ul class="mt-6 grid gap-2.5 border-t border-line pt-5 text-sm text-ink-muted">
                <li class="flex gap-2"><x-icon name="receipt" class="mt-0.5 size-4 shrink-0 text-action" />Take a screenshot showing the amount, date and transaction ID.</li>
                <li class="flex gap-2"><x-icon name="shield-check" class="mt-0.5 size-4 shrink-0 text-action" />Only pay with the QR code shown on this official page.</li>
                <li class="flex gap-2"><x-icon name="key-round" class="mt-0.5 size-4 shrink-0 text-action" />We will never ask for your Red Hat password or bank PIN.</li>
            </ul>
        </div>

        {{-- Booking form --}}
        <div class="card p-6 sm:p-8">
            <h2 class="text-h3">2. Send your booking details</h2>
            <p class="mt-1 mb-6 text-sm text-ink-muted">All fields are required unless marked optional.</p>

            @if (! $open)
                <div class="rounded-lg border border-line bg-surface p-5 text-sm" role="status">
                    <p class="font-semibold text-navy-700">Online booking is not open yet.</p>
                    <p class="mt-1 text-ink-muted">Call @if ($phone)<a href="tel:{{ $phone['tel'] }}">{{ $phone['label'] }}</a>@else us @endif or <a href="/contact/">contact us</a> to book your Red Hat exam.</p>
                </div>
            @else
            @php($issued = encrypt(now()->timestamp))
            <form action="{{ route('exam-booking.store') }}" method="post" enctype="multipart/form-data" class="grid gap-5" novalidate
                  x-data="examBookingForm" @submit="submit($event)">
                @csrf
                <input type="hidden" name="issued" value="{{ $issued }}">
                <div class="absolute -left-[9999px]" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

                @if ($errors->any())
                    <div class="rounded-md bg-error-50 px-4 py-3 text-sm font-medium text-error-600" role="alert" tabindex="-1" x-init="$el.focus()">
                        Please fix {{ $errors->count() }} field{{ $errors->count() > 1 ? 's' : '' }} below.@if ($errors->has('proof') || old('name')) Please attach your payment screenshot again.@endif
                    </div>
                @endif

                <fieldset class="grid gap-4 sm:grid-cols-2">
                    <legend class="mb-3 font-display font-bold text-navy-700">Your details</legend>
                    @foreach ([
                        ['name', 'Full name', 'text', 'name', '', 120, 'As on your citizenship or passport.'],
                        ['phone', 'Phone number', 'tel', 'tel', '98XXXXXXXX', 30, ''],
                        ['email', 'Email address', 'email', 'email', 'you@example.com', 160, ''],
                    ] as [$f, $label, $type, $ac, $ph, $max, $hint])
                        <div @class(['sm:col-span-2' => $f === 'email' || $f === 'name'])>
                            <label for="{{ $field }}-{{ $f }}" class="field-label">{{ $label }}</label>
                            <input id="{{ $field }}-{{ $f }}" name="{{ $f }}" type="{{ $type }}" class="input" autocomplete="{{ $ac }}" maxlength="{{ $max }}" value="{{ old($f) }}" required
                                   @if ($ph) placeholder="{{ $ph }}" @endif data-rule="{{ $f }}" @blur="validate($event.target)" @error($f) aria-invalid="true" @enderror
                                   aria-describedby="{{ $hint ? $field.'-'.$f.'-hint ' : '' }}{{ $field }}-{{ $f }}-err">
                            @if ($hint)<p id="{{ $field }}-{{ $f }}-hint" class="field-hint">{{ $hint }}</p>@endif
                            <p id="{{ $field }}-{{ $f }}-err" class="field-error" @unless ($errors->has($f)) hidden @endunless><x-icon name="circle-alert" class="size-4" /><span>{{ $errors->first($f) }}</span></p>
                        </div>
                    @endforeach
                </fieldset>

                <fieldset class="grid gap-4 sm:grid-cols-2 border-t border-line pt-5">
                    <legend class="mb-3 pt-5 font-display font-bold text-navy-700">Red Hat account and exam</legend>
                    <div class="sm:col-span-2 flex gap-3 rounded-md bg-sun-50 p-3.5 text-sm text-navy-700">
                        <x-icon name="lock" class="mt-0.5 size-5 shrink-0" />
                        <p><strong>Do not enter your Red Hat password.</strong> We only need your login (username) and the email on your Red Hat account.</p>
                    </div>
                    @foreach ([
                        ['redhat_login', 'Red Hat login / username', 'text', 'off', 'e.g. sita.sharma', 100, 'The login you use at redhat.com (not your password).'],
                        ['redhat_email', 'Red Hat account email', 'email', 'off', 'you@example.com', 160, 'The email address registered on your Red Hat account.'],
                    ] as [$f, $label, $type, $ac, $ph, $max, $hint])
                        <div>
                            <label for="{{ $field }}-{{ $f }}" class="field-label">{{ $label }}</label>
                            <input id="{{ $field }}-{{ $f }}" name="{{ $f }}" type="{{ $type }}" class="input" autocomplete="{{ $ac }}" maxlength="{{ $max }}" value="{{ old($f) }}" placeholder="{{ $ph }}" required spellcheck="false" autocapitalize="off"
                                   data-rule="{{ $f }}" @blur="validate($event.target)" @error($f) aria-invalid="true" @enderror aria-describedby="{{ $field }}-{{ $f }}-hint {{ $field }}-{{ $f }}-err">
                            <p id="{{ $field }}-{{ $f }}-hint" class="field-hint">{{ $hint }}</p>
                            <p id="{{ $field }}-{{ $f }}-err" class="field-error" @unless ($errors->has($f)) hidden @endunless><x-icon name="circle-alert" class="size-4" /><span>{{ $errors->first($f) }}</span></p>
                        </div>
                    @endforeach
                    <div class="sm:col-span-2">
                        <label for="{{ $field }}-exam" class="field-label">Exam</label>
                        <select id="{{ $field }}-exam" name="exam" class="input" required data-rule="exam" @change="validate($event.target)" @error('exam') aria-invalid="true" @enderror aria-describedby="{{ $field }}-exam-err">
                            <option value="">Choose your exam…</option>
                            @foreach ($exams as $e)<option value="{{ $e }}" @selected(old('exam', request('exam')) === $e)>{{ $e }}</option>@endforeach
                        </select>
                        <p id="{{ $field }}-exam-err" class="field-error" @unless ($errors->has('exam')) hidden @endunless><x-icon name="circle-alert" class="size-4" /><span>{{ $errors->first('exam') }}</span></p>
                    </div>
                </fieldset>

                <fieldset class="grid gap-4 border-t border-line pt-5">
                    <legend class="mb-3 pt-5 font-display font-bold text-navy-700">Payment of {{ $feeLabel }}</legend>
                    <div>
                        <span class="field-label" id="{{ $field }}-proof-label">Payment screenshot</span>
                        <label for="{{ $field }}-proof" class="flex cursor-pointer flex-col items-center gap-2 rounded-lg border-2 border-dashed border-line bg-white p-6 text-center hover:border-action focus-within:border-action"
                               :class="fileError ? 'border-error-600' : ''">
                            <template x-if="preview"><img :src="preview" alt="Preview of your payment screenshot" class="max-h-48 rounded-md border border-line"></template>
                            <x-icon name="upload" class="size-7 text-action" x-show="!preview" />
                            <span class="font-semibold text-navy-700" x-text="fileName || 'Choose a screenshot or PDF'">Choose a screenshot or PDF</span>
                            <span class="text-xs text-ink-muted">JPG, PNG, WebP or PDF, up to 5 MB</span>
                            <input id="{{ $field }}-proof" name="proof" type="file" accept="image/jpeg,image/png,image/webp,application/pdf" class="sr-only" required
                                   aria-labelledby="{{ $field }}-proof-label" aria-describedby="{{ $field }}-proof-err" @change="pickFile($event)" @error('proof') aria-invalid="true" @enderror>
                        </label>
                        <p id="{{ $field }}-proof-err" class="field-error" @unless ($errors->has('proof')) hidden @endunless><x-icon name="circle-alert" class="size-4" /><span>{{ $errors->first('proof') }}</span></p>
                    </div>
                    <div>
                        <label for="{{ $field }}-transaction_id" class="field-label">Transaction ID <span class="font-normal text-ink-muted">(optional)</span></label>
                        <input id="{{ $field }}-transaction_id" name="transaction_id" type="text" class="input" maxlength="100" value="{{ old('transaction_id') }}" autocomplete="off" spellcheck="false"
                               data-rule="transaction_id" @blur="validate($event.target)" @error('transaction_id') aria-invalid="true" @enderror aria-describedby="{{ $field }}-transaction_id-hint {{ $field }}-transaction_id-err">
                        <p id="{{ $field }}-transaction_id-hint" class="field-hint">From your bank or wallet receipt. Helps us find your payment faster.</p>
                        <p id="{{ $field }}-transaction_id-err" class="field-error" @unless ($errors->has('transaction_id')) hidden @endunless><x-icon name="circle-alert" class="size-4" /><span>{{ $errors->first('transaction_id') }}</span></p>
                    </div>
                </fieldset>

                <div>
                    <label class="flex gap-3 text-sm text-ink-muted">
                        <input id="{{ $field }}-confirm" type="checkbox" name="confirm" value="1" class="check mt-0.5" required data-rule="confirm" @change="validate($event.target)" @checked(old('confirm')) @error('confirm') aria-invalid="true" @enderror aria-describedby="{{ $field }}-confirm-err">
                        <span>I confirm these details are correct, I have paid {{ $feeLabel }}, and I agree that IT Security Nepal uses them to book my Red Hat exam, as described in the <a href="/privacy-policy/">Privacy Policy</a>.</span>
                    </label>
                    <p id="{{ $field }}-confirm-err" class="field-error" @unless ($errors->has('confirm')) hidden @endunless><x-icon name="circle-alert" class="size-4" /><span>{{ $errors->first('confirm') }}</span></p>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" :disabled="sending">
                    <span x-show="!sending">Submit booking request</span><span x-show="sending" x-cloak>Uploading…</span>
                </button>
                <p class="flex items-center justify-center gap-1.5 text-xs text-ink-muted"><x-icon name="shield-check" class="size-4" /> Sent over a secure connection. Only authorised IT Security Nepal staff can see your payment screenshot.</p>
            </form>
            @endif
        </div>
    </div>
</section>

<section class="section" aria-labelledby="xb-faq">
    <div class="container-page grid gap-12 lg:grid-cols-[1fr_1.4fr]">
        <div>
            <x-section-heading eyebrow="Questions" title="Red Hat exam booking FAQ" id="xb-faq" />
            <p class="mt-4 text-ink-muted">Preparing for the exam? See our <a href="/red-hat-system-administration/">Red Hat System Administration (RHCSA path)</a> and <a href="/red-hat-certified-system-engineer/">RHCE</a> courses, or read the <a href="/rhcsa-certification-guide/">RHCSA certification guide</a>.</p>
        </div>
        <x-faq-list :faqs="$faqs" :schema="true" />
    </div>
</section>
@endsection
