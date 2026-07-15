<?php

namespace App\Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use App\Traits\CloudflareUpload;

#[Fillable([
    'name',
    'photo',
    'position',
    'quote',
    'description',
    'is_active',
])]
class DeveloperMoment extends Model
{
    use CloudflareUpload;

    protected $appends = [
        'photo_url',
    ];

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo) {
            return $this->getImageUrl($this->photo, 'developer-moments');
        }

        return null;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
