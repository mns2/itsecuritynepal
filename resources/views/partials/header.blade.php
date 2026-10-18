{{-- Theme E header: row 1 = logo · All Courses mega menu · search · phone · Send Inquiry; row 2 = section links + "Training for" --}}
@php
    $contact = $site['contact'] ?? [];
    $audiences = $site['audiences'] ?? [];
    $current = '/'.ltrim(rtrim(request()->path(), '/').'/', '/');
    $nav = collect(content('global.header_nav', []))->map(fn ($n) => ['name' => $n['label'] ?? '', 'url' => $n['url'] ?? '#', 'icon' => $n['icon'] ?: 'circle', 'tag' => $n['tag'] ?? null])->all();
@endphp
<header x-data="siteHeader" class="sticky top-0 z-50 bg-white transition-shadow" :class="scrolled && 'shadow-[0_6px_20px_-12px_rgb(0_50_117/0.35)]'" @keydown.escape="closeAll()">
    <div class="container-page relative flex h-[4.5rem] items-center gap-3">
        <a href="/" class="mr-auto shrink-0 lg:mr-2" aria-label="IT Security Nepal home">
            <img src="{{ asset('images/logo-itsn.png') }}" alt="IT Security — College of Computer Studies" width="480" height="188" class="h-10 w-auto lg:h-11" fetchpriority="high">
        </a>

        <div class="hidden lg:block" @mouseleave="mega = false">
            <button type="button" class="flex h-11 items-center gap-2 rounded-md px-4 font-display text-sm font-bold transition-colors" :class="mega ? 'bg-navy-700 text-white' : 'bg-surface text-navy-700 hover:bg-surface-2'" :aria-expanded="mega" aria-controls="mega-courses" @click="mega = !mega">
                <x-icon name="layers" class="size-[18px]" /> All Courses <x-icon name="chevron-down" class="size-4" />
            </button>
            <div id="mega-courses" x-show="mega" x-cloak x-transition.opacity.duration.150ms @click.outside="mega = false" class="absolute top-full left-4 z-10 w-[min(56rem,calc(100vw-2rem))] pt-2.5 sm:left-6 lg:left-8">
                <div class="rounded-lg border border-line bg-white p-5 shadow-panel">
                    <ul class="grid grid-cols-3 gap-x-5 gap-y-1">
                        @foreach ($navCategories as $cat)
                            <li>
                                <a href="{{ $cat->url() }}" class="flex gap-3 rounded-[10px] p-3 no-underline hover:bg-surface">
                                    <span class="mt-0.5 text-action"><x-icon :name="$cat->icon" class="size-[22px]" /></span>
                                    <span>
                                        <span class="block font-display text-[0.90625rem] leading-snug font-bold text-navy-700">{{ $cat->name }}</span>
                                        <span class="block text-[0.78125rem] text-ink-muted">{{ $cat->publishedCourses->take(4)->pluck('code')->join(', ') }}{{ $cat->publishedCourses->count() > 4 ? '…' : '' }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                        <li>
                            <a href="/course-finder/" class="flex gap-3 rounded-[10px] bg-sun-100 p-3 no-underline hover:bg-sun-200">
                                <span class="mt-0.5 text-navy-700"><x-icon name="compass" class="size-[22px]" /></span>
                                <span><span class="block font-display text-[0.90625rem] font-bold text-navy-700">Course Finder</span><span class="block text-[0.78125rem] text-ink">4 questions, 1 recommendation</span></span>
                            </a>
                        </li>
                    </ul>
                    <div class="mt-2 flex items-center justify-between border-t border-line pt-3.5 text-[0.84375rem] text-ink-muted">
                        <span>Training for individuals, groups and companies</span>
                        <a href="/course/" class="link-arrow">Browse all {{ $courseCount }} courses <x-icon name="arrow-right" class="size-4" /></a>
                    </div>
                </div>
            </div>
        </div>

        <form action="/course/" method="get" role="search" class="relative hidden max-w-[21rem] flex-1 lg:block">
            <label for="hdr-q" class="sr-only">Search courses</label>
            <input id="hdr-q" name="q" type="search" class="h-11 w-full rounded-md border-[1.5px] border-line bg-surface pr-12 pl-3.5 text-sm text-ink placeholder:text-ink-subtle focus:border-action focus:bg-white focus:ring-4 focus:ring-action/12 focus:outline-none" placeholder="Search courses, e.g. RHCSA, CCNA…" autocomplete="off">
            <button type="submit" class="absolute top-1 right-1 grid size-9 place-items-center rounded-[6px] bg-navy-700 text-white hover:bg-action" aria-label="Search"><x-icon name="search" class="size-[17px]" /></button>
        </form>

        @if (! empty($contact['phonePrimary']))
            <a href="tel:{{ $contact['phonePrimary']['tel'] }}" class="ml-auto hidden items-center gap-2.5 no-underline xl:flex">
                <span class="grid size-[38px] place-items-center rounded-full bg-sun-100 text-navy-700"><x-icon name="phone" class="size-[18px]" /></span>
                <span><span class="block text-[0.6875rem] text-ink-muted">Call an advisor</span><span class="block font-display text-sm font-bold text-navy-700">{{ $contact['phonePrimary']['label'] }}</span></span>
            </a>
        @endif
        <button type="button" class="btn btn-primary hidden whitespace-nowrap sm:inline-flex" @click="$dispatch('open-enquiry', {})">{{ content('global.header_button') }} <x-icon name="arrow-right" class="size-[18px]" /></button>

        <a href="tel:{{ $contact['phonePrimary']['tel'] ?? '' }}" class="grid size-11 place-items-center rounded-md border-[1.5px] border-line text-navy-700 hover:border-action hover:text-action sm:hidden" aria-label="Call {{ $contact['phonePrimary']['label'] ?? '' }}"><x-icon name="phone" class="size-5" /></a>
        <button type="button" class="grid size-11 place-items-center rounded-md border-[1.5px] border-line text-navy-700 hover:border-action hover:text-action lg:hidden" @click="$dispatch('open-search')" aria-label="Search courses"><x-icon name="search" class="size-5" /></button>
        <button type="button" class="grid size-11 place-items-center rounded-md border-[1.5px] border-line text-navy-700 hover:border-action hover:text-action lg:hidden" @click="mobile = true" :aria-expanded="mobile" aria-controls="mobile-nav" aria-label="Open menu"><x-icon name="menu" class="size-5" /></button>
    </div>

    <div class="hidden border-y border-line lg:block">
        <div class="container-page flex h-[2.875rem] items-center justify-between gap-6 text-[0.84375rem]">
            <nav aria-label="Main">
                <ul class="flex items-center gap-5 2xl:gap-6">
                    @foreach ($nav as $item)
                        <li><a href="{{ $item['url'] }}" @class(['inline-flex items-center gap-1.5 font-medium whitespace-nowrap no-underline hover:text-action', 'text-action' => $current === $item['url'], 'text-ink' => $current !== $item['url']]) @if ($current === $item['url']) aria-current="page" @endif>
                            <span class="text-action"><x-icon :name="$item['icon']" class="size-[15px]" /></span>{{ $item['name'] }}@if (! empty($item['tag']))<span class="rounded-full bg-sun-400 px-1.5 py-0.5 text-[0.65625rem] leading-none font-bold text-navy-700">{{ $item['tag'] }}</span>@endif
                        </a></li>
                    @endforeach
                </ul>
            </nav>
            <p class="hidden shrink-0 items-center gap-3 text-ink-muted xl:flex">Training for:
                @foreach ($audiences as $a)<a href="{{ $a['url'] }}" class="font-semibold text-navy-700 no-underline hover:text-action hover:underline">{{ $a['name'] }}</a>@endforeach
            </p>
        </div>
    </div>

    <nav aria-label="Quick links" class="border-y border-line lg:hidden">
        <ul class="no-scrollbar container-page flex gap-2 overflow-x-auto py-2 text-[0.8125rem] font-semibold">
            <li><button type="button" class="flex h-8 items-center gap-1.5 rounded-full bg-navy-700 px-3 whitespace-nowrap text-white" @click="mobile = true"><x-icon name="layers" class="size-3.5" />All Courses</button></li>
            @foreach (content('global.mobile_quick_links', []) as $q)
                <li><a href="{{ $q['url'] }}" class="flex h-8 items-center rounded-full border-[1.5px] border-line px-3 whitespace-nowrap text-navy-700 no-underline">{{ $q['label'] }}</a></li>
            @endforeach
        </ul>
    </nav>

    {{-- Mobile drawer (left) --}}
    <div id="mobile-nav" x-show="mobile" x-cloak class="fixed inset-0 z-[60] lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
        <div class="absolute inset-0 bg-ink/45" @click="mobile = false" x-show="mobile" x-transition.opacity></div>
        <nav aria-label="Mobile" class="absolute inset-y-0 left-0 flex w-[min(23.75rem,90vw)] flex-col overflow-y-auto bg-white" x-show="mobile" x-trap.noscroll.inert="mobile"
             x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            <div class="flex items-center justify-between border-b border-line px-4 py-3.5">
                <span class="font-display text-base font-extrabold text-navy-700">Menu</span>
                <button type="button" class="grid size-11 place-items-center rounded-md border-[1.5px] border-line text-navy-700" @click="mobile = false" aria-label="Close menu"><x-icon name="x" class="size-5" /></button>
            </div>
            <form action="/course/" method="get" role="search" class="px-4 py-3.5">
                <label for="drawer-q" class="sr-only">Search courses</label>
                <input id="drawer-q" name="q" type="search" class="h-11 w-full rounded-md border-[1.5px] border-line bg-surface px-3.5 text-sm" placeholder="Search courses…">
            </form>
            <details open class="border-b border-line">
                <summary class="flex items-center justify-between px-4 py-3.5 font-display text-[0.9375rem] font-bold text-navy-700">All Courses <x-icon name="chevron-down" class="size-[18px]" /></summary>
                <ul class="pb-2">
                    @foreach ($navCategories as $cat)<li><a href="{{ $cat->url() }}" class="block py-2.5 pr-4 pl-7 text-sm text-ink no-underline hover:bg-surface">{{ $cat->name }}</a></li>@endforeach
                    <li><a href="/course/" class="block py-2.5 pr-4 pl-7 text-sm font-semibold text-action no-underline">View all {{ $courseCount }} courses</a></li>
                </ul>
            </details>
            <details class="border-b border-line">
                <summary class="flex items-center justify-between px-4 py-3.5 font-display text-[0.9375rem] font-bold text-navy-700">Training for <x-icon name="chevron-down" class="size-[18px]" /></summary>
                <ul class="pb-2">@foreach ($audiences as $a)<li><a href="{{ $a['url'] }}" class="block py-2.5 pr-4 pl-7 text-sm text-ink no-underline hover:bg-surface">{{ $a['title'] }}</a></li>@endforeach</ul>
            </details>
            <ul>
                @foreach ($nav as $item)<li><a href="{{ $item['url'] }}" class="block border-b border-line px-4 py-3.5 font-display text-[0.9375rem] font-semibold text-ink no-underline hover:bg-surface" @if ($current === $item['url']) aria-current="page" @endif>{{ $item['name'] }}</a></li>@endforeach
                <li><a href="/course-finder/" class="block border-b border-line px-4 py-3.5 font-display text-[0.9375rem] font-semibold text-ink no-underline hover:bg-surface">Course Finder</a></li>
            </ul>
            <div class="mt-auto grid gap-2.5 bg-surface p-4">
                <button type="button" class="btn btn-primary btn-block" @click="mobile = false; $dispatch('open-enquiry', {})">Send Inquiry</button>
                @if (! empty($contact['phonePrimary']))<a href="tel:{{ $contact['phonePrimary']['tel'] }}" class="btn btn-outline btn-block"><x-icon name="phone" class="size-[18px]" /> {{ $contact['phonePrimary']['label'] }}</a>@endif
            </div>
        </nav>
    </div>

    {{-- Search overlay (mobile search button, "/" shortcut) --}}
    <div x-data="courseSearch" @open-search.window="openSearch()" @keydown.window.prevent.slash="if (!['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) openSearch()" x-show="open" x-cloak
         class="fixed inset-0 z-[65] flex items-start justify-center px-4 pt-[10vh]" role="dialog" aria-modal="true" aria-label="Search courses">
        <div class="absolute inset-0 bg-ink/55" @click="open = false"></div>
        <div class="relative w-full max-w-2xl overflow-hidden rounded-xl bg-white shadow-panel" x-trap.noscroll="open" @keydown.escape.stop="open = false">
            <div class="flex items-center gap-3 border-b border-line px-4">
                <x-icon name="search" class="size-5 text-ink-muted" />
                <label for="site-search" class="sr-only">Search courses</label>
                <input id="site-search" x-ref="input" type="search" x-model="q" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="go()" role="combobox" aria-controls="search-results" :aria-expanded="results.length > 0" :aria-activedescendant="active >= 0 ? 'sr-' + active : null"
                       class="h-16 flex-1 bg-transparent text-lg outline-none placeholder:text-ink-subtle" placeholder="What do you want to learn?" autocomplete="off">
                <kbd class="hidden rounded border border-line px-1.5 py-0.5 font-mono text-xs text-ink-muted sm:block">Esc</kbd>
            </div>
            <div class="max-h-[60vh] overflow-y-auto p-2">
                <template x-if="loading"><p class="flex items-center gap-2 p-4 text-ink-muted"><x-icon name="loader-circle" class="size-5 animate-spin" /> Loading courses…</p></template>
                <template x-if="!loading && q.length < 2">
                    <div class="p-3">
                        <p class="text-xs font-semibold tracking-wide text-ink-muted uppercase">Popular searches</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (['RHCSA', 'Ethical hacking', 'CCNA', 'Kubernetes', 'Azure', 'Linux bootcamp'] as $t)<button type="button" class="chip" @click="q = '{{ $t }}'">{{ $t }}</button>@endforeach
                        </div>
                    </div>
                </template>
                <ul id="search-results" role="listbox" x-show="results.length">
                    <template x-for="(c, i) in results" :key="c.slug">
                        <li :id="'sr-' + i" role="option" :aria-selected="i === active">
                            <a :href="c.url" class="flex items-center gap-3 rounded-lg p-3 no-underline" :class="i === active ? 'bg-surface' : 'hover:bg-surface'" @mouseenter="active = i">
                                <span class="inline-flex h-9 min-w-14 items-center justify-center rounded-md bg-navy-700 px-2 font-display text-xs font-bold text-white" x-text="c.code"></span>
                                <span class="min-w-0"><span class="block truncate font-display font-bold text-navy-700" x-text="c.name"></span><span class="block truncate text-sm text-ink-muted" x-text="c.categoryName + ' · ' + c.level"></span></span>
                            </a>
                        </li>
                    </template>
                </ul>
                <template x-if="!loading && q.length >= 2 && !results.length">
                    <div class="p-6 text-center">
                        <p class="font-semibold text-navy-700">No courses match "<span x-text="q"></span>"</p>
                        <p class="mt-1 text-sm text-ink-muted">Try a certification code like RH124 or AZ-104, or ask an advisor.</p>
                        <button type="button" class="btn btn-outline btn-sm mt-4" @click="open = false; $dispatch('open-enquiry', {})">Ask an advisor</button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</header>
