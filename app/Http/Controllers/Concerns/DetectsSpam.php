<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;

/** Honeypot field (`website`) + signed render timestamp (`issued`): submissions faster than MIN_FILL_SECONDS are almost always bots. */
trait DetectsSpam
{
    protected function looksLikeSpam(Request $request, int $minSeconds = 3): bool
    {
        if (filled($request->input('website'))) {
            return true;
        }

        try {
            $issued = (int) decrypt((string) $request->input('issued'));
        } catch (DecryptException) {
            return true;
        }

        return now()->timestamp - $issued < $minSeconds;
    }
}
