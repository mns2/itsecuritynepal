<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enquiry;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\ContentImportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentImportSeeder::class);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
    }

    public function test_every_admin_screen_renders_for_staff(): void
    {
        $this->actingAs(User::factory()->create());
        Enquiry::create(['form' => 'course', 'name' => 'Test Lead', 'phone' => '9812345678', 'email' => 'lead@example.com', 'course_id' => Course::first()->id, 'consent' => true]);

        $screens = ['/admin', '/admin/site-settings'];
        foreach (['courses', 'categories', 'batches', 'learning-paths', 'training-methods', 'posts', 'faqs', 'instructors', 'testimonials', 'pages', 'enquiries', 'redirects'] as $r) {
            $screens[] = "/admin/{$r}";
        }
        foreach (['courses', 'categories', 'batches', 'learning-paths', 'training-methods', 'posts', 'faqs', 'instructors', 'testimonials', 'pages', 'redirects'] as $r) {
            $screens[] = "/admin/{$r}/create";
        }
        $screens[] = '/admin/courses/'.Course::where('slug', 'ethical-hacking-expert')->value('id').'/edit';
        $screens[] = '/admin/posts/'.Post::first()->id.'/edit';
        $screens[] = '/admin/enquiries/'.Enquiry::first()->id.'/edit';

        foreach ($screens as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_enquiries_cannot_be_created_from_admin(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/enquiries/create')->assertNotFound();
    }
}
