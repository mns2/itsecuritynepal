<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Safety net for a copied-but-not-edited .env on a real server (e.g. APP_URL=http://localhost:8000, APP_DEBUG=true).
 * When the site is reached on a public host:
 *  - a localhost APP_URL is replaced by the real host, so links and photos never point at the visitor's own
 *    computer (which triggers the browser's "access devices on your local network" prompt and breaks images);
 *  - debug error pages are switched off, so stack traces and configuration are never shown to visitors.
 * Both cases are logged once a day so the .env can be corrected. Runs globally (public site and admin).
 */
class GuardPublicHost
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! self::isLocalHost($request->getHost())) {
            if (self::isLocalHost(parse_url((string) config('app.url'), PHP_URL_HOST))) {
                $root = $request->getSchemeAndHttpHost();
                config(['app.url' => $root, 'filesystems.disks.public.url' => $root.'/storage']);
                app('filesystem')->forgetDisk('public');
                URL::forceRootUrl($root);
                self::warnDaily('app_url', "APP_URL points to localhost on a public server; using {$root}. Set APP_URL={$root} in .env, then run: php artisan optimize");
            }

            // Main domain served with a staging APP_URL (e.g. https://new.itsecuritynepal.com): canonicals, sitemap and
            // structured data must name the main domain, or search engines are sent to the staging copy.
            $primary = (string) config('seo.primary_host');
            if ($primary !== '' && strcasecmp($request->getHost(), $primary) === 0
                && strcasecmp((string) parse_url((string) config('app.url'), PHP_URL_HOST), $primary) !== 0) {
                $root = 'https://'.strtolower($primary);
                $configured = (string) config('app.url');
                config(['app.url' => $root, 'filesystems.disks.public.url' => $root.'/storage']);
                app('filesystem')->forgetDisk('public');
                URL::forceRootUrl($root);
                URL::forceScheme('https');
                self::warnDaily('app_url_primary', "Main domain served with APP_URL={$configured}; using {$root}. Set APP_URL={$root} and APP_ENV=production in .env, then run: php artisan optimize");
            }

            if (config('app.debug') && ! config('app.allow_public_debug')) {
                config(['app.debug' => false]);
                self::warnDaily('app_debug', 'APP_DEBUG=true on a public server; debug pages were disabled. Set APP_DEBUG=false in .env, then run: php artisan optimize');
            }
        }

        return $next($request);
    }

    private static function isLocalHost(?string $host): bool
    {
        return blank($host)
            || in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true)
            || str_ends_with($host, '.localhost')
            || str_ends_with($host, '.test');
    }

    private static function warnDaily(string $key, string $message): void
    {
        try {
            if (Cache::add("guard.{$key}", true, now()->addDay())) {
                Log::warning($message);
            }
        } catch (\Throwable) {
            // never let the safety net itself break a page
        }
    }
}
