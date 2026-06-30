<?php

namespace App\Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'project_name',
    'description',
    'estimated_budget',
    'expected_deadline',
    'full_name',
    'email',
    'phone',
    'company',
    'status',
])]
class Quote extends Model
{
    protected function casts(): array
    {
        return [
            'expected_deadline' => 'date',
        ];
    }
}
