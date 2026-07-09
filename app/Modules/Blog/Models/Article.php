<?php

namespace App\Modules\Blog\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Tag;
use App\Traits\CloudflareUpload;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable('title', 'short_description', 'description', 'cover_path', 'status', 'created_by', 'updated_by')]
class Article extends Model
{
    use CloudflareUpload;

    // cover_url est calculé dynamiquement à partir de cover_path, il n'existe pas en base
    protected $appends = ['cover_url'];

    // Aligne le modèle en mémoire sur le default(true) de la base
    protected $attributes = ['status' => true];

    #[Override]
    protected function casts()
    {
        return [
            'status' => 'boolean',
        ];
    }

    // Génère l'URL signée Cloudflare R2 de l'image de couverture
    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_path) {
            return $this->getImageUrl($this->cover_path, 'articles');
        }

        return null;
    }

    // Relation many-to-many avec Tag via la table pivot article_tag
    // withTimestamps() permet à Laravel de remplir created_at/updated_at du pivot automatiquement
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'article_tag', 'article_id', 'tag_id')->withTimestamps();
    }

    // Un article peut avoir plusieurs commentaires
    public function comments()
    {
        return $this->hasMany(Comment::class, 'article_id');
    }

    // Images insérées dans le contenu (description) via CKEditor
    public function images()
    {
        return $this->hasMany(ArticleImage::class, 'article_id');
    }

    // Auteur de la création
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Auteur de la dernière modification
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
