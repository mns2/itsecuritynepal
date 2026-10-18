{{-- Accessible accordion (native <details>; content stays in the HTML for search engines).
     $faqs = [['q' => ..., 'a' => ...], ...]. Set :schema="true" on at most one list per page for FAQPage JSON-LD. --}}
@props(['faqs' => [], 'schema' => false])
@php($faqs = collect($faqs)->map(fn ($f) => is_array($f) ? $f : ['q' => $f->question, 'a' => $f->answer])->filter(fn ($f) => ! empty($f['q']) && ! empty($f['a']))->values())
@if ($faqs->isNotEmpty())
    <div class="accordion">
        @foreach ($faqs as $f)
            <details @if ($loop->first) open @endif>
                <summary><span>{{ $f['q'] }}</span><span class="acc-icon"><x-icon name="plus" class="size-4" /></span></summary>
                <div class="acc-body"><p>{{ $f['a'] }}</p></div>
            </details>
        @endforeach
    </div>
    @if ($schema)
        @push('head')
        <script type="application/ld+json">{!! \App\Support\Seo::jsonLd(['@type' => 'FAQPage', 'mainEntity' => $faqs->map(fn ($f) => ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]])->all()]) !!}</script>
        @endpush
    @endif
@endif
