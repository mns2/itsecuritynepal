<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/** Shares site-wide data (settings, navigation categories) with every public view. */
class SiteComposer
{
    /** Computed once per request (the composer runs for the layout and every partial). */
    private static ?array $data = null;

    private static ?int $requestId = null;

    public function compose(View $view): void
    {
        $rid = spl_object_id(request());
        if (self::$requestId !== $rid) {
            self::$data = null;
            self::$requestId = $rid;
        }

        self::$data ??= [
            'site' => Setting::allValues(),
            // Not cached: Laravel's cache refuses to unserialize models (cache.serializable_classes); the query is tiny.
            'navCategories' => Category::query()->orderBy('sort')
                ->with(['publishedCourses' => fn ($q) => $q->select('id', 'category_id', 'code', 'name', 'slug', 'sort')])
                ->get(),
            'courseCount' => Cache::remember('nav.courseCount', 600, fn () => Course::published()->count()),
        ];

        $view->with(self::$data);
    }
}
