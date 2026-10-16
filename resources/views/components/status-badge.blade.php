@props(['status'])
@php
    $map = [
        'open' => ['badge-success', 'Enrolling', 'circle-check'],
        'few' => ['badge-sun', 'Few seats left', 'triangle-alert'],
        'soon' => ['badge-navy', 'Opening soon', 'clock'],
        'waitlist' => ['badge-muted', 'Waitlist', 'list-filter'],
    ];
    [$cls, $label, $icon] = $map[$status] ?? $map['soon'];
@endphp
<span class="badge {{ $cls }}"><x-icon :name="$icon" class="size-3.5" />{{ $label }}</span>
