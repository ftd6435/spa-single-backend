<?php

namespace App\Modules\Blog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

// Registre des images insérées dans le champ description (contenu CKEditor) d'un article.
// article_id reste NULL tant que l'image n'a pas été rattachée à un article sauvegardé.
#[Fillable('article_id', 'path')]
class ArticleImage extends Model
{
    // Dossier de stockage sur R2, partagé avec ArticleController et ArticleImageController
    public const STORAGE_PATH = 'articles/content';

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}
