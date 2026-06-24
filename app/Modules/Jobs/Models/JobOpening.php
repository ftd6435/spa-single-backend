<?php

namespace App\Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Model;

class JobOpening extends Model
{
    protected $fillable = [
        'title',
        'short_description',
        'description',
        'skills',
        'image',
        'closing_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'skills' => 'array',
        'closing_date' => 'date',
    ];

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
