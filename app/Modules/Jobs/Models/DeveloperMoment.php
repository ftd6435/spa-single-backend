<?php

namespace App\Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Model;

class DeveloperMoment extends Model
{
    protected $fillable = [
        'name',
        'photo',
        'position',
        'quote',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}


