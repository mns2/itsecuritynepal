<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** Advanced SEO for one page (see migration create_seo_meta). */
class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $guarded = ['id'];

    protected $casts = ['schema' => 'array'];

    public const ROBOTS = [
        'index, follow' => 'Index and follow links (normal)',
        'noindex, follow' => 'Hide this page from Google, follow its links',
        'noindex, nofollow' => 'Hide from Google, do not follow links',
        'index, nofollow' => 'Index the page, do not follow its links',
    ];

    protected static function booted(): void
    {
        static::saving(function (SeoMeta $m) {
            if ($m->seoable && method_exists($m->seoable, 'seoPath')) {
                $m->path = $m->seoable->seoPath();
            }
            $m->path = self::normalisePath((string) $m->path);
            // Drop empty schema rows
            $m->schema = array_values(array_filter((array) $m->schema, fn ($b) => filled($b['json'] ?? null)));
        });
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function normalisePath(string $path): string
    {
        $path = '/'.trim(parse_url($path, PHP_URL_PATH) ?? $path, '/');

        return $path === '/' ? '/' : $path.'/';
    }

    /** Decoded JSON-LD blocks ready to print (invalid ones are skipped). */
    public function schemaBlocks(): array
    {
        return collect($this->schema ?? [])->map(function ($b) {
            $data = json_decode((string) ($b['json'] ?? ''), true);
            if (! is_array($data) || $data === []) {
                return null;
            }
            if (array_is_list($data)) {
                return ['@context' => 'https://schema.org', '@graph' => $data];
            }

            return ['@context' => $data['@context'] ?? 'https://schema.org'] + $data;
        })->filter()->values()->all();
    }
}
