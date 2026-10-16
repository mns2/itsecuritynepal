<?php

use App\Http\Middleware\TrailingSlash;
use App\Models\Redirect;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(TrailingSlash::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Before showing a 404, check the admin-managed redirect table (old live-site URLs).
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
                return null;
            }
            $path = '/'.trim($request->getPathInfo(), '/').'/';
            try {
                $redirect = Redirect::where('from_path', $path)->first();
            } catch (Throwable) {
                return null; // e.g. database not migrated yet
            }
            if ($redirect) {
                $redirect->increment('hits');

                $target = str_starts_with($redirect->to_path, 'http') ? $redirect->to_path : $request->getSchemeAndHttpHost().$redirect->to_path;

                return new RedirectResponse($target, $redirect->status_code); // keeps the trailing slash
            }

            return null;
        });
    })->create();
