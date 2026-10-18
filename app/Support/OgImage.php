<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Social-sharing images (Open Graph / X): turns a page's own photo on the public disk into a 1200 × 630 JPEG
 * (centre crop), the size and format every network renders reliably (WebP is not supported everywhere).
 * Made once on first use and kept in storage/app/public/og/; returns null if the photo or GD is unavailable,
 * so the layout falls back to images/og-default.png.
 */
class OgImage
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    public static function url(?string $path): ?string
    {
        if (blank($path) || str_starts_with($path, 'http') || ! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $disk = Storage::disk('public');
        $target = 'og/'.pathinfo($path, PATHINFO_FILENAME).'-'.substr(md5($path), 0, 8).'.jpg';

        try {
            if (! $disk->exists($target)) {
                if (! $disk->exists($path) || ! self::render($disk->path($path), $disk->path($target))) {
                    return null;
                }
            }

            return $disk->url($target);
        } catch (\Throwable $e) {
            Log::warning('OG image could not be made', ['path' => $path, 'error' => $e->getMessage()]);

            return null;
        }
    }

    private static function render(string $source, string $target): bool
    {
        $src = match (strtolower(pathinfo($source, PATHINFO_EXTENSION))) {
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false,
            'png' => @imagecreatefrompng($source),
            'jpg', 'jpeg' => @imagecreatefromjpeg($source),
            default => false,
        };
        if (! $src) {
            return false;
        }

        [$w, $h] = [imagesx($src), imagesy($src)];
        $ratio = self::WIDTH / self::HEIGHT;
        [$cw, $ch] = $w / $h > $ratio ? [(int) round($h * $ratio), $h] : [$w, (int) round($w / $ratio)];
        $dst = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        imagecopyresampled($dst, $src, 0, 0, intdiv($w - $cw, 2), intdiv($h - $ch, 2), self::WIDTH, self::HEIGHT, $cw, $ch);

        if (! is_dir(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }
        $ok = imagejpeg($dst, $target, 82);
        imagedestroy($src);
        imagedestroy($dst);

        return $ok;
    }
}
