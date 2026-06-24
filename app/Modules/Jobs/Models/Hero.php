<?php

namespace App\Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Model;

class Hero extends Model
{
    protected $fillable = [
        'page_id',
        'title',
        'sub_description',
        'file',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

}
