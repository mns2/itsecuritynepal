<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DetectsSpam;
use App\Mail\NewExamBooking;
use App\Models\ExamBooking;
use App\Support\Access;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Public Red Hat exam booking page (/red-hat-exam-booking-nepal/) + staff-only access to payment proofs. */
class ExamBookingController extends Controller
{
    use DetectsSpam;

    public function show(): View
    {
        return view('pages.exam-booking', [
            'settings' => ExamBooking::settings(),
            'exams' => ExamBooking::examOptions(),
            'open' => self::isOpen(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(self::isOpen(), 503, 'Online exam booking is not available right now.');

        if ($this->looksLikeSpam($request, 5)) {
            // Look like a success so bots learn nothing; nothing is stored.
            return redirect()->to(route('exam-booking.submitted').'/')->with('exam_booking_sent', true);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s\-]{6,18}$/'],
            'email' => ['required', 'email:rfc', 'max:160'],
            'redhat_login' => ['required', 'string', 'min:2', 'max:100', 'regex:/^[A-Za-z0-9._@+\-]+$/'],
            'redhat_email' => ['required', 'email:rfc', 'max:160'],
            'exam' => ['required', Rule::in(ExamBooking::examOptions())],
            'proof' => ['required', File::types(['jpg', 'jpeg', 'png', 'webp', 'pdf'])->max(5 * 1024)],
            'transaction_id' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9 ._\/#\-]+$/'],
            'confirm' => ['accepted'],
        ], [
            'phone.regex' => 'Enter a valid phone number, e.g. 98XXXXXXXX.',
            'redhat_login.required' => 'Enter your Red Hat login (username).',
            'redhat_login.regex' => 'Use only letters, numbers and . _ - @ + (your Red Hat login, not your password).',
            'redhat_email.required' => 'Enter the email address on your Red Hat account.',
            'exam.required' => 'Choose the exam you want to book.',
            'exam.in' => 'Choose an exam from the list.',
            'proof.required' => 'Upload a screenshot of your payment.',
            'proof.max' => 'The screenshot must be 5 MB or smaller.',
            'proof.mimes' => 'Upload a JPG, PNG, WebP or PDF file.',
            'transaction_id.regex' => 'Use only letters, numbers, spaces and . _ / # -',
            'confirm.accepted' => 'Please confirm your details are correct.',
        ]);

        $file = $request->file('proof');
        $path = $file->store(ExamBooking::PROOF_DIR, ExamBooking::PROOF_DISK); // random file name, private disk

        $booking = ExamBooking::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'redhat_login' => $data['redhat_login'],
            'redhat_email' => $data['redhat_email'],
            'exam' => $data['exam'],
            'fee_npr' => (int) ExamBooking::settings()['fee_npr'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'proof_path' => $path,
            'proof_mime' => $file->getMimeType(), // detected from the file content, not the browser
            'source_url' => url()->previous(),
            'ip_hash' => hash('sha256', $request->ip().config('app.key')),
        ]);

        $to = config('services.enquiries.notify');
        if ($to) {
            try {
                Mail::to($to)->send(new NewExamBooking($booking));
            } catch (\Throwable $e) {
                // The booking is saved in the admin panel; never lose it because email failed.
                Log::error('Exam booking notification email failed', ['booking' => $booking->id, 'error' => $e->getMessage()]);
            }
        }

        return redirect()->to(route('exam-booking.submitted').'/')->with([
            'exam_booking_sent' => true,
            'exam_booking_ref' => $booking->reference,
            'exam_booking_exam' => $booking->exam,
            'exam_booking_name' => strtok($booking->name, ' '),
        ]);
    }

    public function submitted(): View|RedirectResponse
    {
        if (! session('exam_booking_sent')) {
            return redirect()->to(route('exam-booking').'/');
        }

        return view('pages.exam-booking-submitted', ['settings' => ExamBooking::settings()]);
    }

    /** Payment screenshot, only for signed-in staff who handle fees. Served with headers that stop it running as a page. */
    public function proof(ExamBooking $examBooking): StreamedResponse
    {
        abort_unless(Access::allows('finance'), 403);
        abort_unless(Storage::disk(ExamBooking::PROOF_DISK)->exists($examBooking->proof_path), 404);

        $ext = pathinfo($examBooking->proof_path, PATHINFO_EXTENSION);

        return Storage::disk(ExamBooking::PROOF_DISK)->response($examBooking->proof_path, "payment-{$examBooking->reference}.{$ext}", [
            'Content-Type' => $examBooking->proof_mime,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox",
            'Cache-Control' => 'private, no-store',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }

    /** Booking is open only when switched on AND the official payment QR has been uploaded (no QR = nobody can pay). */
    public static function isOpen(): bool
    {
        $s = ExamBooking::settings();

        return (bool) $s['enabled'] && filled($s['qr_image']) && ExamBooking::examOptions() !== [];
    }
}
