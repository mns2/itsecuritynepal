<?php

namespace App\Http\Controllers;

use App\Mail\NewEnquiry;
use App\Models\Course;
use App\Models\Enquiry;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class EnquiryController extends Controller
{
    /** Minimum seconds between page render and submit; faster submissions are almost always bots. */
    private const MIN_FILL_SECONDS = 3;

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($this->looksLikeSpam($request)) {
            // Respond exactly like a success so bots learn nothing; nothing is stored or emailed.
            return $this->respond($request);
        }

        $data = $request->validate([
            'form' => ['required', Rule::in(array_keys(Enquiry::FORMS))],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9\s\-]{6,18}$/'],
            'email' => ['required', 'email:rfc', 'max:160'],
            'course' => ['required', 'string', 'max:120'],
            'audience' => ['nullable', Rule::in(Enquiry::AUDIENCES)],
            'size' => ['nullable', 'string', 'max:40'],
            'mode' => ['nullable', Rule::in(Course::MODES)],
            'org' => ['nullable', 'required_if:form,corporate', 'required_if:audience,Corporate Batch', 'string', 'max:160'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
            'source_url' => ['nullable', 'string', 'max:500'],
        ], [
            'phone.regex' => 'Enter a valid phone number, e.g. 98XXXXXXXX.',
            'consent.accepted' => 'Please agree so we can contact you.',
            'org.required_if' => 'Enter your organisation name.',
            'course.required' => 'Choose a course, or "Not sure yet".',
        ]);

        $course = Course::published()->where('slug', $data['course'])->first();
        $isCorporate = $data['form'] === 'corporate';
        $audience = $isCorporate ? 'Corporate' : ($data['audience'] ?? 'Individual');

        $enquiry = Enquiry::create([
            'form' => $data['form'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'course_id' => $course?->id,
            'interest' => $course ? null : str_replace('area:', '', $data['course']),
            'audience' => $audience,
            'group_size' => $audience !== 'Individual' ? ($data['size'] ?? null) : null,
            'mode' => $data['mode'] ?? null,
            'organisation' => in_array($audience, ['Corporate', 'Corporate Batch'], true) ? ($data['org'] ?? null) : null,
            'message' => $data['message'] ?? null,
            'consent' => true,
            'source_url' => $data['source_url'] ?? null,
            'ip_hash' => hash('sha256', $request->ip().config('app.key')),
        ]);

        $to = config('services.enquiries.notify');
        if ($to) {
            try {
                Mail::to($to)->send(new NewEnquiry($enquiry->load('course')));
            } catch (\Throwable $e) {
                // The lead is already saved in the admin panel; never lose it because email failed.
                Log::error('Enquiry notification email failed', ['enquiry' => $enquiry->id, 'error' => $e->getMessage()]);
            }
        }

        return $this->respond($request);
    }

    private function looksLikeSpam(Request $request): bool
    {
        if (filled($request->input('website'))) {
            return true;
        }

        try {
            $issued = (int) decrypt((string) $request->input('issued'));
        } catch (DecryptException) {
            return true;
        }

        return now()->timestamp - $issued < self::MIN_FILL_SECONDS;
    }

    private function respond(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with(['enquiry_status' => 'sent', 'enquiry_form' => $request->input('form_id')]);
    }
}
