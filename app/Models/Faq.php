<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    public const GROUPS = [
        'getting-started' => 'Getting started',
        'training' => 'Classes & schedules',
        'certification' => 'Certification',
        'fees' => 'Fees & enrolment',
        'corporate' => 'Group & corporate training',
    ];

    protected $guarded = ['id'];

    protected $casts = ['show_on_home' => 'boolean', 'is_published' => 'boolean'];
}
