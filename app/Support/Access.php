<?php

namespace App\Support;

use App\Models\User;

/**
 * Who can use which part of the admin panel. Each screen belongs to one area;
 * each role gets a set of areas. Checked on every request (menu items AND direct URLs).
 * Change the ROLE_AREAS map to adjust permissions.
 */
class Access
{
    public const AREAS = [
        'leads' => 'Enquiries',
        'marketing' => 'Campaigns, subscribers, email campaigns',
        'training' => 'Classes, students, attendance',
        'finance' => 'Fees and payments',
        'catalogue' => 'Courses, tracks, batches, career pathways',
        'content' => 'Page content, blog, FAQs, instructors, testimonials, legal pages',
        'seo' => 'SEO settings and redirects',
        'settings' => 'Site settings',
        'staff' => 'Staff accounts',
    ];

    public const ROLES = [
        'admin' => 'Admin: everything, including settings and staff accounts',
        'manager' => 'Manager: everything except site settings and staff accounts',
        'office' => 'Front desk / counsellor: enquiries, students, attendance and fees',
        'marketing' => 'Marketing: enquiries, campaigns, email, content and SEO',
        'editor' => 'Content editor: website content, courses and SEO',
        'instructor' => 'Instructor: own classes and attendance only (/trainer)',
    ];

    public const ROLE_AREAS = [
        'admin' => ['*'],
        'manager' => ['leads', 'marketing', 'training', 'finance', 'catalogue', 'content', 'seo'],
        'office' => ['leads', 'training', 'finance'],
        'marketing' => ['leads', 'marketing', 'content', 'seo', 'catalogue'],
        'editor' => ['content', 'catalogue', 'seo'],
        'instructor' => [],
    ];

    public static function allows(?string $area, ?User $user = null): bool
    {
        $user ??= auth()->user();
        if (! $user || ! $user->is_active) {
            return false;
        }
        $areas = self::ROLE_AREAS[$user->role] ?? [];

        return in_array('*', $areas, true) || ($area !== null && in_array($area, $areas, true));
    }

    /** Roles that may sign in to /admin. */
    public static function adminRoles(): array
    {
        return array_keys(array_filter(self::ROLE_AREAS, fn ($areas) => $areas !== []));
    }
}
