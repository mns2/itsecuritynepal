<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Course;
use App\Models\Faq;
use App\Models\Instructor;
use App\Models\LearningPath;
use App\Models\Page;
use App\Models\Post;
use App\Models\Testimonial;
use App\Models\TrainingMethod;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    public function home(): View
    {
        $courses = Course::published()->with(['category', 'batches'])->orderByDesc('featured')->orderBy('sort')->get();

        return view('pages.home', [
            'courses' => $courses,
            'homeTracks' => Category::where('show_on_home', true)->withCount(['courses as published_courses_count' => fn ($q) => $q->where('is_published', true)])->orderBy('sort')->get(),
            'categories' => Category::orderBy('sort')->get(),
            'paths' => LearningPath::where('is_published', true)->orderBy('sort')->get(),
            'batches' => $this->batchQuery()->limit(5)->get(),
            'faqs' => Faq::where('is_published', true)->where('show_on_home', true)->orderBy('sort')->get(),
            'testimonials' => Testimonial::visible()->with('course')->limit(3)->get(),
            'featured' => $courses->keyBy('slug'),
        ]);
    }

    public function courses(): View
    {
        return view('pages.courses', [
            'courses' => Course::published()->with(['category', 'batches'])->orderByDesc('featured')->orderBy('sort')->get(),
            'categories' => Category::orderBy('sort')->withCount(['courses as published_courses_count' => fn ($q) => $q->where('is_published', true)])->get(),
        ]);
    }

    public function courseFinder(): View
    {
        return view('pages.course-finder');
    }

    public function groupTraining(): View
    {
        return view('pages.group-training', [
            'popular' => Course::published()->with(['category', 'batches'])
                ->whereIn('slug', content('group.popular_courses', []))->get()
                ->sortBy(fn ($c) => array_search($c->slug, content('group.popular_courses', [])))->values(),
        ]);
    }

    public function corporate(): View
    {
        return view('pages.corporate', ['categories' => Category::orderBy('sort')->get()]);
    }

    public function trainingMethods(): View
    {
        return view('pages.training-methods', ['methods' => TrainingMethod::where('is_published', true)->orderBy('sort')->get()]);
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function instructors(): View
    {
        return view('pages.instructors', [
            'instructors' => Instructor::where('is_published', true)->with('category')->orderBy('sort')->get(),
            'categories' => Category::orderBy('sort')->get(),
        ]);
    }

    public function instructor(string $slug): View
    {
        $instructor = Instructor::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('pages.instructor', ['instructor' => $instructor]);
    }

    public function blog(): View
    {
        $posts = Post::published()->latest('published_at')->get();

        return view('pages.blog', ['posts' => $posts, 'featuredPost' => $posts->first()]);
    }

    public function contact(): View
    {
        return view('pages.contact', ['faqs' => Faq::where('is_published', true)->whereIn('group', ['fees', 'training'])->orderBy('sort')->limit(3)->get()]);
    }

    public function batches(): View
    {
        $batches = $this->batchQuery()->get();

        return view('pages.batches', [
            'batches' => $batches,
            'categories' => Category::orderBy('sort')->get()->whereIn('key', $batches->pluck('course.category.key')->unique()),
        ]);
    }

    public function faq(): View
    {
        return view('pages.faq', ['groups' => Faq::where('is_published', true)->orderBy('sort')->get()->groupBy('group')]);
    }

    /** Root-level legacy slugs: category → course → post → training method → legal page. */
    public function slug(string $slug): View
    {
        if ($category = Category::where('slug', $slug)->first()) {
            return $this->category($category);
        }
        if ($course = Course::published()->where('slug', $slug)->with(['category', 'batches'])->first()) {
            return view('pages.course', ['course' => $course, 'related' => $course->related()]);
        }
        if ($post = Post::published()->where('slug', $slug)->first()) {
            return $this->post($post);
        }
        if ($method = TrainingMethod::where('slug', $slug)->where('is_published', true)->first()) {
            return view('pages.training-method', [
                'method' => $method,
                'courses' => Course::published()->with(['category', 'batches'])->where('featured', true)->orderBy('sort')->limit(4)->get(),
            ]);
        }
        if ($page = Page::where('slug', $slug)->where('is_published', true)->first()) {
            return view('pages.legal', ['page' => $page]);
        }

        abort(404);
    }

    private function category(Category $category): View
    {
        $category->load(['publishedCourses' => fn ($q) => $q->with(['category', 'batches'])]);

        return view('pages.category', ['category' => $category, 'path' => $category->learningPath()]);
    }

    private function post(Post $post): View
    {
        // Render Markdown: demote any H1 (the page owns the only H1), add heading ids for the table of contents.
        $html = Str::markdown($post->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
        $toc = [];
        $html = preg_replace_callback('#<h([1-4])>(.*?)</h\1>#s', function ($m) use (&$toc) {
            $level = max(2, (int) $m[1]);
            $text = trim(strip_tags($m[2]));
            $id = Str::slug($text);
            if ($level === 2) {
                $toc[] = ['id' => $id, 'text' => $text];
            }

            return "<h{$level} id=\"{$id}\">{$m[2]}</h{$level}>";
        }, $html);
        $html = preg_replace('#<table>#', '<div class="table-wrap" tabindex="0" role="region" aria-label="Table"><table>', $html);
        $html = str_replace('</table>', '</table></div>', $html);
        $html = str_replace('href="https://itsecuritynepal.com/', 'href="/', $html);

        return view('pages.article', [
            'post' => $post,
            'html' => $html,
            'toc' => $toc,
            'related' => Post::published()->whereKeyNot($post->id)->latest('published_at')->limit(3)->get(),
            'relatedCourses' => $post->relatedCourses(),
        ]);
    }

    private function batchQuery()
    {
        return Batch::where('is_published', true)
            ->whereHas('course', fn ($q) => $q->where('is_published', true))
            ->with('course.category')->orderBy('sort');
    }
}
