<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A real running cohort of a course (internal; the public "Batches" page is separate). */
class TrainingClass extends Model
{
    public const STATUSES = [
        'upcoming' => 'Upcoming',
        'ongoing' => 'Ongoing',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    protected $guarded = ['id'];

    /** Nepal's working week starts on Sunday. */
    public const DAYS = ['sun' => 'Sun', 'mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat'];

    protected $casts = [
        'starts_on' => 'date',
        'ends_on' => 'date',
        'days' => 'array',
    ];

    protected static function booted(): void
    {
        // Fill the display text ("Sun–Fri · 7:00–9:00 AM") from days and times when it is left empty.
        static::saving(function (TrainingClass $c) {
            if (blank($c->schedule) && ($c->days || $c->start_time)) {
                $c->schedule = $c->scheduleText();
            }
        });
    }

    public function scheduleText(): string
    {
        $days = collect(array_keys(self::DAYS))->filter(fn ($d) => in_array($d, $this->days ?? [], true))->values();
        $keys = array_keys(self::DAYS);
        $isRange = $days->count() > 2 && $days->last() && (array_search($days->last(), $keys) - array_search($days->first(), $keys) + 1) === $days->count();
        $dayText = $isRange ? self::DAYS[$days->first()].'–'.self::DAYS[$days->last()] : $days->map(fn ($d) => self::DAYS[$d])->join(', ');
        $fmt = fn ($t) => $t ? \Illuminate\Support\Carbon::parse($t)->format('g:i A') : null;
        $time = trim(($fmt($this->start_time) ?? '').($this->end_time ? '–'.$fmt($this->end_time) : ''));

        return trim($dayText.($dayText && $time ? ' · ' : '').$time);
    }

    /** Does this class meet on the given date (by its days, start and end dates)? */
    public function meetsOn(\DateTimeInterface $date): bool
    {
        $d = \Illuminate\Support\Carbon::parse($date)->startOfDay();
        if ($this->starts_on && $d->lt($this->starts_on)) {
            return false;
        }
        if ($this->ends_on && $d->gt($this->ends_on)) {
            return false;
        }

        return empty($this->days) || in_array(strtolower($d->format('D')), $this->days, true);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /** Instructor account that teaches this class (can log in at /trainer). */
    public function instructorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_user_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    /** Classes the given user may see: training staff see all, instructors only their own. */
    public function scopeVisibleTo(Builder $q, ?User $user): Builder
    {
        return $user && \App\Support\Access::allows('training', $user) ? $q : $q->where('instructor_user_id', $user?->id ?? 0);
    }

    /** Attended (present + late) as a percentage of all marks in this class, or null if none yet. */
    public function attendanceRate(): ?int
    {
        $marks = Attendance::whereHas('session', fn ($s) => $s->where('training_class_id', $this->id));
        $total = (clone $marks)->count();

        return $total ? (int) round((clone $marks)->whereIn('status', Attendance::ATTENDED)->count() / $total * 100) : null;
    }

    public function scopeOngoing(Builder $q): Builder
    {
        return $q->where('status', 'ongoing');
    }

    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->where('status', 'upcoming');
    }
}
