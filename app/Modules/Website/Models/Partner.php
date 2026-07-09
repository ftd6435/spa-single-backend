<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable([
    'name',
    'acronym',
    'domain',
    'description',
    'logo_path',
    'status',
    'created_by',
    'updated_by',
])]
class Partner extends Model
{
    use CloudflareUpload;

    protected $appends = [
        'logo_url',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return $this->getImageUrl($this->logo_path, 'partners');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
