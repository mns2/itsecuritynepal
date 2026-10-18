{{-- Site-wide structured data, one linked @graph: the business (EducationalOrganization + LocalBusiness), the WebSite,
     and this WebPage. Only facts that are visible on the site (header, footer, contact page) are used.
     A page sets its type with @section('schema_type', 'AboutPage'|'ContactPage'|'CollectionPage'…); default WebPage.
     Uses $pageTitle, $pageDescription, $canonical and $ogImage from layouts.app. --}}
@php
    $c = $site['contact'] ?? [];
    $brand = $site['brand'] ?? [];
    $base = rtrim(config('app.url'), '/');
    $orgId = $base.'/#organization';

    // Opening hours as shown on the site, e.g. "Sunday – Friday, 6:30 AM – 8:00 PM" (left out if the text has another shape)
    $hours = null;
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    if (preg_match('/^(\w+)\s*[–-]\s*(\w+),\s*(\d{1,2}:\d{2}\s*[AP]M)\s*[–-]\s*(\d{1,2}:\d{2}\s*[AP]M)$/u', trim($c['hours'] ?? ''), $m)
        && ($from = array_search($m[1], $days, true)) !== false && ($to = array_search($m[2], $days, true)) !== false) {
        $range = $from <= $to ? array_slice($days, $from, $to - $from + 1) : array_merge(array_slice($days, $from), array_slice($days, 0, $to + 1));
        $hours = ['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => $range, 'opens' => date('H:i', strtotime($m[3])), 'closes' => date('H:i', strtotime($m[4]))];
    }

    $sameAs = collect($site['social'] ?? [])
        ->reject(fn ($s) => in_array(strtolower($s['icon'] ?? ''), ['whatsapp', 'viber'], true))
        ->pluck('url')->filter()->unique()->values()->all();

    $org = array_filter([
        '@type' => ['EducationalOrganization', 'LocalBusiness'],
        '@id' => $orgId,
        'name' => $brand['name'] ?? 'IT Security Nepal',
        'alternateName' => $brand['legalName'] ?? null,
        'description' => $brand['description'] ?? null,
        'url' => $base.'/',
        'logo' => ['@type' => 'ImageObject', 'url' => asset('images/logo-itsn.png')],
        'image' => asset('images/og-default.png'),
        'email' => $c['email'] ?? null,
        'telephone' => $c['phonePrimary']['tel'] ?? null,
        'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'New Baneshwor', 'addressLocality' => 'Kathmandu', 'addressRegion' => 'Bagmati', 'addressCountry' => 'NP'],
        'hasMap' => $c['mapUrl'] ?? null,
        'areaServed' => ['@type' => 'Country', 'name' => 'Nepal'],
        'openingHoursSpecification' => $hours,
        'contactPoint' => array_values(array_filter([
            ! empty($c['phonePrimary']['tel']) ? ['@type' => 'ContactPoint', 'telephone' => $c['phonePrimary']['tel'], 'contactType' => 'admissions', 'areaServed' => 'NP'] : null,
            ! empty($c['phoneOffice']['tel']) ? ['@type' => 'ContactPoint', 'telephone' => $c['phoneOffice']['tel'], 'contactType' => 'customer service', 'areaServed' => 'NP'] : null,
        ])),
        'sameAs' => $sameAs,
    ]);

    $website = [
        '@type' => 'WebSite',
        '@id' => $base.'/#website',
        'url' => $base.'/',
        'name' => $brand['name'] ?? 'IT Security Nepal',
        'alternateName' => $brand['legalName'] ?? null,
        'inLanguage' => 'en',
        'publisher' => ['@id' => $orgId],
    ];

    $type = trim($__env->yieldContent('schema_type')) ?: 'WebPage';
    $webpage = array_filter([
        '@type' => $type,
        '@id' => $canonical.'#webpage',
        'url' => $canonical,
        'name' => $pageTitle,
        'description' => $pageDescription ?: null,
        'inLanguage' => 'en',
        'isPartOf' => ['@id' => $base.'/#website'],
        'about' => in_array($type, ['AboutPage', 'ContactPage'], true) || request()->path() === '/' ? ['@id' => $orgId] : null,
        'primaryImageOfPage' => $ogImage ? ['@type' => 'ImageObject', 'url' => $ogImage] : null,
    ]);
@endphp
<script type="application/ld+json">{!! \App\Support\Seo::jsonLd(['@graph' => [$org, array_filter($website), $webpage]]) !!}</script>
