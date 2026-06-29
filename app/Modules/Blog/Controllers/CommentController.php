<?php

namespace App\Modules\Blog\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Models\Article;
use App\Modules\Blog\Models\Comment;
use App\Modules\Blog\Requests\CommentRequest;
use App\Modules\Blog\Resources\CommentResource;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

// Gestion des commentaires d'articles (lecture/ajout public, suppression admin)
class CommentController extends Controller
{
    use ApiResponses;

    // Route publique — liste les commentaires d'un article donné
    public function index(string $articleId)
    {
        // On vérifie que l'article existe avant de récupérer ses commentaires
        $article = Article::find($articleId);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        $comments = $article->comments()->orderBy('created_at', 'desc')->get();

        return $this->successResponse(CommentResource::collection($comments), "Liste des commentaires chargée avec succès.");
    }

    // Route publique — tout visiteur peut poster un commentaire sur un article
    public function store(CommentRequest $request, string $articleId)
    {
        $article = Article::find($articleId);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        $data = $request->validated();
        $data['article_id'] = $article->id;
        // created_by peut être null si le visiteur n'est pas connecté (route publique)
        $data['created_by'] = Auth::id();

        $comment = Comment::create($data);

        logActivity("Création d'un commentaire", $data, $comment);

        return $this->successResponse(new CommentResource($comment), "Commentaire enregistré avec succès.");
    }

    // Route admin — seul un admin peut supprimer un commentaire
    public function destroy(string $id)
    {
        $comment = Comment::find($id);

        if (! $comment) {
            return $this->errorResponse("Commentaire introuvable");
        }

        logActivity("Suppression d'un commentaire", $comment->toArray(), $comment);
        $comment->delete();

        return $this->noContentSuccessResponse("Commentaire supprimé avec succès");
    }
}
