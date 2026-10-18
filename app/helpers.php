<?php

use App\Support\PageContent;

if (! function_exists('content')) {
    /** Editable page content, e.g. content('home.hero.title'). See App\Support\PageContent. */
    function content(string $key, mixed $default = null): mixed
    {
        return PageContent::get($key, $default);
    }
}

if (! function_exists('media')) {
    /** Public URL for an image path stored on the public disk (or an absolute URL). */
    function media(?string $value): ?string
    {
        return PageContent::imageUrl($value);
    }
}

if (! function_exists('media_alt')) {
    /** Alt text for a bundled stock photo (database/seeders/data/stock-photos.json), else $fallback. */
    function media_alt(?string $value, string $fallback = ''): string
    {
        static $alts = null;
        $alts ??= (function () {
            $file = database_path('seeders/data/stock-photos.json');
            $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : [];

            return collect($data['photos'] ?? [])->map(fn ($p) => $p['alt'] ?? '')->all();
        })();

        return $value ? ($alts[pathinfo($value, PATHINFO_FILENAME)] ?? $fallback) : $fallback;
    }
}
