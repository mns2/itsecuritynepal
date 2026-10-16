<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Course;
use App\Models\Faq;
use App\Models\LearningPath;
use App\Models\Page;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\TrainingMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

/**
 * Imports the approved Phase 1 prototype content (itself extracted from the live itsecuritynepal.com)
 * from database/seeders/data/*.json. Idempotent: re-running updates records by their natural keys.
 */
class ContentImportSeeder extends Seeder
{
    private array $site;

    public function run(): void
    {
        $dir = database_path('seeders/data');
        $this->site = json_decode(file_get_contents("$dir/site.json"), true, flags: JSON_THROW_ON_ERROR);
        $courses = json_decode(file_get_contents("$dir/courses.json"), true, flags: JSON_THROW_ON_ERROR);
        $posts = json_decode(file_get_contents("$dir/posts.json"), true, flags: JSON_THROW_ON_ERROR);

        $this->settings();
        $categoryIds = $this->categories();
        $courseIds = $this->courses($courses, $categoryIds);
        $this->learningPaths($courseIds);
        $this->batches($courseIds);
        $this->trainingMethods();
        $this->faqs();
        $this->posts($posts, $courseIds);
        $this->pages();
        $this->redirects();

        $this->command?->info(sprintf('Imported %d categories, %d courses, %d posts.', count($categoryIds), count($courseIds), count($posts)));
    }

    private function settings(): void
    {
        $s = $this->site;
        Setting::put('brand', Arr::only($s, ['name', 'legalName', 'tagline', 'description']));
        Setting::put('contact', $s['contact']);
        Setting::put('social', $s['social']);
        Setting::put('announcement', $s['announcement'] + ['enabled' => true]);
        Setting::put('trust', $s['trust']);
        Setting::put('audiences', $s['audiences']);
        Setting::put('analytics', ['ga4_id' => null, 'gtm_id' => null]);
    }

    /** @return array<string,int> category key => id */
    private function categories(): array
    {
        $ids = [];
        foreach ($this->site['categories'] as $i => $c) {
            $cat = Category::updateOrCreate(['key' => $c['id']], [
                'slug' => trim($c['url'], '/'),
                'name' => $c['name'],
                'short_name' => $c['short'],
                'icon' => $c['icon'],
                'image' => $this->stock('track-'.$c['id']),
                'tagline' => $c['tagline'],
                'intro' => $c['intro'],
                'objectives' => $c['objectives'],
                'faqs' => $c['faqs'],
                'learning_path_key' => $c['path'] ?? null,
                'sort' => $i,
                'show_on_home' => in_array($c['id'], ['cybersecurity', 'redhat', 'cisco', 'devops', 'microsoft', 'career'], true),
                'meta_title' => $c['name'].' Training in Kathmandu, Nepal | IT Security Nepal',
            ]);
            $ids[$c['id']] = $cat->id;
        }

        return $ids;
    }

    /** @return array<string,int> slug => id */
    private function courses(array $courses, array $categoryIds): array
    {
        $ids = [];
        foreach ($courses as $i => $c) {
            $course = Course::updateOrCreate(['slug' => $c['slug']], [
                'category_id' => $categoryIds[$c['category']],
                'code' => $c['code'],
                'official_code' => $c['official'] ?? true,
                'name' => $c['name'],
                'level' => $c['level'],
                'certification' => $c['certification'],
                'duration' => $c['duration'],
                'modes' => $c['modes'],
                'summary' => $c['summary'],
                'overview' => array_values(array_unique(array_merge($c['overview'] ?: [], $c['overview'] ? [] : ($c['intro'] ?? [])))),
                'outcomes' => $c['outcomes'],
                'prerequisites' => $c['prerequisites'],
                'benefits' => $c['benefits'],
                'careers' => $c['careers'],
                'modules' => $c['outline'],
                'faqs' => $c['faqs'],
                'extra_sections' => $c['extra'],
                'featured' => $c['featured'],
                'is_published' => true,
                'sort' => $i,
            ]);
            $ids[$c['slug']] = $course->id;
        }

        return $ids;
    }

    private function learningPaths(array $courseIds): void
    {
        foreach ($this->site['learningPaths'] as $i => $p) {
            LearningPath::updateOrCreate(['key' => $p['id']], [
                'name' => $p['name'],
                'icon' => $p['icon'],
                'goal' => $p['goal'],
                'steps' => collect($p['steps'])->filter(fn ($s) => isset($courseIds[$s['slug']]))
                    ->map(fn ($s) => ['course_id' => $courseIds[$s['slug']], 'note' => $s['note']])->values()->all(),
                'sort' => $i,
            ]);
        }
    }

