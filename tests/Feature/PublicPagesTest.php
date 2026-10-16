<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Course;
use App\Models\Post;
use Database\Seeders\ContentImportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentImportSeeder::class);
    }

    public static function staticPages(): array
    {
        return array_map(fn ($p) => [$p], ['/', '/course/', '/course-finder/', '/group-training/', '/corporate-training/', '/mode-of-training/', '/about/', '/instructors/', '/blog/', '/contact/', '/batches/', '/faq/']);
    }

    #[DataProvider('staticPages')]
    public function test_static_pages_render_with_one_h1_and_canonical(string $path): void
    {
        $res = $this->get($path)->assertOk();
        $html = $res->getContent();
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html), "Exactly one <h1> on {$path}");
        $this->assertStringContainsString('<link rel="canonical" href="'.rtrim(config('app.url'), '/').$path.'"', $html);
        $this->assertStringContainsString('application/ld+json', $html);
    }

    public function test_every_category_course_post_method_and_legal_page_renders(): void
    {
        $paths = Category::all()->map->url()
            ->concat(Course::all()->map->url())
            ->concat(Post::all()->map->url())
            ->concat(['/classroom-training/', '/live-virtual-training/', '/one-on-one-training/', '/flip-class/', '/privacy-policy/', '/terms-and-conditions/']);

        foreach ($paths as $path) {
            $html = $this->get($path)->assertOk()->getContent();
            $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html), "Exactly one <h1> on {$path}");
        }
        $this->assertGreaterThan(55, $paths->count());
    }

    public function test_course_page_has_course_and_faq_structured_data(): void
    {
        $html = $this->get('/red-hat-system-administration/')->assertOk()->getContent();
        $this->assertStringContainsString('"@type":"Course"', $html);
        $this->assertStringContainsString('"@type":"FAQPage"', $html);
        $this->assertStringContainsString('"@type":"BreadcrumbList"', $html);
        $this->assertStringContainsString('RH124: Red Hat System Administration I Course in Nepal', $html);
    }

    public function test_unpublished_courses_are_hidden(): void
    {
        Course::where('slug', 'comptia-server')->update(['is_published' => false]);
        $this->get('/comptia-server/')->assertNotFound();
        $this->get('/course/')->assertDontSee('/comptia-server/', false);
    }

    public function test_urls_without_trailing_slash_redirect_permanently(): void
    {
        $this->get('/course')->assertStatus(301)->assertRedirect('http://localhost/course/');
        $this->get('/redhat?x=1')->assertStatus(301)->assertRedirect('http://localhost/redhat/?x=1');
    }

    public function test_legacy_urls_redirect_to_their_new_pages(): void
    {
        $this->get('/devops-essential/')->assertStatus(301)->assertRedirect('http://localhost/devops-mastery/');
        $this->get('/graphics-designing-with-ai-essential/')->assertRedirect('http://localhost/graphic-design-with-ai-masterclass/');
    }

    public function test_unknown_pages_return_404_page(): void
    {
        $this->get('/no-such-page/')->assertNotFound()->assertSee('We couldn', false);
    }

    public function test_sitemap_robots_and_course_index(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('/red-hat-system-administration/', false)->assertSee('/rhcsa-certification-guide/', false);
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /'); // non-production: everything disallowed
        $this->getJson('/courses-index.json')->assertOk()->assertJsonCount(Course::published()->count())
            ->assertJsonFragment(['slug' => 'linux-bootcamp']);
    }

    public function test_non_production_pages_are_noindex(): void
    {
        $this->get('/')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_home_shows_individual_and_group_training(): void
    {
        $this->get('/')->assertSee('Individual training')->assertSee('Group training')->assertDontSee('1,580+');
    }
}
