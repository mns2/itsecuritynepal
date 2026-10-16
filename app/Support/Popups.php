<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Website pop-up switches and texts (Admin → Settings → Site settings → Pop-ups).
 * Stored in the `popups` setting; anything not saved yet falls back to these defaults.
 */
class Popups
{
    public const FREQUENCIES = ['session' => 'Once per visit', 'day' => 'Once a day', 'week' => 'Once a week', 'month' => 'Once a month'];

    public static function defaults(): array
    {
        return [
            'campaigns_enabled' => true,
            'course_finder' => [
                'enabled' => true,
                'delay_seconds' => 5,
                'frequency' => 'day',
                'show_on' => 'all',            // all | home
                'device' => 'all',
                'badge' => 'Free · 1 minute',
                'title' => 'Not sure which course is right for you?',
                'text' => 'Answer 4 quick questions about your experience and goals, and get a recommended course and learning path.',
                'button' => 'Find my course',
                'image' => null,
            ],
            'newsletter' => [
                'enabled' => true,
                'trigger' => 'scroll',         // delay | scroll | exit
                'delay_seconds' => 20,
                'scroll_percent' => 60,
                'frequency' => 'week',
                'device' => 'all',
                'badge' => 'Newsletter',
                'title' => 'Get new batch dates and offers first',
                'text' => 'New batches, scholarships and seasonal offers by email. No spam; unsubscribe any time.',
                'button' => 'Subscribe',
                'footer_enabled' => true,      // sign-up box in the footer
                'double_opt_in' => true,       // send a confirmation link before adding someone to the list
                'per_minute' => 30,            // email campaigns: messages per minute (keep under the host's limit)
            ],
        ];
    }

    public static function settings(): array
    {
        return PageContent::merge(self::defaults(), (array) Setting::get('popups', []));
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return data_get(self::settings(), $key, $default);
    }
}
