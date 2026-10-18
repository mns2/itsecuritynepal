<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/** A Red Hat exam booking request with its payment proof. Created by the public form, reviewed by staff (Admin → Exam bookings). */
class ExamBooking extends Model
{
    public const STATUSES = [
        'unverified' => 'Email not confirmed',
        'pending' => 'Pending verification',
        'verified' => 'Payment verified',
        'booked' => 'Exam booked',
        'rejected' => 'Payment not confirmed',
        'cancelled' => 'Cancelled',
    ];

    /** Payment proofs are stored here, on the private disk. */
    public const PROOF_DISK = 'local';

    public const PROOF_DIR = 'exam-payments';

    /** How long the email confirmation and tracking links in emails stay valid. */
    public const LINK_DAYS = 30;

    protected $guarded = ['id'];

    protected $casts = ['exam_at' => 'datetime', 'reviewed_at' => 'datetime', 'email_verified_at' => 'datetime', 'confirmation_sent_at' => 'datetime', 'fee_npr' => 'integer'];

    protected static function booted(): void
    {
        static::creating(function (self $b) {
            $b->reference ??= self::newReference();
        });

        // Remember who changed the status and when.
        static::saving(function (self $b) {
            if ($b->exists && $b->isDirty('status') && auth()->id()) {
                $b->reviewed_by = auth()->id();
                $b->reviewed_at = now();
            }
        });

        static::deleted(fn (self $b) => Storage::disk(self::PROOF_DISK)->delete($b->proof_path));
    }

    /**
     * Page settings (Admin → Site settings → Exam booking) merged over defaults.
     * The QR image has no default on purpose: the page says "not available yet" until staff upload the official one.
     */
    public static function settings(): array
    {
        $defaults = [
            'enabled' => true,
            'fee_npr' => 35000,
            'qr_image' => null,
            'payee_note' => '',
            'exams' => [
                ['name' => 'RHCSA: Red Hat Certified System Administrator', 'code' => 'EX200'],
                ['name' => 'RHCE: Red Hat Certified Engineer', 'code' => 'EX294'],
                ['name' => 'Other Red Hat exam (our team will confirm which one with you)', 'code' => ''],
            ],
        ];

        $saved = array_filter((array) Setting::get('exam_booking', []), fn ($v) => $v !== null && $v !== '' && $v !== []);

        return array_merge($defaults, $saved);
    }

    /** Exam labels offered on the form, e.g. "RHCSA: Red Hat Certified System Administrator (EX200)". */
    public static function examOptions(): array
    {
        return collect(self::settings()['exams'])
            ->filter(fn ($e) => filled($e['name'] ?? null))
            ->map(fn ($e) => trim($e['name'].(filled($e['code'] ?? null) ? " ({$e['code']})" : '')))
            ->values()->all();
    }

    public static function newReference(): string
    {
        do {
            $ref = 'RHX-'.Str::upper(Str::random(6));
        } while (preg_match('/[O0Il1]/', substr($ref, 4)) || self::where('reference', $ref)->exists());

        return $ref;
    }

    /** Link in the confirmation email: confirms the address and opens the tracking page. */
    public function verifyUrl(): string
    {
        return URL::temporarySignedRoute('exam-booking.verify', now()->addDays(self::LINK_DAYS), ['examBooking' => $this->id]);
    }

    /** Link in status-update emails: opens this booking on the tracking page without typing the reference. */
    public function trackUrl(): string
    {
        return URL::temporarySignedRoute('exam-booking.open', now()->addDays(self::LINK_DAYS), ['examBooking' => $this->id]);
    }

    public function emailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function proofIsImage(): bool
    {
        return str_starts_with($this->proof_mime, 'image/');
    }
}
