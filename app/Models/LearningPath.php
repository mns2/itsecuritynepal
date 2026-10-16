<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class LearningPath extends Model
{
    use \App\Models\Concerns\HasSeo;

    protected $guarded = ['id'];

    protected $casts = ['steps' => 'array', 'skills' => 'array', 'job_titles' => 'array', 'is_published' => 'boolean'];

    public function url(): string
    {
        return '/career-pathways/'.$this->slug.'/';
    }

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
