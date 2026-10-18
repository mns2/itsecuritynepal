<?php

namespace Tests\Feature;

use App\Mail\NewExamBooking;
use App\Models\ExamBooking;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\ContentImportSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExamBookingTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/red-hat-exam-booking-nepal/';

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentImportSeeder::class);
        config(['services.enquiries.notify' => 'admissions@example.test']);
        Mail::fake();
        Storage::fake('local');
        Setting::put('exam_booking', ['enabled' => true, 'qr_image' => 'payment/test-qr.png']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Sita Sharma',
            'phone' => '9812345678',
            'email' => 'sita@example.com',
            'redhat_login' => 'sita.sharma',
            'redhat_email' => 'sita.rh@example.com',
            'exam' => ExamBooking::examOptions()[0],
            'proof' => UploadedFile::fake()->image('payment.png', 600, 900),
            'transaction_id' => 'TXN 123/45',
            'confirm' => '1',
            'issued' => encrypt(now()->subMinute()->timestamp),
            'website' => '',
        ], $overrides);
    }

    public function test_page_shows_fee_qr_form_and_structured_data(): void
    {
        $this->get(self::URL)->assertOk()
            ->assertSee('Red Hat Exam Booking in Nepal')
            ->assertSee('NPR 35,000')
            ->assertSee('payment/test-qr.png')
            ->assertSee('name="redhat_login"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('"@type": "Service"', false)
            ->assertSee('"@type": "FAQPage"', false)
            ->assertDontSee('name="password"', false);
    }

    public function test_form_is_closed_until_the_official_qr_is_uploaded(): void
    {
        Setting::put('exam_booking', ['enabled' => true]);

        $this->get(self::URL)->assertOk()->assertSee('Payment QR code coming soon')->assertDontSee('name="redhat_login"', false);
        $this->post(self::URL, $this->payload())->assertStatus(503);
        $this->assertSame(0, ExamBooking::count());
    }

    public function test_valid_booking_is_saved_privately_and_shows_pending_confirmation(): void
    {
        $res = $this->post(self::URL, $this->payload());
        $res->assertRedirectContains('/red-hat-exam-booking-nepal/submitted/');

        $b = ExamBooking::sole();
        $this->assertSame('pending', $b->status);
        $this->assertSame(35000, $b->fee_npr);
        $this->assertSame('sita.sharma', $b->redhat_login);
        $this->assertSame('image/png', $b->proof_mime);
        $this->assertMatchesRegularExpression('/^RHX-[A-Z2-9]{6}$/', $b->reference);
        $this->assertStringStartsWith('exam-payments/', $b->proof_path);
        Storage::disk('local')->assertExists($b->proof_path);
        Mail::assertSent(NewExamBooking::class, fn ($m) => $m->hasTo('admissions@example.test'));

        $this->followRedirects($res)->assertOk()
            ->assertSee($b->reference)
            ->assertSee('payment and exam booking are pending verification', false);
    }

    public function test_validation_rejects_bad_files_logins_and_missing_fields(): void
    {
        $this->post(self::URL, $this->payload([
            'proof' => UploadedFile::fake()->create('payment.php', 10, 'application/x-php'),
            'redhat_login' => 'pass word!',
            'exam' => 'Made-up exam',
            'confirm' => '',
        ]))->assertSessionHasErrors(['proof', 'redhat_login', 'exam', 'confirm']);

        $this->post(self::URL, $this->payload(['proof' => UploadedFile::fake()->image('big.png')->size(6000)]))->assertSessionHasErrors('proof');
        $this->post(self::URL, $this->payload(['proof' => null]))->assertSessionHasErrors('proof');
        $this->assertSame(0, ExamBooking::count());
    }

    public function test_transaction_id_is_optional_and_pdf_receipts_are_accepted(): void
    {
        $this->post(self::URL, $this->payload([
            'transaction_id' => '',
            'proof' => UploadedFile::fake()->create('receipt.pdf', 200, 'application/pdf'),
        ]))->assertRedirect();

        $this->assertNull(ExamBooking::sole()->transaction_id);
    }

    public function test_spam_is_dropped_silently(): void
    {
        $this->post(self::URL, $this->payload(['website' => 'http://spam.test']))->assertRedirect();
        $this->post(self::URL, $this->payload(['issued' => encrypt(now()->timestamp)]))->assertRedirect();
        $this->assertSame(0, ExamBooking::count());
        Mail::assertNothingSent();
    }

    public function test_submitted_page_without_a_booking_goes_back_to_the_form(): void
    {
        $this->get('/red-hat-exam-booking-nepal/submitted/')->assertRedirectContains(self::URL);
    }

    public function test_payment_proof_is_only_visible_to_finance_staff(): void
    {
        $this->post(self::URL, $this->payload());
        $b = ExamBooking::sole();
        $url = route('exam-booking.proof', $b);

        $this->get($url)->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'editor', 'is_active' => true]))->get($url)->assertForbidden();

        $this->actingAs(User::factory()->create(['role' => 'office', 'is_active' => true]))->get($url)
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_staff_can_review_bookings_in_admin_and_deleting_removes_the_file(): void
    {
        $this->post(self::URL, $this->payload());
        $b = ExamBooking::sole();
        $office = User::factory()->create(['role' => 'office', 'is_active' => true]);

        $this->actingAs($office)->get('/admin/exam-bookings')->assertOk()->assertSee($b->reference);
        $this->actingAs($office)->get("/admin/exam-bookings/{$b->id}/edit")->assertOk()->assertSee('sita.sharma')->assertSee(route('exam-booking.proof', $b), false);
        $this->actingAs(User::factory()->create(['role' => 'editor', 'is_active' => true]))->get('/admin/exam-bookings')->assertForbidden();

        $this->actingAs($office);
        $b->update(['status' => 'verified']);
        $this->assertSame($office->id, $b->fresh()->reviewed_by);

        $path = $b->proof_path;
        $b->delete();
        Storage::disk('local')->assertMissing($path);
    }

    public function test_page_is_in_the_sitemap(): void
    {
        $this->get('/sitemap.xml')->assertOk()->assertSee('/red-hat-exam-booking-nepal/');
    }
}
