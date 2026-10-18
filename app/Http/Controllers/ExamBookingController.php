<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\DetectsSpam;
use App\Mail\ConfirmExamBookingEmail;
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
            'status' => 'unverified', // reaches staff only after the candidate confirms their email
        ]);

        $mailed = self::sendConfirmation($booking);

        return redirect()->to(route('exam-booking.submitted').'/')->with([
            'exam_booking_sent' => true,
            'exam_booking_ref' => $booking->reference,
            'exam_booking_exam' => $booking->exam,
            'exam_booking_name' => strtok($booking->name, ' '),
            'exam_booking_email' => $booking->email,
            'exam_booking_mailed' => $mailed,
        ]);
    }

    /** Emails the confirmation link. Returns false if sending failed (the booking stays saved; staff can see it). */
    public static function sendConfirmation(ExamBooking $booking): bool
    {
        try {
            Mail::to($booking->email, $booking->name)->send(new ConfirmExamBookingEmail($booking));
            $booking->forceFill(['confirmation_sent_at' => now()])->saveQuietly();

            return true;
        } catch (\Throwable $e) {
            Log::error('Exam booking confirmation email failed', ['booking' => $booking->id, 'error' => $e->getMessage()]);

            return false;
        }
    }

    /** Link from the confirmation email: marks the email confirmed, sends the booking to staff, opens tracking. */
    public function verify(ExamBooking $examBooking): RedirectResponse
    {
        if (! $examBooking->emailVerified()) {
            self::markEmailVerified($examBooking);
            session()->flash('exam_track_notice', 'Thank you, your email is confirmed. Our team will now verify your payment.');
        }

        return $this->openTracking($examBooking);
    }

    /** Confirms the email (link or staff) and alerts staff that a payment is waiting to be checked. */
    public static function markEmailVerified(ExamBooking $booking): void
    {
        $booking->forceFill(['email_verified_at' => now(), 'status' => $booking->status === 'unverified' ? 'pending' : $booking->status])->saveQuietly();

        if ($to = config('services.enquiries.notify')) {
            try {
                Mail::to($to)->send(new NewExamBooking($booking));
            } catch (\Throwable $e) {
                // The booking is saved in the admin panel; never lose it because email failed.
                Log::error('Exam booking notification email failed', ['booking' => $booking->id, 'error' => $e->getMessage()]);
            }
        }
    }

    /** Link from status emails: opens this booking on the tracking page. */
    public function open(ExamBooking $examBooking): RedirectResponse
    {
        return $this->openTracking($examBooking);
    }

    /** Tracking page: lookup form, plus the bookings this browser has opened (by link or reference + email). */
    public function track(): View
    {
        $bookings = ExamBooking::whereIn('id', (array) session('exam_track', []))->latest()->get();

        return view('pages.exam-booking-track', ['bookings' => $bookings, 'settings' => ExamBooking::settings()]);
    }

    public function lookup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reference' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email:rfc', 'max:160'],
        ], ['reference.required' => 'Enter your booking reference, e.g. RHX-7K2P9Q.']);

        $ref = strtoupper(trim($data['reference']));
        $ref = str_starts_with($ref, 'RHX-') ? $ref : 'RHX-'.ltrim($ref, '-');
        $booking = ExamBooking::where('reference', $ref)->first();

        // Same answer whether the reference or the email is wrong: nothing to learn by guessing.
        if (! $booking || strcasecmp(trim($booking->email), trim($data['email'])) !== 0) {
            return back()->withInput()->withErrors(['reference' => 'We could not find a booking with that reference and email address.']);
        }

        return $this->openTracking($booking);
    }

    /** "Send the confirmation email again" on the tracking page (only for this browser's unconfirmed bookings). */
    public function resend(ExamBooking $examBooking): RedirectResponse
    {
        abort_unless(in_array($examBooking->id, (array) session('exam_track', []), true), 403);

        if (! $examBooking->emailVerified()) {
            $ok = $examBooking->confirmation_sent_at?->gt(now()->subMinutes(2)) ? true : self::sendConfirmation($examBooking);
            session()->flash('exam_track_notice', $ok
                ? "We sent the confirmation link to {$examBooking->email} again. Check your inbox and spam folder."
                : 'We could not send the email right now. Please call us to confirm your booking.');
        }

        return redirect()->to(route('exam-booking.track').'/');
    }

    private function openTracking(ExamBooking $booking): RedirectResponse
    {
        session()->put('exam_track', array_values(array_unique(array_merge((array) session('exam_track', []), [$booking->id]))));

        return redirect()->to(route('exam-booking.track').'/');
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
