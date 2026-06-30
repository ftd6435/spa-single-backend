<?php

namespace App\Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'short_description',
    'description',
    'skills',
    'image',
    'closing_date',
    'is_active',
])]
class JobOpening extends Model
{
    protected function casts(): array
    {
        return [
            'skills'       => 'array',
            'closing_date' => 'date',
            'is_active'    => 'boolean',
        ];
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
