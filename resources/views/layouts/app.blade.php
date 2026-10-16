@php
    $brand = $site['brand'] ?? [];
    // @section('x', 'value') stores HTML-escaped text; decode once here so {{ }} below escapes exactly once.
    $yield = fn (string $s) => html_entity_decode(trim($__env->yieldContent($s)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $pageTitle = $yield('title') ?: ($brand['name'] ?? 'IT Security Nepal');
    $pageDescription = $yield('description') ?: ($brand['description'] ?? '');
    $canonical = rtrim(config('app.url'), '/').'/'.ltrim(rtrim(request()->path(), '/').'/', '/');
    $canonical = request()->path() === '/' ? rtrim(config('app.url'), '/').'/' : $canonical;
    $indexable = app()->isProduction() && ! trim($__env->yieldContent('noindex'));
    $ga = $site['analytics']['ga4_id'] ?? config('services.analytics.ga4');
@endphp
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($pageDescription, 300, '') }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="robots" content="{{ $indexable ? 'index, follow, max-image-preview:large' : 'noindex, nofollow' }}">
    <meta property="og:site_name" content="{{ $brand['name'] ?? 'IT Security Nepal' }}">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($pageDescription, 300, '') }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $yield('og_image') ?: asset('images/og-default.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#003275">
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>document.documentElement.classList.replace('no-js','js')</script>
    @include('partials.jsonld-organization')
    @stack('head')
</head>
<body class="min-h-dvh" x-data>
    <a href="#main" class="sr-only z-[100] rounded-md bg-sun-400 px-4 py-3 font-semibold text-navy-900 focus:not-sr-only focus:fixed focus:top-3 focus:left-3">Skip to content</a>

    @include('partials.announcement')
    @include('partials.header')

    <main id="main" tabindex="-1" class="outline-none">
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.mobile-actions')

    {{-- Global enquiry dialog, opened by any element dispatching `open-enquiry` --}}
    <div x-data="{ open: false }" @open-enquiry.window="open = true" @keydown.escape.window="open = false" x-show="open" x-cloak
         class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center sm:p-6" role="dialog" aria-modal="true" aria-labelledby="enquiry-dialog-title">
        <div class="absolute inset-0 bg-navy-900/60 backdrop-blur-[2px]" @click="open = false" x-show="open" x-transition.opacity></div>
        <div class="relative max-h-[92dvh] w-full max-w-lg overflow-y-auto rounded-t-xl bg-white p-6 shadow-panel sm:rounded-xl sm:p-8" x-show="open" x-trap.noscroll.inert="open"
             x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-6 opacity-0" x-transition:enter-end="translate-y-0 opacity-100">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-eyebrow text-action">{{ content('global.enquiry_dialog.eyebrow') }}</p>
                    <h2 id="enquiry-dialog-title" class="mt-1 text-h3">{{ content('global.enquiry_dialog.title') }}</h2>
                </div>
                <button type="button" class="btn btn-ghost size-11 shrink-0 p-0" @click="open = false" aria-label="Close dialog"><x-icon name="x" class="size-5" /></button>
            </div>
            <p class="mt-2 mb-6 text-sm text-ink-muted">{{ content('global.enquiry_dialog.intro') }}</p>
            <x-enquiry-form id="dialog-enquiry" :compact="true" />
        </div>
    </div>

    @if ($ga)
        @include('partials.consent', ['ga' => $ga])
    @endif
    @stack('scripts')
</body>
</html>
