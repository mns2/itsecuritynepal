{{-- Batch list: table layout on ≥md, stacked cards on mobile, from the same markup. --}}
@props(['batches'])
<div class="overflow-hidden rounded-lg border border-line bg-white">
    <div class="hidden grid-cols-[2.2fr_1.1fr_1.5fr_1.2fr_1fr_auto] gap-4 border-b border-line bg-surface px-5 py-3 text-xs font-semibold tracking-wide text-ink-muted uppercase md:grid" aria-hidden="true">
        <span>Course</span><span>Starts</span><span>Schedule</span><span>Mode</span><span>Status</span><span class="w-28"></span>
    </div>
    <ul role="list" class="divide-y divide-line">
        @foreach ($batches as $b)
            @php($c = $b->course)
            <li class="grid gap-3 px-5 py-4 md:grid-cols-[2.2fr_1.1fr_1.5fr_1.2fr_1fr_auto] md:items-center md:gap-4" data-batch data-category="{{ $c->category->key }}">
                <div>
                    <a href="{{ $c->url() }}" class="font-semibold text-navy-700 no-underline hover:text-action">{{ $c->name }}</a>
                    <p class="text-xs text-ink-muted">{{ $c->code }} · {{ $c->category->name }}</p>
                </div>
                <p class="text-sm"><span class="font-medium text-ink-muted md:sr-only">Starts: </span>{{ $b->starts_on ? $b->starts_on->format('j M Y') : $b->start_label }}</p>
                <p class="text-sm"><span class="font-medium text-ink-muted md:sr-only">Schedule: </span>{{ $b->schedule ?: 'To be announced' }}</p>
                <p class="text-sm"><span class="font-medium text-ink-muted md:sr-only">Mode: </span>{{ $b->mode }}</p>
                <div><x-status-badge :status="$b->status" /></div>
                <button type="button" @class(['btn btn-sm w-full md:w-28', 'btn-outline' => $b->status === 'waitlist', 'btn-navy' => $b->status !== 'waitlist']) x-data @click="$dispatch('open-enquiry', { course: '{{ $c->slug }}' })">
                    {{ $b->status === 'waitlist' ? 'Join waitlist' : 'Reserve' }}<span class="sr-only"> a seat in {{ $c->name }}</span>
                </button>
            </li>
        @endforeach
    </ul>
</div>
