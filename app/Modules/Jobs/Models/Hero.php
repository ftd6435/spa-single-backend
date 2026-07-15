<?php

namespace App\Modules\Jobs\Models;

use App\Traits\CloudflareUpload;
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
    use CloudflareUpload;

    protected $appends = [
        'file_url',
    ];

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file) {
            return $this->getFileUrl($this->file, 'heroes');
        }

        return null;
    }


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
