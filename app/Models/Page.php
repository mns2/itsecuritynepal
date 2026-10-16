<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['last_updated' => 'date', 'is_published' => 'boolean'];
}
