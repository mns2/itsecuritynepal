<?php

namespace Tests\Feature;

use App\Filament\Pages\PageContentEditor;
use App\Models\Setting;
use App\Models\User;
use App\Support\PageContent;
use Database\Seeders\ContentImportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PageContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentImportSeeder::class);
        PageContent::flush();
    }

    public function test_defaults_are_used_when_nothing_is_saved(): void
    {
        $this->assertSame(PageContent::defaults()['home']['hero']['title'], content('home.hero.title'));
        $this->get('/')->assertOk()->assertSee(e(content('home.hero.title')), false);
    }

    public function test_saved_override_merges_with_defaults(): void
    {
        Setting::put('page_home', ['hero' => ['title' => 'Edited hero headline']]);
        PageContent::flush();

        $this->assertSame('Edited hero headline', content('home.hero.title'));
        $this->assertSame(PageContent::defaults()['home']['hero']['lead'], content('home.hero.lead'));
        $this->get('/')->assertOk()->assertSee('Edited hero headline');
    }

    public function test_stock_photos_render_on_the_homepage_and_cards(): void
    {
        $this->assertSame('/storage/stock/home-hero-main.webp', parse_url(media('stock/home-hero-main.webp'), PHP_URL_PATH));
        $this->assertSame('https://example.com/a.jpg', media('https://example.com/a.jpg'));
        $this->assertNull(media(null));

        $this->get('/')->assertOk()->assertSee('/storage/stock/home-hero-main.webp', false);
        $this->get('/course/')->assertOk()->assertSee('/storage/stock/track-', false);
    }

    public function test_page_content_editor_renders_and_saves(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/page-content')->assertOk()->assertSee('Homepage');

        Livewire::test(PageContentEditor::class)
            ->set('data.home.hero.title', 'Saved from admin')
            ->set('data.global.footer_text', 'Footer edited in admin')
            ->call('save')
            ->assertHasNoErrors();

        PageContent::flush();
        $this->assertSame('Saved from admin', content('home.hero.title'));
        $this->assertSame(PageContent::defaults()['home']['hero']['main_image'], content('home.hero.main_image'));
        $this->get('/')->assertSee('Saved from admin')->assertSee('Footer edited in admin');
    }
}
