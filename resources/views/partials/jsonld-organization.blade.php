@php
    $c = $site['contact'] ?? [];
    $org = [
        '@context' => 'https://schema.org',
        '@type' => 'EducationalOrganization',
        '@id' => rtrim(config('app.url'), '/').'/#organization',
        'name' => $site['brand']['name'] ?? 'IT Security Nepal',
        'alternateName' => $site['brand']['legalName'] ?? null,
        'url' => rtrim(config('app.url'), '/').'/',
        'logo' => asset('images/logo-itsn.png'),
        'email' => $c['email'] ?? null,
        'telephone' => $c['phonePrimary']['tel'] ?? null,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'New Baneshwor',
            'addressLocality' => 'Kathmandu',
            'addressCountry' => 'NP',
        ],
        'sameAs' => collect($site['social'] ?? [])->pluck('url')->values()->all(),
    ];
@endphp
<script type="application/ld+json">{!! json_encode(array_filter($org), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
