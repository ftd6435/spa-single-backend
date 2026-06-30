<?php

namespace App\Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'phone',
    'email',
    'is_subscribed',
])]
class Newsletter extends Model
{
    protected function casts(): array
    {
        return [
            'is_subscribed' => 'boolean',
        ];
    }
}
