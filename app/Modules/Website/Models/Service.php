<?php

namespace App\Modules\Website\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Tag;
use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable([
    'icon',
    'image_path',
    'title',
    'short_description',
    'description',
    'benefits',
    'created_by',
    'updated_by',
])]
class Service extends Model
{
    use CloudflareUpload;

    protected $appends = [
        'image_url',
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'benefits' => 'array',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return $this->getImageUrl($this->image_path, 'services');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'service_tag')
            ->withPivot(['created_by', 'updated_by'])
            ->withTimestamps();
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
