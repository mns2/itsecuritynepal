{{-- Breadcrumbs + BreadcrumbList JSON-LD. $items = [['name' => 'Courses', 'url' => '/course/'], ['name' => 'RH124']] --}}
@props(['items' => []])
@php
    $base = rtrim(config('app.url'), '/');
    $all = array_merge([['name' => 'Home', 'url' => '/']], $items);
    $ld = ['@type' => 'BreadcrumbList', 'itemListElement' => collect($all)->values()->map(fn ($it, $i) => array_filter([
        '@type' => 'ListItem', 'position' => $i + 1, 'name' => $it['name'],
        'item' => isset($it['url']) ? $base.$it['url'] : $base.'/'.ltrim(rtrim(request()->path(), '/').'/', '/'),
    ]))->all()];
@endphp
<nav aria-label="Breadcrumb" class="breadcrumbs text-ink-muted">
    <ol>
        <li><a href="/">Home</a></li>
        @foreach ($items as $it)
            <li aria-hidden="true"><x-icon name="chevron-right" class="size-3.5 opacity-60" /></li>
            <li>
                @if (! empty($it['url']) && ! $loop->last)
                    <a href="{{ $it['url'] }}">{{ $it['name'] }}</a>
                @else
                    <span aria-current="page" class="font-medium text-navy-700">{{ $it['name'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@push('head')
<script type="application/ld+json">{!! \App\Support\Seo::jsonLd($ld) !!}</script>
@endpush
