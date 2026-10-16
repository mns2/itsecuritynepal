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

    public static function current(): ?SeoMeta
    {
        return self::forPath('/'.ltrim(request()->path(), '/'));
    }

    /** JSON for a <script type="application/ld+json"> block; "<" is escaped so content can never close the script tag. */
    public static function jsonLd(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_PRETTY_PRINT);
    }
}
