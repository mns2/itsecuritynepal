<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Category;
use App\Models\LearningPath;
use App\Models\Course;
use App\Models\Instructor;
use App\Models\Page;
use App\Models\Post;
use App\Models\TrainingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    /** JSON used by the header search overlay and Course Finder. */
    public function courseIndex(): JsonResponse
    {
        $data = Cache::remember('courses.index.json', 600, fn () => Course::published()->with('category')
            ->orderByDesc('featured')->orderBy('sort')->get()->map->toIndexArray()->values()->all());

        return response()->json($data)->header('Cache-Control', 'public, max-age=600');
    }

    public function sitemap(): Response
    {
        $base = rtrim(config('app.url'), '/');
        // Pages hidden from Google in Admin → SEO are left out of the sitemap too.
        $hidden = \App\Models\SeoMeta::where('robots', 'like', 'noindex%')->pluck('path')->all();
        $urls = self::urls()->reject(fn ($u) => in_array($u[0], $hidden, true));

        return $this->renderSitemap($base, $urls);
    }

    /** Every public, indexable URL as [path, lastModified] (also used by Admin → SEO to pick a page). */
    public static function urls(): \Illuminate\Support\Collection
    {
        return collect([
            ['/', now()], ['/course/', null], ['/course-finder/', null], ['/group-training/', null], ['/corporate-training/', null],
            ['/mode-of-training/', null], ['/about/', null], ['/instructors/', null], ['/blog/', null], ['/contact/', null],
            ['/batches/', null], ['/faq/', null], ['/career-pathways/', null],
        ])
            ->concat(Category::all()->map(fn ($c) => [$c->url(), $c->updated_at]))
            ->concat(Course::published()->get()->map(fn ($c) => [$c->url(), $c->updated_at]))
            ->concat(Post::published()->get()->map(fn ($p) => [$p->url(), $p->updated_at]))
            ->concat(TrainingMethod::where('is_published', true)->get()->map(fn ($m) => [$m->url(), $m->updated_at]))
            ->concat(Instructor::where('is_published', true)->get()->map(fn ($i) => ['/instructors/'.$i->slug.'/', $i->updated_at]))
            ->concat(Page::where('is_published', true)->get()->map(fn ($p) => ['/'.$p->slug.'/', $p->updated_at]))
            ->concat(LearningPath::where('is_published', true)->whereNotNull('slug')->get()->map(fn ($p) => [$p->url(), $p->updated_at]))
            ->concat(Campaign::live()->where('has_landing_page', true)->get()->map(fn ($c) => [$c->landingUrl(), $c->updated_at]));
    }

    private function renderSitemap(string $base, \Illuminate\Support\Collection $urls): Response
    {

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as [$path, $mod]) {
            $xml .= '  <url><loc>'.e($base.$path).'</loc>'.($mod ? '<lastmod>'.$mod->toAtomString().'</lastmod>' : '')."</url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = app()->isProduction()
            ? ['User-agent: *', 'Disallow: /admin', 'Disallow: /livewire', 'Disallow: /enquiries', 'Disallow: /*?q=', '', 'Sitemap: '.rtrim(config('app.url'), '/').'/sitemap.xml']
            : ['User-agent: *', 'Disallow: /']; // never index staging / local copies

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
