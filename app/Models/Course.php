<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    public const LEVELS = ['Beginner', 'Intermediate', 'Advanced'];

    public const MODES = ['Classroom', 'Live Online', 'One-on-One'];

    protected $guarded = ['id'];

    protected $casts = [
        'official_code' => 'boolean',
        'modes' => 'array',
        'overview' => 'array',
        'outcomes' => 'array',
        'prerequisites' => 'array',
        'benefits' => 'array',
        'careers' => 'array',
        'modules' => 'array',
        'faqs' => 'array',
        'extra_sections' => 'array',
        'featured' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class)->where('is_published', true)->orderBy('sort');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true);
    }

    public function url(): string
    {
        return '/'.$this->slug.'/';
    }

    /** Page title: only real exam/course codes are used as a prefix. */
    public function seoTitle(): string
    {
        if ($this->meta_title) {
            return $this->meta_title;
        }

        return ($this->official_code ? $this->code.': ' : '').$this->name.' Course in Nepal | IT Security Nepal';
    }

    public function nextBatch(): ?Batch
    {
        return $this->batches->first();
    }

    public function related(int $limit = 3): Collection
    {
        return static::published()->with(['category', 'batches'])
            ->where('category_id', $this->category_id)
            ->whereKeyNot($this->getKey())->orderBy('sort')->limit($limit)->get();
    }

    /** Lightweight shape used by the client-side search and Course Finder. */
    public function toIndexArray(): array
    {
        return [
            'slug' => $this->slug,
            'code' => $this->code,
            'name' => $this->name,
            'category' => $this->category?->key,
            'categoryName' => $this->category?->name,
            'level' => $this->level,
            'certification' => $this->certification,
            'modes' => $this->modes ?? [],
            'summary' => $this->summary,
            'duration' => $this->duration,
            'featured' => $this->featured,
            'url' => $this->url(),
        ];
    }
}
