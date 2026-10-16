@php
    $contact = $site['contact'] ?? [];
    $popular = \App\Models\Course::published()->whereIn('slug', ['red-hat-system-administration', 'ethical-hacking-expert', 'cisco-certified-network-associate', 'certified-kubernetes-administrator', 'linux-bootcamp', 'job-ready'])->get()->keyBy('slug');
    $popularLabels = ['red-hat-system-administration' => 'RH124 · RHCSA path', 'ethical-hacking-expert' => 'Ethical Hacking Expert', 'cisco-certified-network-associate' => 'CCNA', 'certified-kubernetes-administrator' => 'CKA', 'linux-bootcamp' => 'Linux Bootcamp', 'job-ready' => 'Job Ready (AU/NZ)'];
@endphp
<footer class="on-dark bg-navy-700 text-navy-100" aria-labelledby="footer-heading">
    <h2 id="footer-heading" class="sr-only">Site footer</h2>
    <div class="container-page grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_1fr_1.4fr] lg:gap-8 lg:py-16">
        <div class="max-w-sm">
            <a href="/" class="inline-block rounded-md bg-white p-2.5" aria-label="IT Security Nepal home">
                <img src="{{ asset('images/logo-itsn.png') }}" alt="IT Security — College of Computer Studies" width="480" height="188" class="h-10 w-auto" loading="lazy">
            </a>
            <p class="mt-4 text-[0.9375rem] leading-relaxed">Network | System | Cybersecurity | Red Hat | DevOps specialised training in Kathmandu. Red Hat Authorized Training Partner for 15+ years.</p>
            <ul class="mt-5 flex gap-2" aria-label="Social media">
                @foreach ($site['social'] ?? [] as $s)
                    <li><a href="{{ $s['url'] }}" class="flex size-11 items-center justify-center rounded-md bg-white/10 text-white hover:bg-sun-400 hover:text-navy-700" rel="noopener" target="_blank"><x-icon :name="$s['icon']" class="size-5" /><span class="sr-only">{{ $s['name'] }} (opens in a new tab)</span></a></li>
                @endforeach
            </ul>
        </div>
        <nav aria-labelledby="f-tracks">
            <h3 id="f-tracks" class="font-display text-[0.9375rem] font-bold text-white">Tracks</h3>
            <ul class="mt-3.5 grid gap-2 text-sm">
                @foreach ($navCategories as $cat)<li><a href="{{ $cat->url() }}" class="text-navy-100 no-underline hover:text-sun-300 hover:underline">{{ in_array($cat->key, ['comptia', 'isc2']) ? $cat->short_name : $cat->name }}</a></li>@endforeach
            </ul>
        </nav>
        <nav aria-labelledby="f-popular">
            <h3 id="f-popular" class="font-display text-[0.9375rem] font-bold text-white">Popular</h3>
            <ul class="mt-3.5 grid gap-2 text-sm">
                @foreach ($popularLabels as $slug => $label)
                    @isset($popular[$slug])<li><a href="{{ $popular[$slug]->url() }}" class="text-navy-100 no-underline hover:text-sun-300 hover:underline">{{ $label }}</a></li>@endisset
                @endforeach
            </ul>
        </nav>
        <nav aria-labelledby="f-inst">
            <h3 id="f-inst" class="font-display text-[0.9375rem] font-bold text-white">Institute</h3>
            <ul class="mt-3.5 grid gap-2 text-sm">
                @foreach ([['Individual training', '/course/'], ['Group training', '/group-training/'], ['Corporate training', '/corporate-training/'], ['Training methods', '/mode-of-training/'], ['Upcoming batches', '/batches/'], ['About us', '/about/'], ['Instructors', '/instructors/'], ['Blog', '/blog/'], ['FAQ', '/faq/']] as [$label, $url])
                    <li><a href="{{ $url }}" class="text-navy-100 no-underline hover:text-sun-300 hover:underline">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>
        <div>
            <h3 class="font-display text-[0.9375rem] font-bold text-white">Contact</h3>
            <address class="mt-3.5 grid gap-2.5 text-sm not-italic">
                <a href="{{ $contact['mapUrl'] ?? '#' }}" class="flex gap-2.5 text-navy-100 no-underline hover:text-sun-300" rel="noopener" target="_blank"><x-icon name="map-pin" class="size-[18px] shrink-0 text-sun-400" /><span>{{ $contact['address'] ?? '' }}<span class="sr-only"> (opens Google Maps)</span></span></a>
                @foreach (['phonePrimary', 'phoneOffice'] as $k)
                    @if (! empty($contact[$k]))<a href="tel:{{ $contact[$k]['tel'] }}" class="flex gap-2.5 text-navy-100 no-underline hover:text-sun-300"><x-icon name="phone" class="size-[18px] shrink-0 text-sun-400" />{{ $contact[$k]['label'] }}</a>@endif
                @endforeach
                @if (! empty($contact['email']))<a href="mailto:{{ $contact['email'] }}" class="flex gap-2.5 text-navy-100 no-underline hover:text-sun-300"><x-icon name="mail" class="size-[18px] shrink-0 text-sun-400" />{{ $contact['email'] }}</a>@endif
                @if (! empty($contact['hoursShort']))<p class="flex gap-2.5"><x-icon name="clock" class="size-[18px] shrink-0 text-sun-400" />{{ $contact['hoursShort'] }}</p>@endif
            </address>
        </div>
    </div>
    <div class="border-t border-white/15">
        <div class="container-page flex flex-col gap-3 py-5 text-[0.8125rem] sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} {{ $site['brand']['name'] ?? 'IT Security Nepal' }}. All rights reserved. Red Hat® and other certification names are trademarks of their respective owners.</p>
            <ul class="flex gap-5">
                <li><a href="/privacy-policy/" class="text-navy-100 hover:text-sun-300">Privacy Policy</a></li>
                <li><a href="/terms-and-conditions/" class="text-navy-100 hover:text-sun-300">Terms and Conditions</a></li>
            </ul>
        </div>
    </div>
</footer>
