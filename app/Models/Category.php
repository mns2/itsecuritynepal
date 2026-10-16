<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use \App\Models\Concerns\HasSeo;

    protected $guarded = ['id'];

    protected $casts = [
        'objectives' => 'array',
        'faqs' => 'array',
        'show_on_home' => 'boolean',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class)->orderBy('sort');
    }

    public function publishedCourses(): HasMany
    {
        return $this->courses()->where('is_published', true);
    }

    public function url(): string
    {
        return '/'.$this->slug.'/';
    }

    public function learningPath(): ?LearningPath
    {
        return $this->learning_path_key
            ? LearningPath::where('key', $this->learning_path_key)->where('is_published', true)->first()
            : null;
    }
}
