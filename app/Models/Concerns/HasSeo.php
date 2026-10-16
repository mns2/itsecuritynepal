<?php

namespace App\Models\Concerns;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/** Gives a model an editable "Advanced SEO" record whose path follows the model's URL. */
trait HasSeo
{
    public static function bootHasSeo(): void
    {
        static::saved(function ($model) {
            if ($model->wasChanged('slug') && ($seo = $model->seo()->first())) {
                $seo->save(); // re-sync path to the new URL
            }
        });
        static::deleted(fn ($model) => $model->seo()->delete());
    }

    public function seo(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function seoPath(): string
    {
        return method_exists($this, 'url') ? $this->url() : '/'.$this->slug.'/';
    }
}
