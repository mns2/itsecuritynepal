{{-- Theme E track tile (link variant). The homepage renders the same look as filter buttons. --}}
@props(['category'])
<a href="{{ $category->url() }}" class="track-tile h-full">
    <span class="ic"><x-icon :name="$category->icon" class="size-5" /></span>
    <b>{{ $category->name }}</b>
    <small>{{ $category->published_courses_count ?? $category->publishedCourses->count() }} courses</small>
</a>
