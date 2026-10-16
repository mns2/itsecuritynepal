<?php

namespace Tests\Feature;

use App\Mail\ConfirmSubscription;
use App\Mail\NewsletterEmail;
use App\Models\Campaign;
use App\Models\EmailCampaign;
use App\Models\Enquiry;
use App\Models\Post;
use App\Models\SeoMeta;
use App\Models\Subscriber;
use App\Models\User;
use Database\Seeders\CareerPathwaysSeeder;
use Database\Seeders\ContentImportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class SeoAndMarketingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ContentImportSeeder::class, CareerPathwaysSeeder::class]);
    }

    public function test_custom_seo_overrides_a_static_page_and_prints_safe_schema(): void
    {
        SeoMeta::create([
            'path' => '/about',
            'title' => 'About IT Security Nepal – Red Hat training partner',
            'description' => 'Custom description for the about page.',
            'og_title' => 'Share title for about',
            'canonical' => '/about/',
            'robots' => 'noindex, follow',
            'schema' => [['label' => 'FAQ', 'json' => json_encode(['@type' => 'FAQPage', 'mainEntity' => [['@type' => 'Question', 'name' => 'Q </script><script>alert(1)</script>', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'A']]]])]],
        ]);

        $html = $this->get('/about/')->assertOk()->getContent();
        $this->assertStringContainsString('<title>About IT Security Nepal – Red Hat training partner</title>', $html);
        $this->assertStringContainsString('content="Custom description for the about page."', $html);
        $this->assertStringContainsString('<meta property="og:title" content="Share title for about">', $html);
        $this->assertStringContainsString('"@type": "FAQPage"', $html);
        $this->assertStringContainsString('"@context": "https://schema.org"', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html); // "<" is escaped inside JSON-LD

        // noindex pages leave the sitemap
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/about/</loc>', false)->assertSee('/contact/</loc>', false);
    }

    public function test_blog_post_seo_follows_the_post(): void
    {
        $post = Post::published()->first();
        $post->seo()->create(['title' => 'Custom blog SEO title', 'schema' => [['label' => 'Video', 'json' => '{"@type":"VideoObject","name":"Demo"}']]]);

        $this->assertSame($post->url(), $post->seo->path);
        $this->get($post->url())->assertOk()->assertSee('<title>Custom blog SEO title</title>', false)->assertSee('"@type": "VideoObject"', false);
    }

    public function test_career_pathway_pages(): void
    {
        $this->get('/career-pathways/')->assertOk()->assertSee('Career pathways')->assertSee('DevOps Engineer');
        $this->get('/career-pathways/devops-engineer/')->assertOk()->assertSee('What the job involves')->assertSee('Your steps')->assertSee('Kubernetes');
        $this->get('/career-pathways/does-not-exist/')->assertNotFound();
        $this->get('/sitemap.xml')->assertSee('/career-pathways/devops-engineer/</loc>', false);
    }

    public function test_campaign_popup_landing_page_and_lead_attribution(): void
    {
        $c = Campaign::create(['name' => 'Test offer', 'slug' => 'test-offer', 'is_active' => true, 'style' => 'card', 'headline' => 'Test headline', 'cta_label' => 'Claim', 'ends_at' => now()->addDay(), 'show_countdown' => true, 'offer_code' => 'TEST10']);

        $this->get('/')->assertOk()->assertSee('Test headline')->assertSee('sitePopup(', false);
        $this->get('/offer/test-offer/')->assertOk()->assertSee('Test headline')->assertSee('TEST10')->assertSee('Offer ends in');
        $this->postJson(route('campaigns.track', $c), ['event' => 'view'])->assertOk();
        $this->assertSame(1, $c->fresh()->views);

        // Master switch off: no pop-up, landing page still works
        \App\Models\Setting::put('popups', ['campaigns_enabled' => false]);
        $this->get('/')->assertDontSee('Test headline');
        $this->get('/offer/test-offer/')->assertOk();

        // Inactive campaigns disappear
        $c->update(['is_active' => false]);
        $this->get('/offer/test-offer/')->assertNotFound();
    }

    public function test_course_finder_popup_can_be_switched_off(): void
    {
        $this->get('/')->assertSee('cf-popup-title', false);
        $this->get('/course-finder/')->assertDontSee('cf-popup-title', false);
        \App\Models\Setting::put('popups', ['course_finder' => ['enabled' => false]]);
        $this->get('/')->assertDontSee('cf-popup-title', false);
    }

    public function test_newsletter_double_opt_in_unsubscribe_and_sending(): void
    {
        Mail::fake();
        $this->travel(5)->seconds();
        $issued = encrypt(now()->subSeconds(10)->timestamp);

        $this->postJson('/newsletter', ['email' => 'Reader@Example.com', 'consent' => '1', 'issued' => $issued, 'source' => 'footer'])
            ->assertOk()->assertJson(['result' => 'pending']);
        $s = Subscriber::sole();
        $this->assertSame(['reader@example.com', 'pending', 'footer'], [$s->email, $s->status, $s->source]);
        Mail::assertSent(ConfirmSubscription::class);

        $this->postJson('/newsletter', ['email' => 'x@example.com', 'issued' => $issued])->assertStatus(422)->assertJsonValidationErrors('consent');

        $this->get($s->confirmUrl())->assertOk()->assertSee("You're subscribed", false);
        $this->assertSame('subscribed', $s->fresh()->status);

        // Email campaign: queue + send in batches; unsubscribed people are skipped
        $other = Subscriber::create(['email' => 'gone@example.com', 'status' => 'subscribed']);
        $email = EmailCampaign::create(['subject' => 'New batch', 'body' => 'Hello **there**', 'cta_label' => 'Book', 'cta_url' => 'https://itsecuritynepal.com/batches/']);
        $this->assertSame(2, $email->queueForSending());
        $other->unsubscribe();
        Artisan::call('newsletter:send');
        Mail::assertSent(NewsletterEmail::class, 1);
        $this->assertSame(['sent', 1], [$email->fresh()->status, $email->fresh()->sent_count]);

        // Click tracking + unsubscribe (GET shows a button, POST unsubscribes, also one-click without CSRF)
        $this->get(route('newsletter.click', $email))->assertRedirect('https://itsecuritynepal.com/batches/');
        $this->assertSame(1, $email->fresh()->clicks);
        $this->get($s->unsubscribeUrl())->assertOk()->assertSee('Yes, unsubscribe me');
        $this->assertSame('subscribed', $s->fresh()->status);
        $this->post(route('newsletter.unsubscribe.do', $s->token))->assertOk();
        $this->assertSame('unsubscribed', $s->fresh()->status);
    }

    public function test_admin_page_sends_campaigns_without_cron_within_the_limit(): void
    {
        Mail::fake();
        $this->actingAs(User::factory()->create());
        \App\Models\Setting::put('popups', ['newsletter' => ['per_minute' => 8]]);
        foreach (range(1, 5) as $i) {
            Subscriber::create(['email' => "reader{$i}@example.com", 'status' => 'subscribed']);
        }
        $email = EmailCampaign::create(['subject' => 'Batch test', 'body' => 'Hi']);
        $email->queueForSending();

        $this->get('/admin/email-campaigns')->assertOk()->assertSee('Keep this page open')->assertSee('Sends from this page');

        // Each 15-second tick sends a quarter of the per-minute limit (8 / 4 = 2)
        $widget = Livewire::test(\App\Filament\Widgets\NewsletterSending::class);
        $widget->call('tick');
        Mail::assertSent(NewsletterEmail::class, 2);
        $widget->call('tick')->call('tick');
        Mail::assertSent(NewsletterEmail::class, 5);
        $this->assertSame(['sent', 5], [$email->fresh()->status, $email->fresh()->sent_count]);

        // Nothing left: further ticks do nothing; the per-minute budget is shared with the cron command
        $widget->call('tick');
        Mail::assertSent(NewsletterEmail::class, 5);
        $this->assertFalse(\App\Support\NewsletterSender::cronActive());
        \App\Support\NewsletterSender::heartbeat();
        $this->assertTrue(\App\Support\NewsletterSender::cronActive());
    }

    public function test_enquiry_newsletter_tick_box_is_opt_in(): void
    {
        Mail::fake();
        $base = ['form' => 'course', 'name' => 'Sita', 'phone' => '9812345678', 'email' => 'sita@example.com', 'course' => 'not-sure', 'consent' => '1', 'issued' => encrypt(now()->subSeconds(10)->timestamp)];
        $this->postJson('/enquiries', $base)->assertOk();
        $this->assertSame(0, Subscriber::count());
        $this->postJson('/enquiries', ['email' => 'ram@example.com', 'newsletter' => '1'] + $base)->assertOk();
        $this->assertSame(['ram@example.com', 'enquiry'], [Subscriber::sole()->email, Subscriber::sole()->source]);
        $this->assertNotNull(Subscriber::sole()->enquiry_id);
    }

    public function test_all_new_admin_screens_render_for_admins(): void
    {
        $this->actingAs(User::factory()->create());
        Campaign::create(['name' => 'C', 'slug' => 'c', 'style' => 'card', 'headline' => 'H', 'cta_label' => 'Go']);
        $email = EmailCampaign::create(['subject' => 'S', 'body' => 'B']);
        $seo = SeoMeta::create(['path' => '/contact/', 'title' => 'T']);
        $post = Post::first();
        $enquiry = Enquiry::create(['form' => 'walkin', 'name' => 'Walk In', 'phone' => '9800000000', 'email' => '', 'consent' => true]);

        foreach (['/admin/campaigns', '/admin/campaigns/create', '/admin/subscribers', '/admin/subscribers/create', '/admin/email-campaigns', '/admin/email-campaigns/create',
            "/admin/email-campaigns/{$email->id}/edit", '/admin/payments', '/admin/users', '/admin/users/create', '/admin/seo', '/admin/seo/create', "/admin/seo/{$seo->id}/edit",
            '/admin/attendance', '/admin/classes', '/admin/students/create', "/admin/posts/{$post->id}/edit", '/admin/site-settings?tab=pop-ups::data::tab',
            "/admin/enquiries/{$enquiry->id}/edit", '/admin/learning-paths/1/edit'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
