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

    // Route publique — seuls les commentaires actifs d'un article actif sont visibles
    public function index(string $articleId)
    {
        $article = Article::where('status', true)->find($articleId);

        if (! $article) {
            return $this->errorResponse("Article introuvable");
        }

        $comments = $article->comments()->where('status', true)->orderBy('created_at', 'desc')->get();

        return $this->successResponse(CommentResource::collection($comments), "Liste des commentaires chargée avec succès.");
    }

    // Route admin — tous les commentaires d'un article, y compris les désactivés
    public function adminIndex(string $articleId)
    {
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
        // On ne peut pas commenter un article désactivé
        $article = Article::where('status', true)->find($articleId);

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
    // Route admin — affiche/masque le commentaire sous l'article (modération)
    public function switchStatus(string $id)
    {
        $comment = Comment::find($id);

        if (! $comment) {
            return $this->errorResponse("Commentaire introuvable");
        }

        $comment->update(['status' => ! $comment->status, 'updated_by' => Auth::id()]);

        logActivity("Changement de statut d'un commentaire", ['status' => $comment->status], $comment);

        return $this->successResponse(new CommentResource($comment), $comment->status ? "Commentaire activé avec succès." : "Commentaire désactivé avec succès.");
    }

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
