<?php

namespace App\Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'page_id',
    'title',
    'sub_description',
    'file',
    'is_active',
])]
class Hero extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
