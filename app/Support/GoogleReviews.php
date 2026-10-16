<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Real Google reviews of the institute's Google Business Profile, via the Places API (New).
 * Needs GOOGLE_PLACES_API_KEY in .env and the Place ID in Admin → Site settings → Google reviews.
 * Google returns the overall rating, total count and up to 5 reviews; results are cached for 12 hours
 * (about 60 API calls a month, well inside Google's free monthly allowance).
 */
class GoogleReviews
{
    private const CACHE_KEY = 'google.reviews';

    public static function defaults(): array
    {
        return [
            'enabled' => false,
            'place_id' => '',
            'min_rating' => 0,            // hide individual reviews below this many stars (overall rating is always shown)
            'show_home' => true,
            'show_about' => true,
            'show_course' => false,
            'title' => 'What students say on Google',
        ];
    }

    public static function settings(): array
    {
        return array_merge(self::defaults(), (array) Setting::get('reviews', []));
    }

    public static function configured(): bool
    {
        $s = self::settings();

        return $s['enabled'] && filled($s['place_id']) && filled(config('services.google.places_key'));
    }

    /** @return array{rating: float, count: int, url: ?string, writeUrl: string, reviews: array}|null */
    public static function get(): ?array
    {
        if (! self::configured()) {
            return null;
        }

        $data = Cache::get(self::CACHE_KEY);
        if ($data === null) {
            $data = self::fetch();
            // Cache failures briefly so a broken key does not slow every page.
            Cache::put(self::CACHE_KEY, $data ?: [], $data ? now()->addHours(12) : now()->addMinutes(30));
        }
        if (! $data) {
            return null;
        }

        $min = (int) self::settings()['min_rating'];
        $data['reviews'] = array_values(array_filter($data['reviews'], fn ($r) => $r['rating'] >= $min && filled($r['text'])));

        return $data;
    }

    public static function refresh(): ?array
    {
        Cache::forget(self::CACHE_KEY);

        return self::get();
    }

    /** Calls Google. Returns null on any error (logged). */
    public static function fetch(?string &$error = null): ?array
    {
        $placeId = trim((string) self::settings()['place_id']);
        try {
            $res = Http::timeout(8)->withHeaders([
                'X-Goog-Api-Key' => config('services.google.places_key'),
                'X-Goog-FieldMask' => 'rating,userRatingCount,googleMapsUri,reviews',
            ])->get('https://places.googleapis.com/v1/places/'.rawurlencode($placeId), ['languageCode' => 'en']);

            if (! $res->successful()) {
                $error = $res->json('error.message') ?: 'HTTP '.$res->status();
                Log::warning('Google reviews request failed', ['status' => $res->status(), 'error' => $error]);

                return null;
            }

            $j = $res->json();

            return [
                'rating' => round((float) ($j['rating'] ?? 0), 1),
                'count' => (int) ($j['userRatingCount'] ?? 0),
                'url' => $j['googleMapsUri'] ?? null,
                'writeUrl' => 'https://search.google.com/local/writereview?placeid='.rawurlencode($placeId),
                'reviews' => collect($j['reviews'] ?? [])->map(fn ($r) => [
                    'author' => $r['authorAttribution']['displayName'] ?? 'Google user',
                    'authorUrl' => $r['authorAttribution']['uri'] ?? null,
                    'photo' => $r['authorAttribution']['photoUri'] ?? null,
                    'rating' => (int) ($r['rating'] ?? 0),
                    'text' => $r['originalText']['text'] ?? ($r['text']['text'] ?? ''),
                    'when' => $r['relativePublishTimeDescription'] ?? null,
                ])->all(),
            ];
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            Log::warning('Google reviews request failed', ['error' => $error]);

            return null;
        }
    }
}
