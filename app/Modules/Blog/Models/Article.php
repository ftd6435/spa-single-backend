<?php

namespace App\Modules\Blog\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Tag;
use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('title', 'short_description', 'description', 'cover_path', 'created_by', 'updated_by')]
class Article extends Model
{
    use CloudflareUpload;

    protected $appends = [
        'cover_url',
    ];

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_path) {
            return $this->getImageUrl($this->cover_path, 'articles');
        }

        return null;
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'article_tag', 'article_id', 'tag_id')->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'article_id');
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