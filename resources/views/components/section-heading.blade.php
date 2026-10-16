@props(['eyebrow' => null, 'title', 'intro' => null, 'align' => 'left', 'dark' => false, 'id' => null])
<div @class(['mx-auto max-w-3xl text-center' => $align === 'center', 'max-w-3xl' => $align !== 'center'])>
    @if ($eyebrow)<p @class(['text-eyebrow', 'text-sun-300' => $dark, 'text-action' => ! $dark])>{{ $eyebrow }}</p>@endif
    <h2 @if ($id) id="{{ $id }}" @endif @class(['mt-2.5 text-h2', 'text-white' => $dark])>{{ $title }}</h2>
    @if ($intro)<p @class(['mt-3 max-w-[52ch]', 'mx-auto' => $align === 'center', 'text-navy-100' => $dark, 'text-ink-muted' => ! $dark])>{{ $intro }}</p>@endif
</div>