    private function batches(array $courseIds): void
    {
        Batch::query()->delete();
        foreach ($this->site['batches'] as $i => $b) {
            Batch::create([
                'course_id' => $courseIds[$b['course']],
                'start_label' => $b['start'],
                'schedule' => $b['schedule'],
                'mode' => $b['mode'],
                'status' => $b['status'],
                'is_illustrative' => $b['source'] === 'illustrative',
                'sort' => $i,
            ]);
        }
    }

    private function trainingMethods(): void
    {
        foreach ($this->site['trainingMethods'] as $i => $m) {
            TrainingMethod::updateOrCreate(['slug' => trim($m['url'], '/')], [
                'name' => $m['name'],
                'icon' => $m['icon'],
                'short' => $m['short'],
                'body' => $m['body'],
                'best_for' => $m['bestFor'],
                'features' => $m['features'],
                'verification_note' => $m['verify'] ?? null,
                'sort' => $i,
            ]);
        }
    }

    private function faqs(): void
    {
        $groupMap = ['getting-started' => 'getting-started', 'training' => 'training', 'certification' => 'certification', 'fees' => 'fees', 'corporate' => 'corporate'];
        $homeQuestions = ['Do you provide training for complete beginners?', 'Are you an official Red Hat Training Partner?', 'Can I study online?', 'How much do courses cost?', 'Do you help with certification exams?', 'When do batches start?'];
        Faq::query()->delete();
        $sort = 0;
        foreach ($this->site['faqGroups'] as $g) {
            foreach ($g['items'] as $item) {
                Faq::create([
                    'group' => $groupMap[$g['id']] ?? $g['id'],
                    'question' => $item['q'],
                    'answer' => $item['a'],
                    'show_on_home' => in_array($item['q'], $homeQuestions, true),
                    'sort' => $sort++,
                ]);
            }
        }
        Faq::create([
            'group' => 'corporate',
            'question' => 'Can we train as a group?',
            'answer' => 'Yes. Friends, classmates or colleagues can train together in a private batch with a schedule agreed with the group, at our centre, on-site or live online.',
            'sort' => $sort,
        ]);
    }

    private function posts(array $posts, array $courseIds): void
    {
        foreach ($posts as $p) {
            Post::updateOrCreate(['slug' => $p['slug']], [
                'title' => $p['title'],
                'category' => $this->site['postCategories'][$p['slug']] ?? 'News',
                'excerpt' => $p['excerpt'],
                'featured_image' => $this->stock('post-'.$p['slug']),
                'body' => $p['body'],
                'related_course_ids' => collect($this->site['postCourses'][$p['slug']] ?? [])
                    ->map(fn ($s) => $courseIds[$s] ?? null)->filter()->values()->all(),
                'published_at' => $p['date'].' 09:00:00',
                'meta_title' => $p['title'].' | IT Security Nepal',
            ]);
        }
    }

    private function pages(): void
    {
        foreach ($this->site['legal'] as $l) {
            Page::firstOrCreate(['slug' => $l['slug']], ['title' => $l['title'], 'body' => null]);
        }
    }

    /** Path of a bundled stock photo on the public disk (see data/stock-photos.json), or null if missing. */
    private function stock(string $name): ?string
    {
        $path = "stock/{$name}.webp";

        return is_file(storage_path("app/public/{$path}")) ? $path : null;
    }

    /** Old live-site URLs that were broken, duplicated or are being retired (see scrape README §8). */
    private function redirects(): void
    {
        $map = [
            '/graphics-designing-with-ai-essential/' => '/graphic-design-with-ai-masterclass/',
            '/digital-marketing/' => '/course/',
            '/microsoft-devops-solutions/' => '/az-400-microsoft-devops-course/',
            '/data-engineering-on-microsoft-azure/' => '/microsoft-azure-data-engineer/',
            '/designing-microsoft-azure-infrastructure-solutions/' => '/azure-infrastructure-architect-course/',
            '/cl210-red-hat-openstack-administration-i/' => '/cl210-openstack-administration-ii-red-hat/',
            '/devops-essential/' => '/devops-mastery/',
            '/devops-certified-kubernetes-administrator-cka/' => '/certified-kubernetes-administrator/',
            '/ccna-cisco/' => '/cisco-certified-network-associate/',
            '/rh124-red-hat-system-administration-i-rh124/' => '/red-hat-system-administration/',
            '/course-inquiry/' => '/contact/',
            '/learning-path/' => '/course-finder/',
            '/team-one/' => '/instructors/',
            '/team-extra/' => '/instructors/',
            '/home-test/' => '/',
            '/my-account/' => '/',
            '/my-account-2/' => '/',
            '/student-registration/' => '/contact/',
            '/student-registration-2/' => '/contact/',
            '/instructor-registration/' => '/contact/',
            '/instructor-registration-2/' => '/contact/',
            '/404-error/' => '/',
        ];
        foreach ($map as $from => $to) {
            Redirect::updateOrCreate(['from_path' => $from], ['to_path' => $to, 'status_code' => 301]);
        }
    }
}
