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
            ['/batches/', null], ['/faq/', null], ['/career-pathways/', null], ['/red-hat-exam-booking-nepal/', null],
        ])
            ->concat(Category::all()->map(fn ($c) => [$c->url(), $c->updated_at]))
            ->concat(Course::published()->get()->map(fn ($c) => [$c->url(), $c->updated_at]))
            ->concat(Post::published()->get()->map(fn ($p) => [$p->url(), $p->updated_at]))
            ->concat(TrainingMethod::where('is_published', true)->get()->map(fn ($m) => [$m->url(), $m->updated_at]))
            ->concat(Instructor::where('is_published', true)->get()->map(fn ($i) => ['/instructors/'.$i->slug.'/', $i->updated_at]))
            ->concat(Page::where('is_published', true)->get()->filter(fn ($p) => filled($p->body))->map(fn ($p) => ['/'.$p->slug.'/', $p->updated_at]))
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
        if (! \App\Support\Seo::indexable()) {
            $lines = ['User-agent: *', 'Disallow: /']; // never index staging / local copies
        } else {
            $lines = ['User-agent: *', 'Disallow: /admin', 'Disallow: /trainer', 'Disallow: /livewire', 'Disallow: /enquiries', 'Disallow: /newsletter/', 'Disallow: /*?q=', ''];
            if (! config('seo.allow_ai_crawlers')) {
                foreach (['GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-SearchBot', 'PerplexityBot', 'Google-Extended', 'Applebot-Extended', 'CCBot', 'Bytespider'] as $bot) {
                    $lines[] = "User-agent: {$bot}";
                }
                array_push($lines, 'Disallow: /', '');
            }
            $lines[] = 'Sitemap: '.rtrim(config('app.url'), '/').'/sitemap.xml';
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** RSS 2.0 feed of blog posts at /feed/ (the old WordPress feed URL, so existing readers keep working). */
    public function feed(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $brand = \App\Models\Setting::get('brand.name', 'IT Security Nepal');
        $posts = Post::published()->latest('published_at')->limit(30)->get();
        $esc = fn (?string $v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel>'
            .'<title>'.$esc($brand.' Blog').'</title><link>'.$esc($base.'/blog/').'</link>'
            .'<description>'.$esc('IT career and certification guides from '.$brand.', Kathmandu.').'</description>'
            .'<language>en</language><atom:link href="'.$esc($base.'/feed/').'" rel="self" type="application/rss+xml"/>'
            .($posts->isNotEmpty() ? '<lastBuildDate>'.$posts->max('updated_at')->toRssString().'</lastBuildDate>' : '');
        foreach ($posts as $p) {
            $xml .= '<item><title>'.$esc($p->title).'</title><link>'.$esc($base.$p->url()).'</link>'
                .'<guid isPermaLink="true">'.$esc($base.$p->url()).'</guid>'
                .'<pubDate>'.$p->published_at->toRssString().'</pubDate>'
                .'<description>'.$esc($p->excerptText()).'</description></item>';
        }
        $xml .= '</channel></rss>';

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8', 'X-Robots-Tag' => 'noindex, follow']);
    }

    /**
     * /llms.txt: a plain Markdown overview for AI assistants and answer engines (llmstxt.org format), built only
     * from what the site itself publishes: business details from Site settings, tracks, courses and guides.
     */
    public function llms(): Response
    {
        $base = rtrim(config('app.url'), '/');
        $site = \App\Models\Setting::allValues();
        $brand = $site['brand'] ?? [];
        $c = $site['contact'] ?? [];
        $line = fn (string $title, string $path, ?string $note = null) => "- [{$title}]({$base}{$path})".($note ? ': '.preg_replace('/\s+/', ' ', trim($note)) : '');

        $out = ['# '.($brand['name'] ?? 'IT Security Nepal'), ''];
        if (! empty($brand['description'])) {
            $out[] = '> '.$brand['description'];
            $out[] = '';
        }
        $out[] = collect([
            ! empty($brand['legalName']) ? 'Registered name: '.$brand['legalName'].'.' : null,
            ! empty($c['address']) ? 'Location: '.$c['address'].'.' : null,
            ! empty($c['phonePrimary']['label']) ? 'Phone: '.$c['phonePrimary']['label'].(! empty($c['phoneOffice']['label']) ? ', '.$c['phoneOffice']['label'] : '').'.' : null,
            ! empty($c['email']) ? 'Email: '.$c['email'].'.' : null,
            ! empty($c['hours']) ? 'Opening hours: '.$c['hours'].'.' : null,
            'Training is offered for individuals, groups and corporate batches, in class in Kathmandu or live online; each course page lists the modes it offers.',
        ])->filter()->implode("\n");
        $out[] = '';

        $out[] = '## Key pages';
        foreach ([['All courses', '/course/'], ['Upcoming batches', '/batches/'], ['Course Finder', '/course-finder/'], ['Career pathways', '/career-pathways/'],
            ['Red Hat exam booking', '/red-hat-exam-booking-nepal/'], ['Training methods', '/mode-of-training/'], ['Group training', '/group-training/'],
            ['Corporate training', '/corporate-training/'], ['About', '/about/'], ['FAQ', '/faq/'], ['Contact', '/contact/']] as [$t, $u]) {
            $out[] = $line($t, $u);
        }
        $out[] = '';

        $courses = Course::published()->with('category')->orderBy('sort')->get()->groupBy('category_id');
        foreach (Category::orderBy('sort')->get() as $cat) {
            if (! ($list = $courses->get($cat->id))) {
                continue;
            }
            $out[] = '## '.$cat->name.' courses';
            $out[] = $line($cat->name.' overview', $cat->url());
            foreach ($list as $course) {
                $title = ($course->official_code ? $course->code.': ' : '').$course->name;
                $meta = collect([$course->level, $course->certification ? 'prepares for '.$course->certification : null])->filter()->implode(', ');
                $out[] = $line($title, $course->url(), trim(($meta ? "({$meta}) " : '').\Illuminate\Support\Str::limit((string) $course->summary, 200)));
            }
            $out[] = '';
        }

        $posts = Post::published()->latest('published_at')->get();
        if ($posts->isNotEmpty()) {
            $out[] = '## Guides';
            foreach ($posts as $p) {
                $out[] = $line($p->title, $p->url(), \Illuminate\Support\Str::limit($p->excerptText(), 200));
            }
            $out[] = '';
        }

        $out[] = '## Optional';
        $out[] = $line('Sitemap', '/sitemap.xml');
        $out[] = $line('Blog feed (RSS)', '/feed/');

        return response(implode("\n", $out)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600']);
    }
}
