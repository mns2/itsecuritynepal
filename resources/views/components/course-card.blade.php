{{-- Theme E course card: decorative course-code "media" block (no stock photo) + compact body. --}}
@props(['course', 'headingLevel' => 3])
@php
    $c = $course;
    $batch = $c->relationLoaded('batches') ? $c->batches->first() : null;
    $tag = 'h'.$headingLevel;
    $modes = $c->modes ?? [];
@endphp
<article class="card card-interactive group relative flex h-full flex-col overflow-hidden"
    data-course data-category="{{ $c->category->key }}" data-level="{{ $c->level }}" data-modes="{{ implode('|', $modes) }}"
    data-search="{{ \Illuminate\Support\Str::lower($c->code.' '.$c->name.' '.$c->certification.' '.$c->category->name) }}" data-featured="{{ $c->featured ? 'true' : 'false' }}">
    <div class="relative grid aspect-[16/9] place-items-center bg-media text-white" aria-hidden="true">
        <span class="badge absolute top-2.5 left-2.5 bg-sun-100 text-sun-700">{{ $c->category->short_name }}</span>
        @if ($batch && ! $batch->is_illustrative)
            <span class="badge absolute top-2.5 right-2.5 bg-white text-success-600"><x-icon name="calendar-days" class="size-3" />{{ $batch->start_label }}</span>
        @endif
        <span class="relative px-4 text-center font-display text-[1.75rem] leading-none font-extrabold tracking-tight">{{ $c->code }}</span>
    </div>
    <div class="flex flex-1 flex-col gap-2 p-4">
        <{{ $tag }} class="font-display text-[0.96875rem] leading-snug font-bold text-navy-700">
            <a href="{{ $c->url() }}" class="stretched-link text-inherit no-underline group-hover:text-action">{{ $c->name }}</a>
        </{{ $tag }}>
        <ul class="flex flex-wrap gap-x-3 gap-y-1.5 text-[0.78125rem] text-ink-muted">
            <li class="inline-flex items-center gap-1.5"><span class="text-action"><x-icon name="graduation-cap" class="size-3.5" /></span>{{ $c->level }}</li>
            @if ($c->duration)
                <li class="inline-flex items-center gap-1.5"><span class="text-action"><x-icon name="clock" class="size-3.5" /></span>{{ $c->duration }}</li>
            @elseif ($c->certification)
                <li class="inline-flex items-center gap-1.5"><span class="text-action"><x-icon name="badge-check" class="size-3.5" /></span>{{ $c->certification }}</li>
            @endif
        </ul>
        <div class="mt-auto flex items-center justify-between gap-3 border-t border-line pt-2.5 text-[0.8125rem]">
            <span class="text-ink-muted">{{ count($modes) > 1 ? 'Classroom · Online' : ($modes[0] ?? '') }}</span>
            <span class="font-display font-bold text-action group-hover:underline" aria-hidden="true">Details →</span>
        </div>
    </div>
</article>
