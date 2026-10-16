<?php

namespace Tests\Feature;

use App\Mail\NewEnquiry;
use App\Models\Enquiry;
use Database\Seeders\ContentImportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EnquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentImportSeeder::class);
        config(['services.enquiries.notify' => 'admissions@example.test']);
        Mail::fake();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'form' => 'course',
            'name' => 'Sita Sharma',
            'phone' => '9812345678',
            'email' => 'sita@example.com',
            'course' => 'red-hat-system-administration',
            'audience' => 'Individual',
            'mode' => 'Classroom',
            'message' => 'Evening batch please',
            'consent' => '1',
            'issued' => encrypt(now()->subMinute()->timestamp),
            'website' => '',
        ], $overrides);
    }

    public function test_valid_enquiry_is_stored_and_emailed(): void
    {
        $this->postJson('/enquiries', $this->payload())->assertOk()->assertJson(['ok' => true]);

        $e = Enquiry::sole();
        $this->assertSame('Sita Sharma', $e->name);
        $this->assertSame('red-hat-system-administration', $e->course->slug);
        $this->assertSame('Individual', $e->audience);
        $this->assertNull($e->group_size);
        $this->assertSame('new', $e->status);
        $this->assertNotNull($e->ip_hash);
        Mail::assertSent(NewEnquiry::class, fn ($m) => $m->hasTo('admissions@example.test') && $m->hasReplyTo('sita@example.com'));
    }

    public function test_group_enquiry_keeps_group_size(): void
    {
        $this->postJson('/enquiries', $this->payload(['form' => 'group', 'audience' => 'Group', 'size' => '6–10 people']))->assertOk();
        $this->assertSame(['Group', '6–10 people'], [Enquiry::sole()->audience, Enquiry::sole()->group_size]);
    }

    public function test_corporate_batch_enquiry_requires_organisation(): void
    {
        $this->postJson('/enquiries', $this->payload(['audience' => 'Corporate Batch']))->assertStatus(422)->assertJsonValidationErrors('org');
        $this->postJson('/enquiries', $this->payload(['audience' => 'Corporate Batch', 'org' => 'Example Ltd', 'size' => '11–20 people']))->assertOk();
        $e = Enquiry::sole();
        $this->assertSame(['Corporate Batch', 'Example Ltd', '11–20 people'], [$e->audience, $e->organisation, $e->group_size]);
    }

    public function test_not_sure_enquiry_has_no_course_but_keeps_interest(): void
    {
        $this->postJson('/enquiries', $this->payload(['course' => 'not-sure']))->assertOk();
        $this->assertNull(Enquiry::sole()->course_id);
        $this->assertSame('not-sure', Enquiry::sole()->interest);
    }

    public function test_corporate_enquiry_requires_organisation(): void
    {
        $this->postJson('/enquiries', $this->payload(['form' => 'corporate', 'course' => 'area:cybersecurity']))
            ->assertStatus(422)->assertJsonValidationErrors('org');
        $this->postJson('/enquiries', $this->payload(['form' => 'corporate', 'course' => 'area:cybersecurity', 'org' => 'Example Bank', 'size' => '16–40']))->assertOk();
        $this->assertSame(['Corporate', 'Example Bank', 'cybersecurity'], [Enquiry::sole()->audience, Enquiry::sole()->organisation, Enquiry::sole()->interest]);
    }

    public function test_validation_errors_are_returned(): void
    {
        $this->postJson('/enquiries', $this->payload(['name' => '', 'phone' => 'abc', 'email' => 'nope', 'consent' => '0']))
            ->assertStatus(422)->assertJsonValidationErrors(['name', 'phone', 'email', 'consent']);
        $this->assertSame(0, Enquiry::count());
        Mail::assertNothingSent();
    }

    public function test_honeypot_and_too_fast_submissions_are_silently_dropped(): void
    {
        $this->postJson('/enquiries', $this->payload(['website' => 'http://spam.example']))->assertOk();
        $this->postJson('/enquiries', $this->payload(['issued' => encrypt(now()->timestamp)]))->assertOk();
        $this->postJson('/enquiries', $this->payload(['issued' => 'tampered']))->assertOk();
        $this->assertSame(0, Enquiry::count());
        Mail::assertNothingSent();
    }

    public function test_non_js_submission_redirects_back_with_success_flash(): void
    {
        $this->from('/contact/')->post('/enquiries', $this->payload(['form_id' => 'contact-form']))
            ->assertRedirect('/contact/')->assertSessionHas('enquiry_status', 'sent');
    }

    public function test_enquiries_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/enquiries', $this->payload(['email' => "a{$i}@example.com"]))->assertOk();
        }
        $this->postJson('/enquiries', $this->payload())->assertStatus(429);
    }

    public function test_email_failure_does_not_lose_the_lead(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));
        $this->postJson('/enquiries', $this->payload())->assertOk();
        $this->assertSame(1, Enquiry::count());
    }
}
