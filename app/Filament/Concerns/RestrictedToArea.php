<?php

namespace App\Filament\Concerns;

use App\Support\Access;

/**
 * Limits a Filament resource to one access area (see App\Support\Access).
 * The class sets: protected static string $accessArea = 'leads';
 * Filament checks canViewAny() for the menu and for every page of the resource.
 */
trait RestrictedToArea
{
    public static function canViewAny(): bool
    {
        return Access::allows(static::$accessArea);
    }
}
