<?php

namespace App\View\Components;

use Illuminate\View\Component;

/**
 * Inline Lucide icon: <x-icon name="arrow-right" class="size-4" />
 * SVGs live in resources/icons (Lucide, ISC licence; brand icons added locally). Decorative by default.
 */
class Icon extends Component
{
    private static array $cache = [];

    public function __construct(public string $name, public string $class = 'size-5') {}

    public function render(): string
    {
        $svg = self::$cache[$this->name] ??= $this->load();

        return str_replace('<svg', '<svg class="'.e($this->class).'" aria-hidden="true" focusable="false"', $svg);
    }

    private function load(): string
    {
        $file = resource_path('icons/'.basename($this->name).'.svg');
        if (! is_file($file)) {
            return app()->hasDebugModeEnabled() ? '<!-- missing icon: '.e($this->name).' -->' : '';
        }

        $svg = preg_replace('/<!--.*?-->/s', '', file_get_contents($file));
        $svg = preg_replace('/\s(class|width|height)="[^"]*"/', '', $svg, 3);

        return trim($svg);
    }
}
