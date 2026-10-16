<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'related_course_ids' => 'array',
        'published_at' => 'datetime',
    ];

    public function scopePublished(Builder $q): Builder
    {
        return $q->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function url(): string
    {
        return '/'.$this->slug.'/';
    }

    public function readingMinutes(): int
    {
        return max(1, (int) round(str_word_count(strip_tags($this->body)) / 220));
    }

    public function relatedCourses(): Collection
    {
        $ids = $this->related_course_ids ?? [];

        return Course::published()->with(['category', 'batches'])->whereIn('id', $ids)->get()
            ->sortBy(fn ($c) => array_search($c->id, $ids))->values();
    }

    public function excerptText(): string
    {
        return $this->excerpt ?: Str::limit(trim(strip_tags(Str::markdown($this->body))), 200);
    }
}
