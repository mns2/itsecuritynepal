<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The live site uses trailing-slash URLs (/redhat/). Keep them canonical: GET/HEAD requests for
 * page URLs without a trailing slash are 301-redirected. Files (.xml, .txt, .json…) and /admin, /trainer, /livewire are untouched.
 */
class TrailingSlash
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        if (in_array($request->method(), ['GET', 'HEAD'], true)
            && $path !== '/'
            && ! str_ends_with($path, '/')
            && ! str_contains(basename($path), '.')
            && ! preg_match('#^/(admin|trainer|livewire|filament|storage|build|up|newsletter|campaigns|offer-track|exam-booking)(/|$)#', $path)) {
            $query = $request->getQueryString();

            // Build the URL by hand: Laravel's URL generator would strip the trailing slash again.
            return new RedirectResponse($request->getSchemeAndHttpHost().$request->getBaseUrl().$path.'/'.($query ? '?'.$query : ''), 301);
        }

        return $next($request);
    }
}
