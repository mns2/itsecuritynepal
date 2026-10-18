<?php

namespace App\Providers;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Course;
use App\View\Components\Icon;
use App\View\Composers\SiteComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['layouts.app', 'partials.*', 'pages.*', 'errors.*', 'components.enquiry-form'], SiteComposer::class);

        Blade::component('icon', Icon::class);

        // Enquiry spam protection: max 5 submissions per minute and 20 per day per IP.
        RateLimiter::for('enquiries', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perDay(20)->by($request->ip()),
        ]);

        RateLimiter::for('exam-bookings', fn (Request $request) => [
            Limit::perMinute(3)->by($request->ip()),
            Limit::perDay(10)->by($request->ip()),
        ]);

        RateLimiter::for('newsletter', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perDay(20)->by($request->ip()),
        ]);

        // Navigation caches are refreshed whenever catalogue data changes in the admin panel.
        foreach ([Category::class, Course::class, Batch::class] as $model) {
            $model::saved(fn () => Cache::forget('nav.categories') || true);
            $model::saved(fn () => Cache::forget('nav.courseCount'));
            $model::deleted(fn () => Cache::forget('nav.categories'));
        }
    }
}
