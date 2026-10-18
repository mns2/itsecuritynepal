<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\ContentImportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Indexing per host, structured data validity, feeds and sitemap rules. */
class SearchVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentImportSeeder::class);
        // As on the live server today: staging values in .env while the main domain points at the site.
        config(['app.url' => 'https://new.itsecuritynepal.com', 'seo.primary_host' => 'itsecuritynepal.com', 'seo.indexing' => null]);
    }

    /** @return array<int, array> every JSON-LD block on the page, decoded */
    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return array_map(fn ($b) => json_decode($b, true, 512, JSON_THROW_ON_ERROR), $m[1]);
    }

    public function test_main_domain_is_indexable_with_its_own_canonicals_even_with_a_staging_env(): void
    {
        $html = $this->get('https://itsecuritynepal.com'.'/redhat/')->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="https://itsecuritynepal.com/redhat/">', $html);
        $this->assertStringContainsString('<meta name="robots" content="index, follow', $html);
        $this->assertStringContainsString('<meta property="og:url" content="https://itsecuritynepal.com/redhat/">', $html);
        $this->assertStringNotContainsString('new.itsecuritynepal.com', $html);

        $robots = $this->get('https://itsecuritynepal.com'.'/robots.txt')->assertOk()->getContent();
        $this->assertStringContainsString('Sitemap: https://itsecuritynepal.com/sitemap.xml', $robots);
        $this->assertStringContainsString('Disallow: /admin', $robots);
        $this->assertStringNotContainsString("Disallow: /\n", $robots);

        $sitemap = $this->get('https://itsecuritynepal.com'.'/sitemap.xml')->assertOk()->getContent();
        $this->assertStringContainsString('<loc>https://itsecuritynepal.com/red-hat-system-administration/</loc>', $sitemap);
        $this->assertStringNotContainsString('new.itsecuritynepal.com', $sitemap);
    }

    public function test_staging_subdomain_stays_out_of_search_even_in_production(): void
    {
        $this->app['env'] = 'production';

        $this->get('https://new.itsecuritynepal.com'.'/')->assertOk()->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        $this->assertSame("User-agent: *\nDisallow: /\n", $this->get('https://new.itsecuritynepal.com'.'/robots.txt')->getContent());
    }

    public function test_ai_crawlers_can_be_blocked_from_settings(): void
    {
        $this->assertStringNotContainsString('GPTBot', $this->get('https://itsecuritynepal.com'.'/robots.txt')->getContent());

        config(['seo.allow_ai_crawlers' => false]);
        $robots = $this->get('https://itsecuritynepal.com'.'/robots.txt')->getContent();
        $this->assertStringContainsString("User-agent: GPTBot", $robots);
        $this->assertStringContainsString('Sitemap: https://itsecuritynepal.com/sitemap.xml', $robots);
    }

    public function test_structured_data_is_valid_json_ld_on_every_page_type(): void
    {
        $pages = ['/' => ['WebSite', 'WebPage'], '/about/' => ['AboutPage'], '/contact/' => ['ContactPage'], '/course/' => ['CollectionPage'],
            '/redhat/' => ['CollectionPage', 'BreadcrumbList'], '/red-hat-system-administration/' => ['Course', 'BreadcrumbList', 'FAQPage'],
            '/rhcsa-certification-guide/' => ['BlogPosting'], '/faq/' => ['FAQPage'], '/red-hat-exam-booking-nepal/' => ['Service']];

        foreach ($pages as $url => $expected) {
            $types = [];
            foreach ($this->jsonLd($this->get('https://itsecuritynepal.com'.$url)->assertOk()->getContent()) as $block) {
                $this->assertSame('https://schema.org', $block['@context'] ?? null, "{$url}: block without @context");
                foreach (array_keys($block) as $key) {
                    $this->assertStringStartsNotWith('<?php', (string) $key, "{$url}: Blade compiled a key");
                }
                foreach ($block['@graph'] ?? [$block] as $node) {
                    $types = array_merge($types, (array) $node['@type']);
                }
            }
            foreach ($expected as $t) {
                $this->assertContains($t, $types, "{$url} should have {$t}");
            }
            $this->assertContains('LocalBusiness', $types, "{$url} should describe the business");
            $this->assertLessThanOrEqual(1, count(array_keys($types, 'FAQPage')), "{$url}: only one FAQPage");
        }
    }

    public function test_course_markup_and_share_image_use_the_pages_own_content(): void
    {
        $html = $this->get('https://itsecuritynepal.com'.'/red-hat-system-administration/')->getContent();
        $course = collect($this->jsonLd($html))->firstWhere('@type', 'Course');

        $this->assertSame('RH124', $course['courseCode']);
        $this->assertNotEmpty($course['coursePrerequisites']);
        $this->assertStringContainsString('<meta property="og:image:width" content="1200">', $html);
        $this->assertMatchesRegularExpression('#<meta property="og:image" content="https://itsecuritynepal.com/[^"]+\.(jpg|png)">#', $html);
    }

    public function test_long_titles_drop_the_brand_suffix_but_short_ones_keep_it(): void
    {
        $this->get('https://itsecuritynepal.com'.'/cissp-certified-information-systems-security-professional/')
            ->assertSee('<title>CISSP: Certified Information Systems Security Professional Course in Nepal</title>', false);
        $this->get('https://itsecuritynepal.com'.'/faq/')->assertSee('<title>Frequently Asked Questions | IT Security Nepal</title>', false);
    }

    public function test_rss_feed_and_llms_txt(): void
    {
        $this->get('https://itsecuritynepal.com'.'/feed')->assertRedirect('https://itsecuritynepal.com/feed/');
        $feed = $this->get('https://itsecuritynepal.com'.'/feed/')->assertOk()->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')->getContent();
        $this->assertNotFalse(simplexml_load_string($feed));
        $this->assertStringContainsString('<link>https://itsecuritynepal.com/rhcsa-certification-guide/</link>', $feed);

        $llms = $this->get('https://itsecuritynepal.com'.'/llms.txt')->assertOk()->getContent();
        $this->assertStringStartsWith('# IT Security Nepal', $llms);
        $this->assertStringContainsString('(https://itsecuritynepal.com/red-hat-system-administration/)', $llms);
        $this->assertStringContainsString('info@itsecuritynepal.com', $llms);
    }

    public function test_noindexed_legal_pages_are_left_out_of_the_sitemap(): void
    {
        Page::query()->update(['body' => null]);
        $sitemap = $this->get('https://itsecuritynepal.com'.'/sitemap.xml')->getContent();
        $this->assertStringNotContainsString('/privacy-policy/', $sitemap);
        $this->get('https://itsecuritynepal.com'.'/privacy-policy/')->assertSee('noindex', false);
    }
}
