<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LearningPath extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['steps' => 'array', 'is_published' => 'boolean'];

    /** Steps with Course models resolved; unpublished or missing courses are skipped. */
    public function resolvedSteps(): Collection
    {
        $steps = collect($this->steps ?? []);
        $courses = Course::published()->whereIn('id', $steps->pluck('course_id'))->get()->keyBy('id');

        return $steps
            ->map(fn ($s) => isset($courses[$s['course_id'] ?? 0])
                ? ['course' => $courses[$s['course_id']], 'note' => $s['note'] ?? null]
                : null)
            ->filter()
            ->values();
    }
}
