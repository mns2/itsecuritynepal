@props(['title', 'text' => null])
<section class="section-tight">
    <div class="container-page">
        <div class="on-dark relative overflow-hidden rounded-xl bg-grid-navy px-6 py-10 text-white sm:px-12 lg:flex lg:items-center lg:justify-between lg:gap-12 lg:py-12">
            <div class="relative max-w-2xl">
                <h2 class="text-h2 text-white">{{ $title }}</h2>
                @if ($text)<p class="mt-3 text-lead text-navy-100">{{ $text }}</p>@endif
            </div>
            <div class="relative mt-8 flex flex-wrap gap-3 lg:mt-0 lg:shrink-0">{{ $slot }}</div>
        </div>
    </div>
</section>
