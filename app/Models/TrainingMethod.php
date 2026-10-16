<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingMethod extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['features' => 'array', 'is_published' => 'boolean'];

    public function url(): string
    {
        return '/'.$this->slug.'/';
    }
}
