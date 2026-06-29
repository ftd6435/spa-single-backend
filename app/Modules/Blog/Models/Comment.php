<?php

namespace App\Modules\Blog\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

// Commentaire laissé par un visiteur sur un article
#[Fillable('article_id', 'name', 'email', 'content', 'created_by', 'updated_by')]
class Comment extends Model
{
    // L'article auquel appartient ce commentaire
    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    // Utilisateur connecté qui a posté le commentaire (null si visiteur anonyme)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
