<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Uri;

abstract class TestCase extends BaseTestCase
{
    /**
     * Laravel's test client trims trailing slashes from request URLs; this site's canonical URLs
     * end in "/" (see App\Http\Middleware\TrailingSlash), so keep the path exactly as written.
     */
    protected function prepareUrlForRequest($uri)
    {
        $uri = $uri instanceof Uri ? $uri->value() : $uri;
        $keepSlash = str_contains(parse_url($uri, PHP_URL_PATH) ?? '', '/') && str_ends_with(strtok($uri, '?'), '/');
        $url = parent::prepareUrlForRequest($uri);

        if ($keepSlash && $uri !== '/') {
            $parts = explode('?', $url, 2);
            $url = rtrim($parts[0], '/').'/'.(isset($parts[1]) ? '?'.$parts[1] : '');
        }

        return $url;
    }
}
