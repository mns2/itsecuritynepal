@props(['post', 'headingLevel' => 3])
@php($tag = 'h'.$headingLevel)
<article class="card card-interactive group relative flex h-full flex-col overflow-hidden" data-category="{{ $post->category }}">
    <div class="relative aspect-[16/9] overflow-hidden bg-media" aria-hidden="true">
        @if ($post->featured_image)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($post->featured_image) }}" alt="" class="absolute inset-0 size-full object-cover" loading="lazy" width="640" height="360">
        @else
            <div class="absolute inset-0 flex flex-col justify-end p-5">
                <span class="badge badge-on-dark w-fit">{{ $post->category }}</span>
                <p class="mt-3 line-clamp-2 font-display text-lg font-semibold text-white/90">{{ $post->title }}</p>
            </div>
            <span class="absolute top-4 right-4 flex size-10 items-center justify-center rounded-lg bg-sun-400 text-navy-700"><x-icon name="book-open" class="size-5" /></span>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-ink-muted">
            <time datetime="{{ $post->published_at?->toDateString() }}">{{ $post->published_at?->format('j M Y') }}</time><span aria-hidden="true">·</span><span>{{ $post->readingMinutes() }} min read</span>
        </p>
        <{{ $tag }} class="mt-2 text-lg leading-snug font-semibold">
            <a href="{{ $post->url() }}" class="stretched-link text-navy-700 no-underline group-hover:text-action">{{ $post->title }}</a>
        </{{ $tag }}>
        <p class="mt-2 line-clamp-3 text-[0.9375rem] text-ink-muted">{{ $post->excerptText() }}</p>
        <span class="link-arrow mt-auto pt-4 text-sm" aria-hidden="true">Read article <x-icon name="arrow-right" class="size-4" /></span>
    </div>
</article>
