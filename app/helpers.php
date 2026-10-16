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
