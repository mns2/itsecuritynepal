<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use \App\Models\Concerns\HasSeo;

    protected $guarded = ['id'];

    protected $casts = ['last_updated' => 'date', 'is_published' => 'boolean'];
}
