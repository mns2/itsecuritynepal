<?php

namespace App\Support;

use App\Models\SeoMeta;

/** Looks up the Advanced SEO record for the current page (once per request; cached on the request). */
class Seo
{
    public static function forPath(string $path): ?SeoMeta
    {
        $path = SeoMeta::normalisePath($path);
        $attributes = request()->attributes;
        $key = 'seo_meta:'.$path;

        if (! $attributes->has($key)) {
            try {
                // A record attached to a page (course, post…) wins over a plain path entry.
                $attributes->set($key, SeoMeta::where('path', $path)->orderByRaw('seoable_id is null')->first());
            } catch (\Throwable) {
                $attributes->set($key, null); // table missing during first deploy: never break pages
            }
        }

        return $attributes->get($key);
    }

    /** Is this request on the public domain search engines should index? See config/seo.php. */
    public static function indexable(): bool
    {
        $forced = config('seo.indexing');
        if ($forced !== null && $forced !== '') {
            return filter_var($forced, FILTER_VALIDATE_BOOL);
        }

        return self::onPrimaryHost() || (app()->isProduction() && ! self::onSecondaryHost());
    }

    public static function onPrimaryHost(): bool
    {
        return strcasecmp(request()->getHost(), (string) config('seo.primary_host')) === 0;
    }

    /** A subdomain of the primary domain (e.g. new.itsecuritynepal.com staging): never indexed by the automatic rule. */
    private static function onSecondaryHost(): bool
    {
        $primary = (string) config('seo.primary_host');

        return $primary !== '' && str_ends_with(strtolower(request()->getHost()), '.'.strtolower($primary));
    }

    public static function current(): ?SeoMeta
    {
        return self::forPath('/'.ltrim(request()->path(), '/'));
    }

    /** JSON for a <script type="application/ld+json"> block; "<" is escaped so content can never close the script tag. */
    public static function jsonLd(array $data): string
    {
        // Adds the context itself: inside Blade templates, never type '@context' in {!! !!} (Laravel's @context
        // directive turns it into PHP code and breaks the markup). Keep schema arrays in @php blocks or pass them here.
        $data = ['@context' => 'https://schema.org'] + $data;

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRETTY_PRINT);
    }
}
