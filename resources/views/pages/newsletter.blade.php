@extends('layouts.app')
@section('title', 'Email updates | IT Security Nepal')
@section('description', 'Manage your IT Security Nepal email subscription.')
@section('noindex', '1')

@section('content')
<section class="bg-grid-light">
    <div class="container-page py-16 lg:py-24">
        <div class="card mx-auto max-w-lg p-8 text-center sm:p-10">
            @if ($state === 'confirmed')
                <span class="mx-auto grid size-14 place-items-center rounded-full bg-success-50 text-success-600"><x-icon name="circle-check" class="size-7" /></span>
                <h1 class="mt-5 text-h2">You're subscribed</h1>
                <p class="mt-3 text-ink-muted">Thank you. We'll email <strong class="text-navy-700">{{ $subscriber->email }}</strong> about new batches, scholarships and offers.</p>
                <a href="/course/" class="btn btn-primary mt-8">Browse courses</a>
            @elseif ($state === 'confirm-unsubscribe')
                <span class="mx-auto grid size-14 place-items-center rounded-full bg-sun-100 text-navy-700"><x-icon name="mail" class="size-7" /></span>
                <h1 class="mt-5 text-h2">Unsubscribe?</h1>
                <p class="mt-3 text-ink-muted">Stop sending emails to <strong class="text-navy-700">{{ $subscriber->email }}</strong>.</p>
                <form method="post" action="{{ route('newsletter.unsubscribe.do', $subscriber->token) }}" class="mt-8">
                    @csrf
                    <button type="submit" class="btn btn-primary">Yes, unsubscribe me</button>
                </form>
            @else
                <span class="mx-auto grid size-14 place-items-center rounded-full bg-surface text-navy-700"><x-icon name="mail-x" class="size-7" /></span>
                <h1 class="mt-5 text-h2">You're unsubscribed</h1>
                <p class="mt-3 text-ink-muted"><strong class="text-navy-700">{{ $subscriber->email }}</strong> will not receive any more emails from us. Changed your mind? You can sign up again any time at the bottom of our website.</p>
                <a href="/" class="btn btn-outline mt-8">Back to the website</a>
            @endif
        </div>
    </div>
</section>
@endsection
